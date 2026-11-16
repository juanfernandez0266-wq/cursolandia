
<?php

// PREPARAMOS LAS NOTIFICACIONES DE INSCRIPCION

if (
    session_status() === PHP_SESSION_ACTIVE &&
    isset($_SESSION["idUsuario"])
):

    if (!class_exists("Curso", false)) {
        require_once __DIR__ . "/../clases/curso.php";
    }

    if (empty($_SESSION["token_avisos_inscripcion"])) {
        $_SESSION["token_avisos_inscripcion"] = bin2hex(random_bytes(32));
    }

    try {

        $avisosInscripcion =
            Curso::obtenerAvisosInscripcionCurso(
                (int)$_SESSION["idUsuario"]
            );

    } catch (Throwable $e) {

        error_log($e->getMessage());
        $avisosInscripcion = [];
    }

?>

<?php if ($avisosInscripcion): ?>

    <style>

        .curso-avisos-inscripcion {
            padding: 10px 2px;
        }

        .curso-avisos-inscripcion h3 {
            margin: 6px 12px;
            font-size: 14px;
            font-weight: 600;
            color: #17213c;
        }

        .curso-aviso-inscripcion {
            margin: 0;
            width: 100%;
        }

        .curso-aviso-inscripcion button {
            display: block;
            width: 100%;
            padding: 12px;
            text-align: left;
            border: 0;
            border-bottom: 1px solid #e8ebef;
            background: transparent;
            color: #394860;
            cursor: pointer;
            font-size: 13px;
            line-height: 1.5;
        }

        .curso-aviso-inscripcion button:hover {
            background: #f7f9fc;
        }

        .curso-aviso-inscripcion button strong {
            display: block;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .curso-aviso-inscripcion button span {
            overflow-wrap: anywhere;
        }

        .curso-aviso-inscripcion button time {
            display: block;
            color: #8995a5;
            font-size: 11px;
            margin-top: 5px;
        }

        .curso-aviso-inscripcion button.no-leida {
            background: #eff6ff;
        }

    </style>


    <div class="curso-avisos-inscripcion">

        <h3>Novedades del curso</h3>


        <?php foreach ($avisosInscripcion as $aviso): ?>

            <form
                class="curso-aviso-inscripcion"
                method="POST"
                action="abrir_notificacion_inscripcion.php"
            >

                <input
                    type="hidden"
                    name="token_avisos_inscripcion"
                    value="<?php echo htmlspecialchars($_SESSION["token_avisos_inscripcion"], ENT_QUOTES, "UTF-8"); ?>"
                >

                <input
                    type="hidden"
                    name="idNotificacion"
                    value="<?php echo (int)$aviso["idNotificacion"]; ?>"
                >


                <button
                    type="submit"
                    class="<?php echo (int)$aviso["Leida"] === 0 ? "no-leida" : ""; ?>"
                >

                    
                    <strong>
                        <?php

                        // IDENTIFICAMOS EL TIPO DE NOTIFICACION

                        $tipoAviso = (int)$aviso["Estado"];

                        echo match ($tipoAviso) {
                            8 => "Nueva solicitud",
                            9 => "Inscripción aprobada",
                            10 => "Inscripción rechazada",
                            11 => "Nuevo material",
                            default => "Notificación"
                        };

                        ?>
                    </strong>


                    <span>
                        <?php
                        echo htmlspecialchars(
                            (string)$aviso["TextoReferencia"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>
                    </span>

                    <time>
                        <?php
                        echo htmlspecialchars(
                            date(
                                "d/m/Y H:i",
                                strtotime($aviso["Fecha"])
                            ),
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>
                    </time>

                </button>

            </form>

        <?php endforeach; ?>

    </div>

<?php endif; ?>
<?php endif; ?>
