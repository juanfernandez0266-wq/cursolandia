
<?php

session_start();

require_once __DIR__ . "/clases/curso.php";

// COMPROBAMOS LA SESION

if (!isset($_SESSION["idUsuario"])) {
    $_SESSION["volverDespuesLogin"] = "mis_cursos.php";
    header("Location: iniciar_sesion.php");
    exit;
}

$paginaActual = basename($_SERVER["PHP_SELF"]);

// OBTENEMOS LOS CURSOS CREADOS POR EL USUARIO

$idUsuario = (int) $_SESSION["idUsuario"];

$cursosCreados = [];
$errorCursosCreados = "";

try {

    $cursosCreados = Curso::obtenerCursosCreadosPorUsuario($idUsuario);

} catch (Throwable $e) {

    error_log($e->getMessage());
    $errorCursosCreados = "No se pudieron cargar tus cursos.";

}

// OBTENEMOS LAS INSCRIPCIONES DEL USUARIO

$cursosInscritos = [];
$cursosPendientes = [];
$errorInscripciones = "";
$tienePlanPro = false;

try {

    $cursosInscritos = Curso::obtenerCursosPorEstadoInscripcion($idUsuario, "confirmada");
    $cursosPendientes = Curso::obtenerCursosPorEstadoInscripcion($idUsuario, "pendiente");
    $tienePlanPro = Curso::usuarioTienePlanProVigente($idUsuario);

} catch (Throwable $e) {

    error_log($e->getMessage());
    $errorInscripciones = "No se pudieron cargar las inscripciones.";

}


// CONTAMOS LOS CURSOS INSCRITOS QUE ESTAN ACTIVOS

$hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");
$cantidadInscritosActivos = 0;

foreach ($cursosInscritos as $curso) {

    if ($curso["FechaInicio"] <= $hoy && $curso["FechaFin"] >= $hoy) {
        $cantidadInscritosActivos++;
    }

}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis cursos - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/menu_lateral.css?v=5">
    <link rel="stylesheet" href="css/notificaciones_rapidas.css">
    <link rel="stylesheet" href="css/mis_cursos.css?v=2">
</head>

<body>

    <!-- MENU LATERAL -->

    <?php include __DIR__ . "/componentes/menu_lateral.php"; ?>

    <!-- NOTIFICACIONES RAPIDAS -->

    <?php include __DIR__ . "/componentes/notificaciones_rapidas.php"; ?>

    <!-- CONTENIDO PRINCIPAL -->

    <main class="contenido-con-menu mis-cursos-pagina">

        <div class="mis-cursos-contenido">

            <div class="mis-cursos-panel">

                <!-- ENCABEZADO -->

                <header class="mis-cursos-encabezado">
                    <h1>Mis cursos</h1>
                    <p>Administrá los cursos que creaste y consultá tus inscripciones.</p>
                </header>

                <!-- PESTAÑAS -->

                <div class="mis-cursos-pestanas" role="tablist" aria-label="Tipos de cursos">

                    <button type="button" class="mis-cursos-pestana activa"  id="pestana-creados" data-pestana="creados" role="tab"   aria-selected="true" aria-controls="seccion-creados"> Mis cursos  </button>

                    <button type="button" class="mis-cursos-pestana" id="pestana-inscritos"  data-pestana="inscritos"  role="tab"  aria-selected="false" aria-controls="seccion-inscritos">

                        Inscritos
                    </button>

                    <button type="button"  class="mis-cursos-pestana"  id="pestana-pendientes" data-pestana="pendientes" role="tab" aria-selected="false"  aria-controls="seccion-pendientes">
                         Pendientes
                    </button>

                </div>


                <!-- CURSOS CREADOS -->

                <section class="mis-cursos-seccion" id="seccion-creados"  role="tabpanel"  aria-labelledby="pestana-creados">

                    <div class="mis-cursos-seccion-titulo">
                        <h2>Cursos creados</h2>
                        <p>Los cursos que publicaste en Cursolandia.</p>
                    </div>

                   <!-- MOSTRAMOS LOS CURSOS CREADOS -->

                    <?php if ($errorCursosCreados !== ""): ?>

                        <div class="alert alert-danger"><?php echo htmlspecialchars($errorCursosCreados, ENT_QUOTES, "UTF-8"); ?></div>

                    <?php elseif (empty($cursosCreados)): ?>

                        <div class="mis-cursos-vacio">
                            <i class="bi bi-journal-plus"></i>
                            <p>Todavía no creaste ningún curso.</p>
                            <a href="crear_curso.php" class="btn btn-primary">Crear un curso</a>
                        </div>

                    <?php else: ?>

                        <?php foreach ($cursosCreados as $curso): ?>

                            <article class="mis-cursos-fila">

                                <!-- IMAGEN DEL CURSO -->

                                <div class="mis-cursos-imagen">

                                    <?php if (!empty($curso["ImagenPrincipal"])): ?>

                                        <img src="<?php echo htmlspecialchars($curso["ImagenPrincipal"], ENT_QUOTES, "UTF-8"); ?>" alt="Imagen del curso">

                                    <?php else: ?>

                                        <i class="bi bi-image"></i>

                                    <?php endif; ?>

                                </div>

                                <!-- INFORMACION DEL CURSO -->

                                <div class="mis-cursos-informacion">

                                    <h3><?php echo htmlspecialchars($curso["Titulo"], ENT_QUOTES, "UTF-8"); ?></h3>

                                    <p>Curso creado por vos</p>
                                    <!-- MOSTRAMOS LAS FECHAS DEL CURSO -->

                                    <p class="mis-cursos-fechas">
                                        <i class="bi bi-calendar3"></i>
                                        Inicio: <?php echo date("d/m/Y", strtotime($curso["FechaInicio"])); ?>
                                        · Fin: <?php echo date("d/m/Y", strtotime($curso["FechaFin"])); ?>
                                    </p>

                                    <span class="mis-cursos-estado">
                                        <?php echo $curso["TipoAcceso"] === "publico" ? "Público" : "Privado"; ?>
                                        ·
                                        <?php echo $curso["TipoCurso"] === "gratuito" ? "Gratuito" : "Pago"; ?>
                                    </span>

                                </div>

                                <!-- ADMINISTRACION DEL CURSO -->

                                <a href="curso.php?idCurso=<?php echo (int)$curso["idCurso"]; ?>" class="mis-cursos-accion">Administrar</a>

                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </section>


                <!-- CURSOS INSCRITOS -->

                <section class="mis-cursos-seccion" id="seccion-inscritos" role="tabpanel" aria-labelledby="pestana-inscritos" hidden>

                    <div class="mis-cursos-seccion-titulo">
                        <h2>Cursos inscritos</h2>
                        <p>Los cursos en los que participás como alumno.</p>
                    </div>
                    <!-- MOSTRAMOS EL LIMITE DE INSCRIPCIONES -->

                    <?php if ($errorInscripciones === ""): ?>

                        <div class="mis-cursos-limite">
                            <i class="bi bi-info-circle"></i>

                            <?php if ($tienePlanPro): ?>
                                <span>Plan Pro: inscripciones sin límite.</span>
                            <?php else: ?>
                                <span>Plan gratuito: <strong><?php echo $cantidadInscritosActivos; ?> de 3 cursos activos</strong></span>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>


                    <!-- MOSTRAMOS LOS CURSOS INSCRITOS -->

                    <?php if ($errorInscripciones !== ""): ?>

                        <div class="alert alert-danger"><?php echo htmlspecialchars($errorInscripciones, ENT_QUOTES, "UTF-8"); ?></div>

                    <?php elseif (empty($cursosInscritos)): ?>

                        <div class="mis-cursos-vacio">
                            <i class="bi bi-journal-bookmark"></i>
                            <p>Todavía no estás inscrito en ningún curso.</p>
                        </div>

                    <?php else: ?>

                        <?php foreach ($cursosInscritos as $curso): ?>

                            <article class="mis-cursos-fila">

                                <div class="mis-cursos-imagen">
                                    <?php if (!empty($curso["ImagenPrincipal"])): ?>
                                        <img src="<?php echo htmlspecialchars($curso["ImagenPrincipal"], ENT_QUOTES, "UTF-8"); ?>" alt="Imagen del curso">
                                    <?php else: ?>
                                        <i class="bi bi-image"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="mis-cursos-informacion">
                                    <h3><?php echo htmlspecialchars($curso["Titulo"], ENT_QUOTES, "UTF-8"); ?></h3>
                                    <p>Inscripción confirmada</p>
                                    <p class="mis-cursos-fechas"><i class="bi bi-calendar3"></i> Inicio: <?php echo date("d/m/Y", strtotime($curso["FechaInicio"])); ?> · Fin: <?php echo date("d/m/Y", strtotime($curso["FechaFin"])); ?></p>
                                    <span class="mis-cursos-estado">Acceso habilitado</span>
                                </div>

                                <a href="curso.php?idCurso=<?php echo (int)$curso["idCurso"]; ?>" class="mis-cursos-accion">Entrar al curso</a>

                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </section>


                <!-- INSCRIPCIONES PENDIENTES -->

                <section class="mis-cursos-seccion"  id="seccion-pendientes" role="tabpanel"  aria-labelledby="pestana-pendientes" hidden>

                    <div class="mis-cursos-seccion-titulo">
                        <h2>Inscripciones pendientes</h2>
                        <p>Los cursos en los que todavía esperás una confirmación.</p>
                    </div>

                    <!-- MOSTRAMOS LAS INSCRIPCIONES PENDIENTES -->

                    <?php if ($errorInscripciones !== ""): ?>

                        <div class="alert alert-danger"><?php echo htmlspecialchars($errorInscripciones, ENT_QUOTES, "UTF-8"); ?></div>

                    <?php elseif (empty($cursosPendientes)): ?>

                        <div class="mis-cursos-vacio">
                            <i class="bi bi-clock-history"></i>
                            <p>No tenés inscripciones pendientes.</p>
                        </div>

                    <?php else: ?>

                        <?php foreach ($cursosPendientes as $curso): ?>

                            <article class="mis-cursos-fila">

                                <div class="mis-cursos-imagen">
                                    <?php if (!empty($curso["ImagenPrincipal"])): ?>
                                        <img src="<?php echo htmlspecialchars($curso["ImagenPrincipal"], ENT_QUOTES, "UTF-8"); ?>" alt="Imagen del curso">
                                    <?php else: ?>
                                        <i class="bi bi-image"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="mis-cursos-informacion">
                                    <h3><?php echo htmlspecialchars($curso["Titulo"], ENT_QUOTES, "UTF-8"); ?></h3>
                                    <p>Curso pago</p>
                                    <p class="mis-cursos-fechas"><i class="bi bi-calendar3"></i> Inicio: <?php echo date("d/m/Y", strtotime($curso["FechaInicio"])); ?> · Fin: <?php echo date("d/m/Y", strtotime($curso["FechaFin"])); ?></p>
                                    <span class="mis-cursos-estado pendiente">Esperando confirmación</span>
                                </div>

                                <span class="mis-cursos-accion secundaria">Ver estado</span>

                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </section>

            </div>

        </div>

    </main>

    <script src="js/menu_lateral.js"></script>
    <script src="js/notificaciones_rapidas.js"></script>
    <script src="js/mis_cursos.js?v=1"></script>

</body>
</html>
