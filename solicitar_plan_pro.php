
<?php

session_start();

require_once __DIR__ . "/clases/solicitudplanpro.php";


// COMPROBAR SESION

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php");
    exit;
}

$idUsuario = (int) $_SESSION["idUsuario"];

// MODO DE PRUEBA PARA VISUALIZAR EL FORMULARIO
$modoPrueba = false;

// TOKEN DE SEGURIDAD

if (!isset($_SESSION["token_plan_pro"])) {
    $_SESSION["token_plan_pro"] = bin2hex(random_bytes(32));
}


// VARIABLES

$Mensaje = "";
$error = "";
$maximoArchivo = 10 * 1024 * 1024;

$idEnviado = $_SESSION["solicitud_pro_enviada"] ?? null;
unset($_SESSION["solicitud_pro_enviada"]);

$hayPendiente = SolicitudPlanPro::tienePendiente($idUsuario);


// ENVIAR SOLICITUD

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirmar_solicitud"])) {

    $Mensaje = trim($_POST["Mensaje"] ?? "");

    if (!hash_equals($_SESSION["token_plan_pro"], $_POST["token_plan_pro"] ?? "")) {

        $error = "La solicitud no pudo verificarse. Recargá la página.";

    } elseif ($hayPendiente) {

        $error = "Ya tenés una solicitud de Plan Pro pendiente.";

    } elseif (!isset($_FILES["Comprobante"]) || $_FILES["Comprobante"]["error"] != UPLOAD_ERR_OK) {

        $error = "Debés adjuntar un comprobante válido.";

    } else {

        $archivo = $_FILES["Comprobante"];
        $nombreOriginal = basename(str_replace("\\", "/", $archivo["name"]));
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        $tiposPermitidos = [
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png",
            "pdf" => "application/pdf"
        ];


        // VALIDAR ARCHIVO

        if ($archivo["size"] <= 0 || $archivo["size"] > $maximoArchivo) {

            $error = "El comprobante debe tener un tamaño máximo de 10 MB.";

        } elseif (!is_uploaded_file($archivo["tmp_name"])) {

            $error = "No se pudo verificar el archivo enviado.";

        } else {

            $detector = new finfo(FILEINFO_MIME_TYPE);
            $tipoArchivo = $detector->file($archivo["tmp_name"]);

            if (!isset($tiposPermitidos[$extension]) || $tiposPermitidos[$extension] != $tipoArchivo) {
                $error = "Solo se permiten archivos PNG, JPG o PDF.";
            }
        }


        // GUARDAR COMPROBANTE Y SOLICITUD

        if ($error == "") {

            // CARPETA FUERA DE HTDOCS

            $carpeta = dirname(__DIR__, 2) . "/cursolandia_comprobantes";

            if (!is_dir($carpeta) && !mkdir($carpeta, 0700, true)) {

                $error = "No se pudo crear la carpeta de comprobantes.";

            } else {

                $nombreInterno = bin2hex(random_bytes(16)) . "." . $extension;
                $rutaArchivo = $carpeta . "/" . $nombreInterno;

                if (!move_uploaded_file($archivo["tmp_name"], $rutaArchivo)) {

                    $error = "No se pudo guardar el comprobante.";

                } else {

                    try {

                        $solicitud = new SolicitudPlanPro(
                            $idUsuario,
                            $Mensaje == "" ? null : $Mensaje,
                            $nombreInterno,
                            $nombreOriginal
                        );

                        $idSolicitud = $solicitud->crear();

                        if (!$idSolicitud) {
                            throw new RuntimeException("No se registró la solicitud.");
                        }

                        $_SESSION["solicitud_pro_enviada"] = $idSolicitud;

                        header("Location: solicitar_plan_pro.php");
                        exit;

                    } catch (Throwable $e) {

                        // ELIMINAR ARCHIVO SI FALLA EL REGISTRO

                        unlink($rutaArchivo);

                        error_log($e->getMessage());

                        $error = "No se pudo registrar la solicitud. Intentá nuevamente.";

                    }
                }
            }
        }
    }
}


// CONSULTAR SOLICITUD PENDIENTE

$solicitudActual = null;

if ($hayPendiente || $idEnviado != null) {

    $solicitudes = SolicitudPlanPro::obtenerPorUsuario($idUsuario);

    foreach ($solicitudes as $solicitud) {

        if (
            ($idEnviado != null && $solicitud["idSolicitud"] == $idEnviado)
            || ($idEnviado == null && $solicitud["Estado"] == "pendiente")
        ) {
            $solicitudActual = $solicitud;
            break;
        }
    }
}

$paginaActual = basename($_SERVER["PHP_SELF"]);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Solicitar Plan Pro - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menu_lateral.css">
    <link rel="stylesheet" href="css/solicitar_plan_pro.css?v=3">
</head>

<body>

    <?php include __DIR__ . "/componentes/menu_lateral.php"; ?>

    <main class="container py-5 pagina-solicitud-pro">

        
        <?php if ($solicitudActual != null && !$modoPrueba): ?>

            <!-- SOLICITUD REGISTRADA -->

            <h1><?php echo $idEnviado != null ? "Solicitud enviada correctamente" : "Solicitud de Plan Pro"; ?></h1>

            <p>Tu solicitud se encuentra registrada en Cursolandia.</p>

            <p><strong>ID de solicitud:</strong> <?php echo $solicitudActual["idSolicitud"]; ?></p>
            <p><strong>ID de usuario:</strong> <?php echo $solicitudActual["idUsuario"]; ?></p>
            <p><strong>Mensaje:</strong> <?php echo nl2br(htmlspecialchars($solicitudActual["Mensaje"] ?? "Sin mensaje")); ?></p>
            <p><strong>Comprobante:</strong> <?php echo htmlspecialchars($solicitudActual["NombreComprobante"]); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($solicitudActual["Estado"]); ?></p>
            <p><strong>Fecha:</strong> <?php echo htmlspecialchars($solicitudActual["FechaSolicitud"]); ?></p>


        <?php else: ?>

            <!-- FORMULARIO DE SOLICITUD -->

            <h1>Solicitud de Plan Pro</h1>

            <?php if ($error != ""): ?>
                <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form id="formularioSolicitud" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="token_plan_pro" value="<?php echo htmlspecialchars($_SESSION["token_plan_pro"]); ?>">


                <!-- PASO 1: SOLICITUD -->

                <section id="pasoSolicitud">

                    <h2>Solicitá la activación de tu Plan Pro</h2>

                    <p>Una vez realizado el pago, enviá el comprobante para que un administrador pueda verificarlo.</p>

                    <div class="mb-4">

                        <label for="Mensaje" class="form-label">Mensaje (opcional)</label>

                        <textarea id="Mensaje" name="Mensaje" class="form-control" rows="4" placeholder="Escribí un mensaje para el administrador..."><?php echo htmlspecialchars($Mensaje); ?></textarea>

                    </div>

                    <!-- COMPROBANTE DE TRANSFERENCIA -->

                    <div class="mb-4">

                        <label for="Comprobante" class="form-label">Comprobante de transferencia</label>

                        <input type="file" id="Comprobante" name="Comprobante" class="comprobante-input" accept=".jpg,.jpeg,.png,.pdf">

                        <label for="Comprobante" class="comprobante-zona">

                            <img src="imagenes/subir_comprobante.svg" class="comprobante-icono" alt="">

                            <span class="comprobante-titulo" id="nombreComprobante">Subir comprobante</span>

                            <span class="comprobante-descripcion">PNG, JPG o PDF (máx. 10 MB)</span>

                        </label>

                        <p class="text-danger" id="errorComprobante" hidden></p>

                    </div>

                    <button type="button" id="continuarSolicitud" class="btn btn-primary">Continuar</button>

                </section>


                <!-- PASO 2: DETALLE -->

                <section id="pasoDetalle" hidden>

                    <div class="detalle-encabezado">
                        <p class="detalle-etapa">PASO 2 DE 2 · REVISIÓN</p>
                        <h2>Detalle de la solicitud</h2>
                        <p>Revisá los datos antes de enviar tu solicitud de Plan Pro.</p>
                    </div>


                    <!-- DATOS DE LA SOLICITUD -->

                    <div class="detalle-resumen">

                        <div class="detalle-dato">
                            <span class="detalle-etiqueta">ID de usuario</span>
                            <p><?php echo $idUsuario; ?></p>
                        </div>

                        <div class="detalle-dato">
                            <span class="detalle-etiqueta">Mensaje</span>
                            <p id="detalleMensaje"></p>
                        </div>

                        <div class="detalle-dato">
                            <span class="detalle-etiqueta">Comprobante de transferencia</span>

                            <div class="detalle-archivo">
                                <img src="imagenes/subir_comprobante.svg" class="detalle-icono" alt="">

                                <div>
                                    <p id="detalleComprobante"></p>
                                    <span>Archivo seleccionado</span>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- ACLARACION -->

                    <p class="detalle-aclaracion">
                        La solicitud se registrará cuando confirmes el envío. El administrador deberá verificar el comprobante.
                    </p>


                    <!-- NAVEGACION -->

                    <div class="detalle-acciones">
                        <button type="button" id="volverSolicitud" class="btn btn-outline-secondary">Volver</button>
                        <button type="submit" name="confirmar_solicitud" value="1" class="btn btn-primary" <?php if (!empty($modoPrueba)) echo "disabled"; ?>>Enviar solicitud</button>
                    </div>

                </section>

            </form>

        <?php endif; ?>

    </main>

    <script src="js/menu_lateral.js"></script>
    <script src="js/solicitar_plan_pro.js?v=3"></script>

</body>
</html>
