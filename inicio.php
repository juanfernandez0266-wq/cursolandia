<?php

session_start();

require_once __DIR__ . "/clases/curso.php";


// OBTENEMOS LOS CURSOS GUARDADOS EN MYSQL

$cursosDisponibles = [];
$errorCursos = "";

try {

    $cursosDisponibles = Curso::obtenerCursosParaInicio();

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
    <link rel="stylesheet" href="css/inicio.css?v=3">

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

                <div class="topbar-busqueda">

                    <div class="buscador">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Buscar cursos">
                    </div>

                    <select class="form-select filtro-cursos">
                        <option selected>Todos los cursos</option>
                        <option>Tecnología</option>
                        <option>Deportes</option>
                        <option>Salud</option>
                        <option>Música</option>
                        <option>Aficiones</option>
                    </select>

                </div>

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
                        Todavía no hay cursos disponibles.
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

                                        <a href="#" class="btn btn-outline-primary" onclick="return false;">
                                            Ver curso
                                        </a>

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