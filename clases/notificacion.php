<?php

class Notificacion {



    // OBTENER CONFIGURACION DEL USUARIO

    public static function obtenerConfiguracion($idUsuario) {

        require __DIR__ . "/../bd/conexion.php";

        $sql = "SELECT * FROM configuracionnotificacion WHERE idUsuario = $idUsuario";

        $resultado = mysqli_query($conexion, $sql);


        // SI TODAVIA NO TIENE CONFIGURACION, CREARLA

        if (mysqli_num_rows($resultado) == 0) {

            $sql = "INSERT INTO configuracionnotificacion (idUsuario) VALUES ($idUsuario)";

            mysqli_query($conexion, $sql);

            $configuracion = array(
                "PreguntasCreadas" => 1,
                "Respuestas" => 1,
                "PreguntasCerradas" => 1,
                "RespuestasDestacadas" => 1,
                "PreguntasEliminadas" => 1
            );

            mysqli_close($conexion);

            return $configuracion;
        }


        $configuracion = mysqli_fetch_assoc($resultado);

        mysqli_close($conexion);

        return $configuracion;
    }



    // GUARDAR CONFIGURACION

    public static function guardarConfiguracion(
        $idUsuario,
        $PreguntasCreadas,
        $Respuestas,
        $PreguntasCerradas,
        $RespuestasDestacadas,
        $PreguntasEliminadas
    ) {

        require __DIR__ . "/../bd/conexion.php";

        $PreguntasCreadas = $PreguntasCreadas ? 1 : 0;
        $Respuestas = $Respuestas ? 1 : 0;
        $PreguntasCerradas = $PreguntasCerradas ? 1 : 0;
        $RespuestasDestacadas = $RespuestasDestacadas ? 1 : 0;
        $PreguntasEliminadas = $PreguntasEliminadas ? 1 : 0;


        $sql = "INSERT INTO configuracionnotificacion
                (
                    idUsuario,
                    PreguntasCreadas,
                    Respuestas,
                    PreguntasCerradas,
                    RespuestasDestacadas,
                    PreguntasEliminadas
                )
                VALUES
                (
                    $idUsuario,
                    $PreguntasCreadas,
                    $Respuestas,
                    $PreguntasCerradas,
                    $RespuestasDestacadas,
                    $PreguntasEliminadas
                )

                ON DUPLICATE KEY UPDATE

                PreguntasCreadas = $PreguntasCreadas,
                Respuestas = $Respuestas,
                PreguntasCerradas = $PreguntasCerradas,
                RespuestasDestacadas = $RespuestasDestacadas,
                PreguntasEliminadas = $PreguntasEliminadas";


        $resultado = mysqli_query($conexion, $sql);

        mysqli_close($conexion);

        return $resultado;
    }



    // COMPROBAR SI EL ESTADO ESTA HABILITADO

    private static function estadoHabilitado($estado, $configuracion) {

        if (($estado == 1 || $estado == 2) && !$configuracion["PreguntasCreadas"]) {
            return false;
        }

        if (($estado == 3 || $estado == 4) && !$configuracion["Respuestas"]) {
            return false;
        }

        if ($estado == 5 && !$configuracion["PreguntasCerradas"]) {
            return false;
        }

        if ($estado == 6 && !$configuracion["RespuestasDestacadas"]) {
            return false;
        }

        if ($estado == 7 && !$configuracion["PreguntasEliminadas"]) {
            return false;
        }

        return true;
    }



    // OBTENER NOTIFICACIONES DEL USUARIO

    public static function obtenerNotificaciones($idUsuario, $limite = 30, $soloNoLeidas = false) {

        require __DIR__ . "/../bd/conexion.php";

        $condicionLeida = "";

        if ($soloNoLeidas) {
            $condicionLeida = "AND notificacion.Leida = 0";
        }


        $sql = "SELECT
                    notificacion.*,
                    usuario.Nombre AS NombreActor,
                    usuario.Apellido AS ApellidoActor

                FROM notificacion

                LEFT JOIN usuario
                ON notificacion.idUsuarioActor = usuario.idUsuario

                WHERE notificacion.idUsuario = $idUsuario
                $condicionLeida

                ORDER BY notificacion.Fecha DESC

                LIMIT 100";


        $resultado = mysqli_query($conexion, $sql);

        $configuracion = self::obtenerConfiguracion($idUsuario);

        $notificaciones = array();


        while ($fila = mysqli_fetch_assoc($resultado)) {

            if (self::estadoHabilitado($fila["Estado"], $configuracion)) {

                $notificaciones[] = $fila;
            }

            if (count($notificaciones) >= $limite) {
                break;
            }
        }


        mysqli_close($conexion);

        return $notificaciones;
    }

    // OBTENER TIEMPO TRANSCURRIDO

    public static function obtenerTiempoTranscurrido($fecha) {

        $fechaNotificacion = new DateTime($fecha);
        $ahora = new DateTime();

        $segundos = $ahora->getTimestamp() - $fechaNotificacion->getTimestamp();


        if ($segundos < 60) {
            return "Hace menos de 1 minuto";
        }


        $minutos = floor($segundos / 60);

        if ($minutos < 60) {

            if ($minutos == 1) {
                return "Hace 1 minuto";
            }

            return "Hace " . $minutos . " minutos";
        }


        $horas = floor($minutos / 60);

        if ($horas < 24) {

            if ($horas == 1) {
                return "Hace 1 hora";
            }

            return "Hace " . $horas . " horas";
        }


        $dias = floor($horas / 24);

        if ($dias == 1) {
            return "Hace 1 día";
        }

        return "Hace " . $dias . " días";
    }

    // TEXTO SEGUN EL ESTADO

    public static function obtenerTexto($notificacion) {

        $estado = $notificacion["Estado"];

        $nombreActor = trim(
            $notificacion["NombreActor"] . " " .
            $notificacion["ApellidoActor"]
        );


        if ($nombreActor == "") {
            $nombreActor = "Un usuario";
        }


        if ($estado == 1) {
            return $nombreActor . " creó una nueva pregunta:";
        }

        if ($estado == 2) {
            return "Creaste una nueva pregunta:";
        }

        if ($estado == 3) {
            return $nombreActor . " respondió a tu pregunta:";
        }

        if ($estado == 4) {
            return $nombreActor . " respondió la pregunta:";
        }

        if ($estado == 5) {
            return "Se cerró la pregunta:";
        }

        if ($estado == 6) {
            return "Se cerró la pregunta y tu respuesta quedó destacada:";
        }

        if ($estado == 7) {
            return "El docente eliminó tu pregunta:";
        }


        return "Nueva notificación";
    }



    // CONTAR NOTIFICACIONES NO LEIDAS

    public static function contarNoLeidas($idUsuario) {

        $notificaciones = self::obtenerNotificaciones(
            $idUsuario,
            100,
            true
        );

        return count($notificaciones);
    }



    // MARCAR NOTIFICACIONES COMO LEIDAS

    public static function marcarComoLeidas($idUsuario, $idsNotificaciones) {

        if (count($idsNotificaciones) == 0) {
            return;
        }


        $idsLimpios = array();

        foreach ($idsNotificaciones as $idNotificacion) {

            $idNotificacion = intval($idNotificacion);

            if ($idNotificacion > 0) {
                $idsLimpios[] = $idNotificacion;
            }
        }


        if (count($idsLimpios) == 0) {
            return;
        }


        require __DIR__ . "/../bd/conexion.php";

        $listaIds = implode(",", $idsLimpios);


        $sql = "UPDATE notificacion
                SET Leida = 1
                WHERE idUsuario = $idUsuario
                AND idNotificacion IN ($listaIds)";


        mysqli_query($conexion, $sql);

        mysqli_close($conexion);
    }
}



?>