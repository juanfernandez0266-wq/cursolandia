<?php

session_start();

require_once __DIR__ . "/clases/curso.php";


//LEEMOS LOS FILTROS DE BUSQUEDA

$busqueda = is_string($_GET["buscar"] ?? null) ? trim($_GET["buscar"]) : "";
$busqueda = mb_substr($busqueda, 0, 100);

$etiquetaRecibida = $_GET["etiqueta"] ?? "0";
$idEtiqueta = is_string($etiquetaRecibida) && ctype_digit($etiquetaRecibida)
    ? (int) $etiquetaRecibida
    : 0;


// OBTENEMOS LOS CURSOS GUARDADOS EN MYSQL

$cursosDisponibles = [];
$etiquetasDisponibles = [];
$errorCursos = "";

try {

    $etiquetasDisponibles = Curso::obtenerEtiquetasDisponibles();

    $cursosDisponibles = Curso::obtenerCursosParaInicio($busqueda, $idEtiqueta);

} catch (Throwable $e) {

    error_log($e->getMessage());
    $errorCursos = "No se pudieron cargar los cursos disponibles.";

}

?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio - Cursolandia</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/inicio.css?v=5">

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
    <div class="pagina-principal contenido-con-menu">

        <!-- TOPBAR -->
        <header class="topbar">

            <div class="topbar-contenido">

                <!-- BUSCAMOS CURSOS POR TITULO O ETIQUETAS -->

                <form class="topbar-busqueda" method="GET" action="inicio.php">

                    <!-- BUSCADOR -->

                    <div class="buscador">

                        <button type="submit" class="buscador-enviar" aria-label="Buscar cursos">
                            <i class="bi bi-search"></i>
                        </button>

                        <input type="search"
                            name="buscar"
                            maxlength="100"
                            placeholder="Buscar cursos"
                            value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">

                    </div>


                    <!-- FILTRAMOS POR ETIQUETAS -->

                    <select name="etiqueta" class="form-select filtro-cursos" onchange="this.form.requestSubmit()">

                        <option value="0">Todos los cursos</option>

                        <?php foreach ($etiquetasDisponibles as $etiqueta): ?>

                            <option value="<?php echo (int) $etiqueta["idInteres"]; ?>"
                                <?php echo $idEtiqueta === (int) $etiqueta["idInteres"] ? "selected" : ""; ?>>

                                <?php echo htmlspecialchars($etiqueta["NombreInteres"], ENT_QUOTES, "UTF-8"); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </form>

                <div class="topbar-acciones">

                    <a href="mis_cursos.php" class="btn btn-outline-primary">
                        Acceder a mi Curso
                    </a>

                    <a href="crear_curso.php" class="btn btn-primary">
                        Crear un Curso
                    </a>

                </div>

            </div>

        </header>


        <!-- MAIN CONTENT -->
        <main class="contenido">

            <div class="contenido-interior">

                <div class="encabezado-catalogo">
                    <h1>Cursos disponibles</h1>
                    <p>Explorá los cursos disponibles en Cursolandia.</p>
                </div>


                <!-- CURSOS DISPONIBLES -->

                <?php if ($errorCursos !== ""): ?>

                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errorCursos, ENT_QUOTES, "UTF-8"); ?>
                    </div>

                <?php elseif (empty($cursosDisponibles)): ?>

                    <div class="alert alert-light border">

                        <?php if ($busqueda !== "" || $idEtiqueta > 0): ?>

                            No encontramos cursos que coincidan con tu búsqueda.

                        <?php else: ?>

                            Todavía no hay cursos disponibles.

                        <?php endif; ?>

                    </div>
                <?php else: ?>

                    <div class="row g-4">

                        <?php foreach ($cursosDisponibles as $curso): ?>

                            <?php
                                $titulo = htmlspecialchars($curso["Titulo"], ENT_QUOTES, "UTF-8");
                                $descripcion = trim((string) $curso["Descripcion"]);

                                // MOSTRAR UNA DESCRIPCION BREVE

                                if (mb_strlen($descripcion) > 130) {
                                    $descripcion = mb_substr($descripcion, 0, 130) . "...";
                                }

                                $descripcion = htmlspecialchars($descripcion, ENT_QUOTES, "UTF-8");

                                $imagen = $curso["ImagenPrincipal"] ?? "";
                            ?>

                            <div class="col-lg-4 col-md-6">

                                <div class="curso-card">

                                    <!-- IMAGEN PRINCIPAL -->

                                    <div class="curso-imagen <?php echo $imagen !== "" ? "con-imagen" : ""; ?>">

                                        <?php if ($imagen !== ""): ?>

                                            <img src="<?php echo htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="Imagen del curso <?php echo $titulo; ?>">

                                        <?php else: ?>

                                            Imagen del curso

                                        <?php endif; ?>

                                    </div>


                                    <!-- INFORMACION -->

                                    <div class="curso-contenido">

                                        <h3><?php echo $titulo; ?></h3>

                                        <p><?php echo $descripcion; ?></p>

                                        <!-- EL DETALLE DEL CURSO SE IMPLEMENTARA DESPUES -->

                                        <a href="detalle_curso.php?idCurso=<?php echo (int)$curso['idCurso']; ?>" class="btn btn-outline-primary">Ver curso</a>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS MENU LATERAL -->

    <script src="js/menu_lateral.js"></script>

    <!-- JS NOTIFICACIONES RAPIDAS -->
    <script src="js/notificaciones_rapidas.js"></script>

</body>

</html>