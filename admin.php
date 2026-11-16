
<?php

session_start();

require_once __DIR__ . "/clases/usuario.php";
require_once __DIR__ . "/clases/solicitudplanpro.php";

// COMPROBAMOS QUE EL USUARIO SEA ADMINISTRADOR

if (!isset($_SESSION["idUsuario"])) {
    header("Location: iniciar_sesion.php");
    exit;
}

$idAdministrador = (int)$_SESSION["idUsuario"];
$administrador = Usuario::buscarPorId($idAdministrador);

if (!$administrador || !$administrador->esAdministrador()) {
    http_response_code(403);
    exit("No tenés permisos para acceder a Administración.");
}

// PREPARAMOS EL TOKEN Y LOS MENSAJES

if (empty($_SESSION["token_admin"])) {
    $_SESSION["token_admin"] = bin2hex(random_bytes(32));
}

$errorAdmin = "";
$mensajeAdmin = $_SESSION["mensaje_admin"] ?? "";
unset($_SESSION["mensaje_admin"]);

function escaparAdmin($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// PROCESAMOS LA APROBACION O EL RECHAZO

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accionSolicitudPro"])) {
    try {
        $token = $_POST["token_admin"] ?? "";
        $accion = $_POST["accionSolicitudPro"] ?? "";
        $motivo = $_POST["motivoRechazo"] ?? "";
        $idSolicitud = filter_input(INPUT_POST, "idSolicitud", FILTER_VALIDATE_INT);
        $meses = filter_input(INPUT_POST, "meses", FILTER_VALIDATE_INT);

        if (!is_string($token) || !hash_equals($_SESSION["token_admin"], $token)) {
            throw new DomainException("La solicitud no pudo verificarse. Actualizá la página.");
        }

        if (!$idSolicitud || !is_string($accion) || !is_string($motivo)) {
            throw new DomainException("Los datos de la operación no son válidos.");
        }

        $resultado = SolicitudPlanPro::resolver(
            $idSolicitud,
            $idAdministrador,
            $accion,
            $meses ?: 0,
            $motivo
        );

        $_SESSION["mensaje_admin"] = $resultado === "aprobada"
            ? "La solicitud fue aprobada y el Plan Pro quedó activado."
            : "La solicitud fue rechazada correctamente.";

        $_SESSION["token_admin"] = bin2hex(random_bytes(32));

        header("Location: admin.php");
        exit;

    } catch (DomainException $e) {
        $errorAdmin = $e->getMessage();

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorAdmin = "No pudimos procesar la solicitud.";
    }
}

// CONSULTAMOS LAS SOLICITUDES

$pendientes = SolicitudPlanPro::obtenerPendientes();
$historial = SolicitudPlanPro::obtenerHistorial();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menu_lateral.css?v=5">
    <link rel="stylesheet" href="css/solicitar_plan_pro.css?v=3">
    <link rel="stylesheet" href="css/administracion.css?v=5">
</head>
<body>

    <?php include __DIR__ . "/componentes/menu_lateral.php"; ?>

    <main class="contenido-con-menu admin-pagina">
        <div class="pagina-solicitud-pro admin-panel">

        <h1>Administración</h1>

        <div class="detalle-encabezado">
            <h2>Solicitudes de Plan Pro</h2>
            <p>Revisá las solicitudes enviadas por los usuarios y administrá la activación de sus planes.</p>
        </div>

        <?php if ($mensajeAdmin !== ""): ?>
            <div class="alert alert-success"><?php echo escaparAdmin($mensajeAdmin); ?></div>
        <?php endif; ?>

        <?php if ($errorAdmin !== ""): ?>
            <div class="alert alert-danger"><?php echo escaparAdmin($errorAdmin); ?></div>
        <?php endif; ?>

        <!-- MOSTRAMOS SOLAMENTE LAS SOLICITUDES PENDIENTES -->

        <h3 class="admin-subtitulo">Pendientes de revisión</h3>

        <?php if (empty($pendientes)): ?>

            <div class="admin-vacio">No hay solicitudes pendientes de revisión.</div>

        <?php else: ?>

            <?php foreach ($pendientes as $solicitud): ?>

                <details class="admin-solicitud">

                    <summary class="admin-solicitud-resumen">
                        <span class="admin-solicitud-usuario">
                            <strong><?php echo escaparAdmin($solicitud["Nombre"] . " " . $solicitud["Apellido"]); ?></strong>
                            <small><?php echo escaparAdmin($solicitud["email"]); ?></small>
                        </span>

                        <span class="admin-solicitud-estado">Pendiente</span>
                    </summary>

                    <div class="admin-solicitud-contenido">

                        <div class="detalle-resumen">

                            <div class="detalle-dato">
                                <span class="detalle-etiqueta">ID de solicitud</span>
                                <p><?php echo (int)$solicitud["idSolicitud"]; ?></p>
                            </div>

                            <div class="detalle-dato">
                                <span class="detalle-etiqueta">Fecha de solicitud</span>
                                <p><?php echo escaparAdmin(date("d/m/Y H:i", strtotime($solicitud["FechaSolicitud"]))); ?></p>
                            </div>

                            <div class="detalle-dato">
                                <span class="detalle-etiqueta">Mensaje</span>
                                <p><?php echo nl2br(escaparAdmin($solicitud["Mensaje"] ?: "Sin mensaje")); ?></p>
                            </div>

                            <div class="detalle-dato">
                                <span class="detalle-etiqueta">Comprobante de transferencia</span>

                                <a href="ver_comprobante_pro.php?idSolicitud=<?php echo (int)$solicitud["idSolicitud"]; ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="admin-enlace-comprobante">
                                    <img src="imagenes/subir_comprobante.svg" class="detalle-icono" alt="">
                                    <span><?php echo escaparAdmin($solicitud["NombreComprobante"]); ?></span>
                                    <span>Ver archivo</span>
                                </a>
                            </div>

                        </div>

                        <!-- APROBAMOS Y ASIGNAMOS LA DURACION -->

                        <form method="POST" class="admin-formulario">

                            <input type="hidden" name="token_admin" value="<?php echo escaparAdmin($_SESSION["token_admin"]); ?>">
                            <input type="hidden" name="idSolicitud" value="<?php echo (int)$solicitud["idSolicitud"]; ?>">

                            <label class="form-label">Duración del Plan Pro</label>

                            <select name="meses" class="form-control" required>
                                <option value="1">1 mes</option>
                                <option value="3" selected>3 meses</option>
                                <option value="6">6 meses</option>
                                <option value="12">12 meses</option>
                            </select>

                            <button type="submit" name="accionSolicitudPro" value="aprobar"
                                    class="btn btn-primary"
                                    onclick="return confirm('¿Aprobar esta solicitud y activar el Plan Pro?');">
                                Aprobar solicitud
                            </button>

                        </form>

                        <!-- RECHAZAMOS LA SOLICITUD -->

                        <form method="POST" class="admin-formulario admin-formulario-rechazo">

                            <input type="hidden" name="token_admin" value="<?php echo escaparAdmin($_SESSION["token_admin"]); ?>">
                            <input type="hidden" name="idSolicitud" value="<?php echo (int)$solicitud["idSolicitud"]; ?>">

                            <label class="form-label">Motivo de rechazo (opcional)</label>
                            <textarea name="motivoRechazo" class="form-control" rows="3" maxlength="500"
                                      placeholder="Explicá brevemente el motivo..."></textarea>

                            <button type="submit" name="accionSolicitudPro" value="rechazar"
                                    class="btn btn-outline-danger"
                                    onclick="return confirm('¿Rechazar esta solicitud?');">
                                Rechazar solicitud
                            </button>

                        </form>

                    </div>

                </details>

            <?php endforeach; ?>

        <?php endif; ?>

        <!-- MOSTRAMOS EL HISTORIAL SIN OCUPAR LA PANTALLA PRINCIPAL -->

        <details class="admin-historial">
            <summary>Historial de solicitudes</summary>

            <?php if (empty($historial)): ?>

                <p class="admin-vacio">Todavía no hay solicitudes resueltas.</p>

            <?php else: ?>

                <?php foreach ($historial as $solicitud): ?>

                    <div class="admin-historial-registro">
                        <div>
                            <strong><?php echo escaparAdmin($solicitud["Nombre"] . " " . $solicitud["Apellido"]); ?></strong>
                            <p>
                                <?php echo $solicitud["Estado"] === "aprobada" ? "Aprobada" : "Rechazada"; ?>
                                · <?php echo escaparAdmin($solicitud["FechaResolucion"] ?? "Sin fecha"); ?>
                            </p>

                            <?php if ($solicitud["Estado"] === "aprobada"): ?>
                                <p>Duración: <?php echo (int)$solicitud["MesesAprobados"]; ?> meses</p>
                                <p>Vencimiento asignado: <?php echo escaparAdmin($solicitud["FechaVencimientoAsignada"]); ?></p>
                            <?php elseif (!empty($solicitud["MotivoRechazo"])): ?>
                                <p>Motivo: <?php echo escaparAdmin($solicitud["MotivoRechazo"]); ?></p>
                            <?php endif; ?>
                        </div>

                        <a href="ver_comprobante_pro.php?idSolicitud=<?php echo (int)$solicitud["idSolicitud"]; ?>"
                           target="_blank" rel="noopener noreferrer">
                            Ver comprobante
                        </a>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </details>

            </div>
        </main>

    <script src="js/menu_lateral.js"></script>
</body>
</html>
