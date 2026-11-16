<?php

class SolicitudPlanPro {

    // ATRIBUTOS

    private $idSolicitud;
    private $idUsuario;
    private $Mensaje;
    private $Comprobante;
    private $NombreComprobante;
    private $Estado;
    private $FechaSolicitud;


    // CONSTRUCTOR

    public function __construct($idUsuario, $Mensaje, $Comprobante, $NombreComprobante) {

        $this->idUsuario = $idUsuario;
        $this->Mensaje = $Mensaje;
        $this->Comprobante = $Comprobante;
        $this->NombreComprobante = $NombreComprobante;
        $this->Estado = "pendiente";

    }


    // OBTENER ATRIBUTOS

    public function __get($atributo) {
        return $this->$atributo;
    }


    // COMPROBAR SI EXISTE UNA SOLICITUD PENDIENTE

    public static function tienePendiente($idUsuario) {

        require __DIR__ . "/../bd/conexion.php";

        $sql = "SELECT idSolicitud FROM solicitudplanpro
                WHERE idUsuario = ? AND Estado = 'pendiente' LIMIT 1";

        $consulta = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param($consulta, "i", $idUsuario);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);

        $existe = mysqli_stmt_num_rows($consulta) > 0;

        mysqli_stmt_close($consulta);
        mysqli_close($conexion);

        return $existe;
    }


    // CREAR SOLICITUD

    public function crear() {

        // EVITAMOS NUEVAS SOLICITUDES SI EL PLAN YA ESTA ACTIVO

        require_once __DIR__ . "/usuario.php";

        $usuario = Usuario::buscarPorId((int)$this->idUsuario);

        if (!$usuario || $usuario->esPlanPro() || self::tienePendiente($this->idUsuario)) {
            return false;
        }

        require __DIR__ . "/../bd/conexion.php";

        $sql = "INSERT INTO solicitudplanpro
                (idUsuario, Mensaje, Comprobante, NombreComprobante)
                VALUES (?, ?, ?, ?)";

        $consulta = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param(
            $consulta,
            "isss",
            $this->idUsuario,
            $this->Mensaje,
            $this->Comprobante,
            $this->NombreComprobante
        );

        mysqli_stmt_execute($consulta);

        $this->idSolicitud = mysqli_insert_id($conexion);

        mysqli_stmt_close($consulta);
        mysqli_close($conexion);

        return $this->idSolicitud;
    }


    // OBTENER SOLICITUDES DEL USUARIO

    public static function obtenerPorUsuario($idUsuario) {

        require __DIR__ . "/../bd/conexion.php";

        $sql = "SELECT * FROM solicitudplanpro
                WHERE idUsuario = ?
                ORDER BY FechaSolicitud DESC, idSolicitud DESC";

        $consulta = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param($consulta, "i", $idUsuario);
        mysqli_stmt_execute($consulta);

        $resultado = mysqli_stmt_get_result($consulta);
        $solicitudes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        mysqli_stmt_close($consulta);
        mysqli_close($conexion);

        return $solicitudes;
    }


    // OBTENEMOS LAS SOLICITUDES QUE TODAVIA ESPERAN REVISION

    public static function obtenerPendientes() {
        require __DIR__ . "/../bd/conexion.php";

        $sql = "SELECT s.*, u.Nombre, u.Apellido, u.email, u.NombreUsuario
                FROM solicitudplanpro s
                INNER JOIN usuario u ON u.idUsuario = s.idUsuario
                WHERE s.Estado = 'pendiente'
                ORDER BY s.FechaSolicitud ASC, s.idSolicitud ASC";

        $resultado = mysqli_query($conexion, $sql);
        $solicitudes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        mysqli_close($conexion);
        return $solicitudes;
    }


    // CONSULTAMOS EL HISTORIAL DE SOLICITUDES RESUELTAS

    public static function obtenerHistorial($limite = 30) {
        require __DIR__ . "/../bd/conexion.php";

        $limite = max(1, min(100, (int)$limite));

        $sql = "SELECT s.*, u.Nombre, u.Apellido, u.email
                FROM solicitudplanpro s
                INNER JOIN usuario u ON u.idUsuario = s.idUsuario
                WHERE s.Estado IN ('aprobada', 'rechazada')
                ORDER BY s.FechaResolucion DESC, s.idSolicitud DESC
                LIMIT $limite";

        $resultado = mysqli_query($conexion, $sql);
        $solicitudes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        mysqli_close($conexion);
        return $solicitudes;
    }


    // APROBAMOS O RECHAZAMOS LA SOLICITUD Y ACTUALIZAMOS EL PLAN

    public static function resolver($idSolicitud, $idAdministrador, $accion, $meses = 0, $motivo = "") {
        $idSolicitud = (int)$idSolicitud;
        $idAdministrador = (int)$idAdministrador;
        $meses = (int)$meses;
        $motivo = trim((string)$motivo);

        if ($idSolicitud <= 0 || $idAdministrador <= 0 || !in_array($accion, ["aprobar", "rechazar"], true)) {
            throw new DomainException("La solicitud o la acción no es válida.");
        }

        if ($accion === "aprobar" && !in_array($meses, [1, 3, 6, 12], true)) {
            throw new DomainException("Seleccioná una duración válida.");
        }

        if (mb_strlen($motivo) > 500) {
            throw new DomainException("El motivo no puede superar los 500 caracteres.");
        }

        require __DIR__ . "/../bd/conexion.php";

        mysqli_begin_transaction($conexion);

        try {
            // COMPROBAMOS EL ROL DEL USUARIO QUE REALIZA LA OPERACION

            $consulta = mysqli_prepare($conexion, "SELECT Rol_Usuario FROM usuario WHERE idUsuario = ? FOR UPDATE");
            mysqli_stmt_bind_param($consulta, "i", $idAdministrador);
            mysqli_stmt_execute($consulta);
            $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            if (!$admin || $admin["Rol_Usuario"] !== "administrador") {
                throw new DomainException("No tenés permisos para administrar solicitudes.");
            }

            // BLOQUEAMOS LA SOLICITUD Y OBTENEMOS EL PLAN ACTUAL

            $sql = "SELECT s.idUsuario, s.Estado, u.PlanTipo, u.FechaVencimiento
                    FROM solicitudplanpro s
                    INNER JOIN usuario u ON u.idUsuario = s.idUsuario
                    WHERE s.idSolicitud = ?
                    FOR UPDATE";

            $consulta = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($consulta, "i", $idSolicitud);
            mysqli_stmt_execute($consulta);
            $solicitud = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            if (!$solicitud || $solicitud["Estado"] !== "pendiente") {
                throw new DomainException("La solicitud ya fue procesada o no existe.");
            }

            $idUsuario = (int)$solicitud["idUsuario"];
            $zona = new DateTimeZone("America/Argentina/San_Luis");
            $ahora = new DateTimeImmutable("now", $zona);
            $fechaResolucion = $ahora->format("Y-m-d H:i:s");

            if ($accion === "aprobar") {
                // SUMAMOS LOS MESES A LA VIGENCIA ACTUAL O A LA FECHA DE APROBACION

                $hoy = $ahora->format("Y-m-d");
                $vencimientoActual = substr((string)$solicitud["FechaVencimiento"], 0, 10);

                $fechaBase = strtolower((string)$solicitud["PlanTipo"]) === "pro"
                    && $vencimientoActual >= $hoy
                    ? new DateTimeImmutable($vencimientoActual, $zona)
                    : new DateTimeImmutable($hoy, $zona);

                $fechaVencimiento = $fechaBase->add(new DateInterval("P{$meses}M"))->format("Y-m-d");

                $consulta = mysqli_prepare($conexion, "UPDATE usuario SET PlanTipo = 'pro', FechaVencimiento = ? WHERE idUsuario = ?");
                mysqli_stmt_bind_param($consulta, "si", $fechaVencimiento, $idUsuario);
                mysqli_stmt_execute($consulta);
                mysqli_stmt_close($consulta);

                $sql = "UPDATE solicitudplanpro
                        SET Estado = 'aprobada', MesesAprobados = ?, FechaResolucion = ?,
                            FechaVencimientoAsignada = ?, MotivoRechazo = NULL, idAdministrador = ?
                        WHERE idSolicitud = ? AND Estado = 'pendiente'";

                $consulta = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($consulta, "issii", $meses, $fechaResolucion, $fechaVencimiento, $idAdministrador, $idSolicitud);
            } else {
                // REGISTRAMOS EL RECHAZO SIN MODIFICAR EL PLAN DEL USUARIO

                $sql = "UPDATE solicitudplanpro
                        SET Estado = 'rechazada', MesesAprobados = NULL, FechaResolucion = ?,
                            FechaVencimientoAsignada = NULL, MotivoRechazo = ?, idAdministrador = ?
                        WHERE idSolicitud = ? AND Estado = 'pendiente'";

                $consulta = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($consulta, "ssii", $fechaResolucion, $motivo, $idAdministrador, $idSolicitud);
            }

            mysqli_stmt_execute($consulta);

            if (mysqli_stmt_affected_rows($consulta) !== 1) {
                throw new RuntimeException("No se pudo actualizar el estado de la solicitud.");
            }

            mysqli_stmt_close($consulta);
            mysqli_commit($conexion);

            return $accion === "aprobar" ? "aprobada" : "rechazada";

        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            throw $e;
        } finally {
            mysqli_close($conexion);
        }
    }


}

?>