<?php

require_once __DIR__ . "/../clases/notificacion.php";
require_once __DIR__ . "/../clases/curso.php";

?>


<div class="notificaciones-rapidas">


    <?php

    // SI NO INICIO SESION

    if (!isset($_SESSION["idUsuario"])) {

    ?>

        <!-- CAMPANA -->

        <button type="button" class="boton-campana" id="botonCampana">
            <img src="imagenes/notificaciones/campana.svg" class="icono-campana" id="iconoCampana" alt="Notificaciones">
        </button>


        <!-- PANEL DE NOTIFICACIONES -->

        <div class="panel-notificaciones" id="panelNotificaciones">

            <div class="panel-notificaciones-header">
                <h3>Notificaciones</h3>
            </div>

            <p class="panel-sin-notificaciones">Iniciá sesión para ver tus notificaciones.</p>

            <a href="iniciar_sesion.php" class="ver-notificaciones">Iniciar sesión</a>

        </div>

    <?php

    }


    // SI INICIO SESION

    else {

        $idUsuarioNotificacion = $_SESSION["idUsuario"];

        $cantidadNoLeidas = Notificacion::contarNoLeidas($idUsuarioNotificacion);

        $notificacionesRapidas = Notificacion::obtenerNotificaciones(
            $idUsuarioNotificacion,
            20
        );

        // OBTENEMOS LOS AVISOS DE INSCRIPCION

        $avisosInscripcion = Curso::obtenerAvisosInscripcionCurso(
            $idUsuarioNotificacion
        );

        // EVITAMOS DUPLICAR LAS NOTIFICACIONES DE INSCRIPCION

        $notificacionesRapidas = array_values(array_filter(
            $notificacionesRapidas,
            function ($notificacion) {
                return !in_array((int)$notificacion["Estado"], [8, 9, 10,11], true);
            }
        ));


        // SEPARAR NOTIFICACIONES POR TIEMPO

        $notificacionesRecientes = array();
        $masNotificaciones = array();

        $ahora = time();


        foreach ($notificacionesRapidas as $notificacion) {

            $fechaNotificacion = strtotime($notificacion["Fecha"]);

            $diferencia = $ahora - $fechaNotificacion;


            // MENOS DE 24 HORAS

            if ($diferencia < 86400) {
                $notificacionesRecientes[] = $notificacion;
            }


            // 24 HORAS O MAS

            else {
                $masNotificaciones[] = $notificacion;
            }
        }


        // ELEGIR ICONO DE CAMPANA

        if ($cantidadNoLeidas > 0) {
            $iconoCampana = "imagenes/notificaciones/campana_notificacion.svg";
        }
        else {
            $iconoCampana = "imagenes/notificaciones/campana.svg";
        }

    ?>


        <!-- CAMPANA -->

        <button type="button" class="boton-campana" id="botonCampana">
            <img src="<?php echo $iconoCampana; ?>" class="icono-campana" id="iconoCampana" alt="Notificaciones">
        </button>



        <!-- PANEL DE NOTIFICACIONES -->

        <div class="panel-notificaciones" id="panelNotificaciones">


            <div class="panel-notificaciones-header">

                <h3>Notificaciones</h3>

                <a href="notificaciones.php" class="boton-configuracion" title="Configuración">
                    <img src="imagenes/notificaciones/configuracion/configuracion.svg" class="icono-configuracion" alt="Configuración">
                </a>

            </div>



            <?php

            // SI NO HAY NOTIFICACIONES

            if (count($notificacionesRapidas) == 0 && count($avisosInscripcion) == 0) {

                echo '<p class="panel-sin-notificaciones">No hay notificaciones.</p>';

            }


            // SI HAY NOTIFICACIONES

            else {

            ?>


                <!-- MAS RECIENTES -->

                <?php

                if (count($notificacionesRecientes) > 0) {

                ?>

                    <h4>Más recientes</h4>


                    <?php

                    foreach ($notificacionesRecientes as $notificacion) {

                        $texto = Notificacion::obtenerTexto($notificacion);

                    ?>

                        <a href="#" class="notificacion-rapida" data-id="<?php echo $notificacion["idNotificacion"]; ?>">

                            <div class="notificacion-rapida-icono">C</div>

                            <div class="notificacion-rapida-contenido">

                                <strong>Curso <?php echo $notificacion["idCurso"]; ?></strong>

                                <p><?php echo htmlspecialchars($texto); ?></p>

                                <span><?php echo htmlspecialchars($notificacion["TextoReferencia"]); ?></span>

                                <small class="notificacion-tiempo"><?php echo Notificacion::obtenerTiempoTranscurrido($notificacion["Fecha"]); ?></small>

                            </div>

                        </a>

                    <?php

                    }

                    ?>

                <?php

                }

                ?>



                <!-- MAS NOTIFICACIONES -->

                <?php

                if (count($masNotificaciones) > 0) {

                ?>

                    <h4 class="titulo-mas-notificaciones">Más notificaciones</h4>


                    <?php

                    foreach ($masNotificaciones as $notificacion) {

                        $texto = Notificacion::obtenerTexto($notificacion);

                    ?>

                        <a href="#" class="notificacion-rapida" data-id="<?php echo $notificacion["idNotificacion"]; ?>">

                            <div class="notificacion-rapida-icono">C</div>

                            <div class="notificacion-rapida-contenido">

                                <strong>Curso <?php echo $notificacion["idCurso"]; ?></strong>

                                <p><?php echo htmlspecialchars($texto); ?></p>

                                <span><?php echo htmlspecialchars($notificacion["TextoReferencia"]); ?></span>

                                <small class="notificacion-tiempo"><?php echo Notificacion::obtenerTiempoTranscurrido($notificacion["Fecha"]); ?></small>

                            </div>

                        </a>

                    <?php

                    }

                    ?>

                <?php

                }

                                ?>

            <?php

            }

            ?>


            <!-- MOSTRAMOS LAS NOTIFICACIONES DE INSCRIPCION -->

            <?php include __DIR__ . "/notificaciones_inscripcion.php"; ?>


        </div>


    <?php

    }

    ?>


</div>