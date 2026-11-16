
<?php

session_start();

require_once __DIR__ . "/clases/curso.php";

// IDENTIFICAMOS EL CURSO

$idCurso = filter_input(INPUT_GET, "idCurso", FILTER_VALIDATE_INT);
$idCurso = $idCurso && $idCurso > 0 ? $idCurso : 0;

// COMPROBAMOS LA SESION

if (!isset($_SESSION["idUsuario"])) {
    $_SESSION["volverDespuesLogin"] = "detalle_curso.php?idCurso=" . $idCurso;
    header("Location: iniciar_sesion.php");
    exit;
}

$idUsuario = (int) $_SESSION["idUsuario"];
$paginaActual = basename($_SERVER["PHP_SELF"]);

if (!isset($_SESSION["token_inscripcion"])) {
    $_SESSION["token_inscripcion"] = bin2hex(random_bytes(32));
}

function escaparDetalle($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$error = "";
$curso = null;
$estadoInscripcion = null;

$hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");

// REGISTRAMOS LAS INSCRIPCIONES

if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    (isset($_POST["inscribirme_gratis"]) || isset($_POST["solicitar_inscripcion_pago"]))) {

    $token = $_POST["token_inscripcion"] ?? "";

    try {

        if ($idCurso <= 0 || !is_string($token) || !hash_equals($_SESSION["token_inscripcion"], $token)) {
            throw new DomainException("No se pudo verificar la inscripción. Recargá la página.");
        }

        if (isset($_POST["inscribirme_gratis"])) {

            Curso::inscribirEnCursoGratuito($idUsuario, $idCurso);
            $_SESSION["inscripcion_confirmada"] = $idCurso;

        } else {

            Curso::solicitarInscripcionCursoPago($idUsuario, $idCurso);
            $_SESSION["inscripcion_pendiente"] = $idCurso;

        }

        $_SESSION["token_inscripcion"] = bin2hex(random_bytes(32));

        header("Location: detalle_curso.php?idCurso=" . $idCurso);
        exit;

    } catch (DomainException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = "No se pudo registrar la inscripción.";
    }
}

// OBTENEMOS LOS DATOS DEL CURSO

try {
    if ($idCurso > 0) {
        $curso = Curso::obtenerDetalleCurso($idCurso);

        if ($curso && (int)$curso["idUsuario"] !== $idUsuario) {
            $estadoInscripcion = Curso::obtenerEstadoInscripcion($idUsuario, $idCurso);
        }
    }

    if (!$curso) http_response_code(404);

} catch (Throwable $e) {
    error_log($e->getMessage());
    $error = "No se pudo cargar la información del curso.";
}

$confirmacion = $curso && (int)($_SESSION["inscripcion_confirmada"] ?? 0) === $idCurso;

if ($confirmacion) unset($_SESSION["inscripcion_confirmada"]);
// COMPROBAMOS SI SE REGISTRO UNA SOLICITUD PENDIENTE

$solicitudPendiente = $curso && (int)($_SESSION["inscripcion_pendiente"] ?? 0) === $idCurso;

if ($solicitudPendiente) unset($_SESSION["inscripcion_pendiente"]);

$esCreador = $curso && (int)$curso["idUsuario"] === $idUsuario;
$estaActivo = $curso && substr($curso["FechaInicio"], 0, 10) <= $hoy && substr($curso["FechaFin"], 0, 10) >= $hoy;

$imagenPrincipal = $curso["Imagenes"][0]["Ruta"] ?? "";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del curso - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/menu_lateral.css">
    <link rel="stylesheet" href="css/notificaciones_rapidas.css">
    <link rel="stylesheet" href="css/detalle_curso.css?v=1">
</head>
<body>

    <?php include __DIR__ . "/componentes/menu_lateral.php"; ?>
    <?php include __DIR__ . "/componentes/notificaciones_rapidas.php"; ?>

    <main class="contenido-con-menu detalle-curso-pagina">
        <div class="detalle-curso-contenido">
            <div class="detalle-curso-panel">

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger"><?php echo escaparDetalle($error); ?></div>
                <?php endif; ?>

                <?php if (!$curso): ?>

                    <h1>Curso no disponible</h1>
                    <p>No encontramos el curso solicitado.</p>
                    <a href="inicio.php" class="btn btn-outline-primary">Volver a Inicio</a>

                <?php else: ?>

                    <!-- TITULO DEL CURSO -->

                    <div class="detalle-curso-encabezado">
                        <h1><?php echo escaparDetalle($curso["Titulo"]); ?></h1>
                        <p>Conocé la información del curso antes de inscribirte.</p>
                    </div>

                    <!-- GALERIA DE IMAGENES -->

                    <div class="detalle-curso-portada">
                        <?php if ($imagenPrincipal !== ""): ?>
                            <img id="imagenDetallePrincipal" src="<?php echo escaparDetalle($imagenPrincipal); ?>" alt="Imagen del curso">
                        <?php else: ?>
                            <span>Imagen del curso</span>
                        <?php endif; ?>
                    </div>

                    <?php if (count($curso["Imagenes"]) > 1): ?>
                        <div class="detalle-curso-miniaturas">
                            <?php foreach ($curso["Imagenes"] as $indice => $imagen): ?>
                                <button type="button" class="detalle-miniatura <?php echo $indice === 0 ? 'activa' : ''; ?>" data-imagen="<?php echo escaparDetalle($imagen["Ruta"]); ?>" aria-label="Ver imagen <?php echo $indice + 1; ?>" aria-pressed="<?php echo $indice === 0 ? 'true' : 'false'; ?>">
                                    <img src="<?php echo escaparDetalle($imagen["Ruta"]); ?>" alt="">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- INFORMACION E INSCRIPCION DEL CURSO -->

                    <div class="detalle-curso-resumen">
                    <!-- DATOS DEL CURSO -->

                    <div class="detalle-curso-datos">

                        <div><img src="imagenes/detalle_curso/<?php echo $curso["TipoAcceso"] === "privado" ? "candado_cerrado.svg" : "candado_abierto.svg"; ?>" class="detalle-curso-icono" alt=""><span>Acceso<strong><?php echo $curso["TipoAcceso"] === "publico" ? "Público" : "Privado"; ?></strong></span></div>
                        <div><img src="imagenes/detalle_curso/miembros-cupo.svg" class="detalle-curso-icono" alt=""><span>Cupo<strong><?php echo $curso["Cupo"] === null ? "Sin límite" : (int)$curso["Cupo"] . " alumnos"; ?></strong></span></div>
                        <div><img src="imagenes/detalle_curso/icono_costo.svg" class="detalle-curso-icono" alt=""><span>Costo<strong><?php echo $curso["TipoCurso"] === "gratuito" ? "Gratuito" : "$" . number_format((float)$curso["Costo"], 2, ",", "."); ?></strong></span></div>
                        <div><img src="imagenes/detalle_curso/calendario-inicio.svg" class="detalle-curso-icono" alt=""<span>Inicio<strong><?php echo date("d/m/Y", strtotime($curso["FechaInicio"])); ?></strong></span></div>
                        <div><img src="imagenes/detalle_curso/calendario-finalizacion.svg" class="detalle-curso-icono" alt=""<span>Finalización<strong><?php echo date("d/m/Y", strtotime($curso["FechaFin"])); ?></strong></span></div>

                    </div>


                    <!-- INSCRIPCION -->

                    <div class="detalle-curso-inscripcion">

                        <?php if ($esCreador): ?>

                        <!-- INFORMAMOS QUE EL USUARIO ES EL CREADOR -->

                        <div class="detalle-curso-aviso aviso-propio">
                            <i class="bi bi-info-circle"></i>
                            <span>Este curso fue creado por vos. Podés administrar su contenido y sus inscripciones.</span>
                        </div>

                        <button type="button" class="btn btn-primary mt-3" disabled>Administrar curso</button>

                        <?php elseif ($estadoInscripcion === "confirmada"): ?>
                            <p>Ya estás inscrito en este curso.</p>
                            <a href="mis_cursos.php" class="btn btn-primary">Ver mis cursos</a>

                        <?php elseif ($estadoInscripcion === "pendiente"): ?>
                            <p>Tu inscripción está pendiente de confirmación.</p>

                        <?php elseif ($estadoInscripcion === "rechazada"): ?>
                            <p>Tu solicitud anterior fue rechazada.</p>

                        <?php elseif (!$estaActivo): ?>

                        <div class="detalle-curso-aviso">
                            <i class="bi bi-exclamation-circle"></i>

                            <?php if ($hoy < substr($curso["FechaInicio"], 0, 10)): ?>
                                <span>Las inscripciones estarán disponibles a partir del <?php echo date("d/m/Y", strtotime($curso["FechaInicio"])); ?>.</span>
                            <?php else: ?>
                                <span>El período de inscripción de este curso ya finalizó.</span>
                            <?php endif; ?>
                        </div>

                        <?php elseif ($curso["TipoAcceso"] !== "publico"): ?>
                            <p>La inscripción a cursos privados todavía no está habilitada.</p>

                        <?php elseif ($curso["TipoCurso"] === "pago"): ?>

                        <form method="POST" action="detalle_curso.php?idCurso=<?php echo $idCurso; ?>">
                            <input type="hidden" name="token_inscripcion" value="<?php echo escaparDetalle($_SESSION["token_inscripcion"]); ?>">
                            <button type="submit" name="solicitar_inscripcion_pago" value="1" class="btn btn-primary">Solicitar inscripción</button>
                        </form>

                        <p class="mt-2">Tu solicitud quedará pendiente hasta que el creador confirme tu inscripción.</p>

                        <?php else: ?>
                            <form method="POST" action="detalle_curso.php?idCurso=<?php echo $idCurso; ?>">
                                <input type="hidden" name="token_inscripcion" value="<?php echo escaparDetalle($_SESSION["token_inscripcion"]); ?>">
                                <button type="submit" name="inscribirme_gratis" value="1" class="btn btn-primary">Inscribirme al curso</button>
                            </form>
                        <?php endif; ?>

                    </div>
                    <!-- DESCRIPCION -->

                    <section class="detalle-curso-descripcion">
                        <h2>Descripción del curso</h2>
                        <p><?php echo nl2br(escaparDetalle($curso["Descripcion"])); ?></p>
                    </section>

                    

                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- CONFIRMACION DE INSCRIPCION -->

    <?php if ($confirmacion): ?>
        <div class="modal fade" id="modalInscripcion" tabindex="-1" aria-labelledby="tituloModalInscripcion" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content detalle-modal">
                    <div class="modal-body">
                        <i class="bi bi-check-circle-fill detalle-modal-icono"></i>
                        <h2 id="tituloModalInscripcion">¡Inscripción confirmada!</h2>
                        <p>Te inscribiste correctamente al curso. Ya podés encontrarlo en <strong>Mis cursos</strong>, dentro de la sección <strong>Inscritos</strong>.</p>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CONFIRMACION DE SOLICITUD PENDIENTE -->

    <?php if ($solicitudPendiente): ?>
        <div class="modal fade" id="modalInscripcionPendiente" tabindex="-1" aria-labelledby="tituloModalPendiente" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content detalle-modal">
                    <div class="modal-body">
                        <i class="bi bi-clock-history detalle-modal-icono pendiente"></i>
                        <h2 id="tituloModalPendiente">Solicitud pendiente</h2>
                        <p>Registramos tu solicitud correctamente. El creador del curso deberá confirmarla. Podés consultar su estado en <strong>Mis cursos → Pendientes</strong>.</p>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/menu_lateral.js"></script>
    <script src="js/notificaciones_rapidas.js"></script>
    <script src="js/detalle_curso.js?v=2"></script>
</body>
</html>
