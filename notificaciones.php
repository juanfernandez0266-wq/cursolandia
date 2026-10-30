<?php

session_start();

require "clases/usuario.php";
require "clases/notificacion.php";



// COMPROBAR SI EL USUARIO INICIO SESION

if (!isset($_SESSION["idUsuario"])) {

    $_SESSION["volverDespuesLogin"] = "notificaciones.php";

    header("Location: iniciar_sesion.php");
    exit;
}


$idUsuario = $_SESSION["idUsuario"];

$usuarioActual = Usuario::buscarPorId($idUsuario);


if ($usuarioActual == null) {

    session_unset();
    session_destroy();

    header("Location: iniciar_sesion.php");
    exit;
}



// OBTENER CONFIGURACION

$configuracion = Notificacion::obtenerConfiguracion($idUsuario);

$configuracionGuardada = false;



// GUARDAR CONFIGURACION

if (isset($_POST["guardar_configuracion"])) {

    $PreguntasCreadas = isset($_POST["PreguntasCreadas"]);
    $Respuestas = isset($_POST["Respuestas"]);
    $PreguntasCerradas = isset($_POST["PreguntasCerradas"]);
    $RespuestasDestacadas = isset($_POST["RespuestasDestacadas"]);
    $PreguntasEliminadas = isset($_POST["PreguntasEliminadas"]);


    $resultado = Notificacion::guardarConfiguracion(
        $idUsuario,
        $PreguntasCreadas,
        $Respuestas,
        $PreguntasCerradas,
        $RespuestasDestacadas,
        $PreguntasEliminadas
    );


    if ($resultado) {

        $configuracion = Notificacion::obtenerConfiguracion($idUsuario);

        $configuracionGuardada = true;
    }
}

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notificaciones - Cursolandia</title>


    <!-- Bootstrap CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- CSS PROPIO -->

    <link rel="stylesheet" href="css/notificaciones.css">


    <!-- CSS MENU LATERAL -->

    <link rel="stylesheet" href="css/menu_lateral.css?v=2">


    <!-- CSS NOTIFICACIONES RAPIDAS -->

    <link rel="stylesheet" href="css/notificaciones_rapidas.css">

</head>


<body>


    <!-- MENU LATERAL -->

    <?php include "componentes/menu_lateral.php"; ?>


    <!-- NOTIFICACIONES RAPIDAS -->

    <?php include "componentes/notificaciones_rapidas.php"; ?>



    <!-- CONTENIDO PRINCIPAL -->

    <main class="notificaciones-pagina contenido-con-menu">


        <div class="container py-5">


            <h1 class="titulo-pagina">Notificaciones</h1>



            <!-- CONFIGURACION DE NOTIFICACIONES -->

            <section class="configuracion-notificaciones">


                <h2>Configuración de notificaciones</h2>

                <p>Elegí qué tipo de notificaciones querés visualizar.</p>


                <form method="POST" action="notificaciones.php">



                    <!-- PREGUNTAS CREADAS -->

                    <div class="configuracion-fila">

                        <span>Mostrar notificaciones de preguntas creadas</span>

                        <label class="interruptor">
                            <input type="checkbox" name="PreguntasCreadas" <?php if ($configuracion["PreguntasCreadas"]) { echo "checked"; } ?>>
                            <span class="interruptor-control"></span>
                        </label>

                    </div>



                    <!-- RESPUESTAS -->

                    <div class="configuracion-fila">

                        <span>Mostrar notificaciones de respuestas</span>

                        <label class="interruptor">
                            <input type="checkbox" name="Respuestas" <?php if ($configuracion["Respuestas"]) { echo "checked"; } ?>>
                            <span class="interruptor-control"></span>
                        </label>

                    </div>



                    <!-- PREGUNTAS CERRADAS -->

                    <div class="configuracion-fila">

                        <span>Mostrar notificaciones de preguntas cerradas</span>

                        <label class="interruptor">
                            <input type="checkbox" name="PreguntasCerradas" <?php if ($configuracion["PreguntasCerradas"]) { echo "checked"; } ?>>
                            <span class="interruptor-control"></span>
                        </label>

                    </div>



                    <!-- RESPUESTAS DESTACADAS -->

                    <div class="configuracion-fila">

                        <span>Mostrar notificaciones de respuestas destacadas</span>

                        <label class="interruptor">
                            <input type="checkbox" name="RespuestasDestacadas" <?php if ($configuracion["RespuestasDestacadas"]) { echo "checked"; } ?>>
                            <span class="interruptor-control"></span>
                        </label>

                    </div>



                    <!-- PREGUNTAS ELIMINADAS -->

                    <div class="configuracion-fila">

                        <span>Mostrar notificaciones de preguntas eliminadas</span>

                        <label class="interruptor">
                            <input type="checkbox" name="PreguntasEliminadas" <?php if ($configuracion["PreguntasEliminadas"]) { echo "checked"; } ?>>
                            <span class="interruptor-control"></span>
                        </label>

                    </div>



                    <!-- GUARDAR -->

                    <button type="submit" name="guardar_configuracion" class="btn btn-primary mt-4">Guardar configuración</button>


                    <?php

                    if ($configuracionGuardada) {
                        echo '<p class="text-success mt-3">Configuración guardada correctamente.</p>';
                    }

                    ?>


                </form>


            </section>


        </div>


    </main>



    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>


    <!-- JS MENU LATERAL -->

    <script src="js/menu_lateral.js"></script>


    <!-- JS NOTIFICACIONES RAPIDAS -->

    <script src="js/notificaciones_rapidas.js"></script>


</body>

</html>