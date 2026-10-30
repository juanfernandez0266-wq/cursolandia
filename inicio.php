<?php

session_start();

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
    <link rel="stylesheet" href="css/inicio.css?v=2">

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


                <!-- COURSE GRID -->
                <div class="row g-4">

                    <!-- CURSO 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 1</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 2</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 3</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 4 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 4</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 5 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 5</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 6 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 6</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 7 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 7</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 8 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 8</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>


                    <!-- CURSO 9 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="curso-card">

                            <div class="curso-imagen">
                                Imagen del curso
                            </div>

                            <div class="curso-contenido">
                                <h3>Curso 9</h3>
                                <p>Información breve del curso.</p>
                                <a href="#" class="btn btn-outline-primary">Ver curso</a>
                            </div>

                        </div>
                    </div>

                </div>

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