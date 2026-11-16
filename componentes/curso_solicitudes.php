
<?php

// COMPROBAMOS QUE SOLAMENTE EL CREADOR PUEDA VER LAS SOLICITUDES

if (
    !isset($accesoPermitido, $esCreador) || !$accesoPermitido || !$esCreador
) {

    http_response_code(403);
    exit("Acceso no autorizado.");
}

?>

<section class="curso-interno-seccion">

    <!-- MOSTRAMOS LAS SOLICITUDES DE INSCRIPCION -->

    <div class="curso-interno-introduccion">

        <h2>Solicitudes de inscripción</h2>

        <p>Revisá las personas que solicitaron ingresar al curso.</p>

    </div>


    <!-- MOSTRAMOS LOS MENSAJES DE RESULTADO -->

    <?php if ($mensajeSolicitudes !== ""): ?>

        <div class="alert alert-success mt-3">
            <?php echo escaparCurso($mensajeSolicitudes); ?>
        </div>

    <?php endif; ?>


    <?php if ($errorSolicitudes !== ""): ?>

        <div class="alert alert-danger mt-3">
            <?php echo escaparCurso($errorSolicitudes); ?>
        </div>

    <?php endif; ?>


    <div class="curso-solicitudes-contenido">

        <h3>
            Solicitudes pendientes
            <span class="curso-solicitudes-numero">
                <?php echo count($solicitudesPendientes); ?>
            </span>
        </h3>


        <?php if (empty($solicitudesPendientes)): ?>

            <p class="curso-solicitudes-vacio">
                No hay solicitudes de inscripción pendientes.
            </p>

        <?php else: ?>


            <?php foreach ($solicitudesPendientes as $solicitud): ?>

                <div class="curso-solicitud-fila">


                    <!-- MOSTRAMOS LA INFORMACION DEL ALUMNO -->

                    <span class="curso-solicitud-avatar">

                        <?php
                        echo mostrarIconoCurso("persona", $iconos)
                            ?: escaparCurso(
                                mb_strtoupper(
                                    mb_substr(
                                        $solicitud["NombreCompleto"],
                                        0,
                                        1
                                    )
                                )
                            );
                        ?>

                    </span>


                    <div class="curso-solicitud-persona">

                        <strong>
                            <?php echo escaparCurso($solicitud["NombreCompleto"]); ?>
                        </strong>

                        <span>
                            Solicitó ingresar el
                            <?php
                            echo escaparCurso(
                                date(
                                    "d/m/Y H:i",
                                    strtotime($solicitud["FechaInscripcion"])
                                )
                            );
                            ?>
                        </span>

                    </div>


                    <!-- MOSTRAMOS LAS ACCIONES -->

                    <div class="curso-solicitud-acciones">


                        <!-- APROBAMOS LA SOLICITUD -->

                        <form
                            method="POST"
                            action="curso.php?idCurso=<?php echo (int)$idCurso; ?>&seccion=solicitudes"
                        >

                            <input type="hidden" name="token_solicitudes"  value="<?php echo escaparCurso($_SESSION["token_solicitudes"]); ?>"  >

                            <input type="hidden" name="idInscripcion"  value="<?php echo (int)$solicitud["idInscripcion"]; ?>"
                            >

                            <button type="submit" name="accionSolicitud" value="aprobar" class="curso-solicitud-aprobar" > Aprobar </button>

                        </form>


                        <!-- RECHAZAMOS CON COMENTARIO OPCIONAL -->

                        <details class="curso-solicitud-rechazo">

                            <summary>Rechazar</summary>


                            <form method="POST" action="curso.php?idCurso=<?php echo (int)$idCurso; ?>&seccion=solicitudes"  >

                                <input type="hidden" name="token_solicitudes"  value="<?php echo escaparCurso($_SESSION["token_solicitudes"]); ?>"  >

                                <input  type="hidden"   name="idInscripcion" value="<?php echo (int)$solicitud["idInscripcion"]; ?>"  >

                                <label for="motivo-<?php echo (int)$solicitud["idInscripcion"]; ?>">
                                    Motivo del rechazo (opcional)
                                </label>

                                <textarea id="motivo-<?php echo (int)$solicitud["idInscripcion"]; ?>" name="motivoRechazo" rows="3" maxlength="500" placeholder="Podés explicarle al alumno por qué rechazaste la solicitud."
                                ></textarea>

                                <button type="submit" name="accionSolicitud" value="rechazar" class="curso-solicitud-confirmar-rechazo" > Confirmar rechazo </button>
                            </form>

                        </details>

                    </div>

                </div>

            <?php endforeach; ?>


        <?php endif; ?>

    </div>

</section>
