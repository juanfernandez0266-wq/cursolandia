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

        if (self::tienePendiente($this->idUsuario)) {
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

}

?>