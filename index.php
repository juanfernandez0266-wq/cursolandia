<?php

$errores = array();

$nombre = "";
$apellido = "";
$usuario = "";
$email = "";

if (isset($_POST["crear_cuenta"])) {

    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $usuario = trim($_POST["usuario"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($nombre == "") {
        $errores["nombre"] = "Debe ingresar su nombre.";
    }

    if ($apellido == "") {
        $errores["apellido"] = "Debe ingresar su apellido.";
    }

    if ($usuario == "") {
        $errores["usuario"] = "Debe ingresar un nombre de usuario.";
    }

    if ($email == "") {
        $errores["email"] = "Debe ingresar un correo electrónico.";
    }

    if ($password == "") {
        $errores["password"] = "Debe ingresar una contraseña.";
    }

    if (count($errores) == 0) {
        $registroCorrecto = true;
    }
}

?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cursolandia</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/estilos.css">

</head>

<body>

    <!-- HEADER -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="container">

                <!-- Nombre / Logo -->
                <a class="navbar-brand" href="index.php">
                    Cursolandia
                </a>

                <!-- Botón menú para pantallas pequeñas -->
                <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal"
                    aria-expanded="false"
                    aria-label="Abrir menú">

                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Opciones del menú -->
                <div class="collapse navbar-collapse" id="menuPrincipal">

                    <ul class="navbar-nav ms-auto align-items-lg-center">

                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Ver cursos
                            </a>
                        </li>

                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-primary" href="#registro">
                                Registrarse
                            </a>
                        </li>

                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary" href="iniciar_sesion.php">
                                Entrar
                            </a>
                        </li>

                    </ul>

                </div>

            </div>
        </nav>
    </header>


    <main>

        <!-- SECCIÓN 1 -->
        <section class="seccion-principal text-white py-5 mt-5">

            <div class="container">

                <div class="row align-items-center">

                    <!-- Parte izquierda -->
                    <div class="col-lg-8">
                        <h1>Aprendé y compartí lo que sabés</h1>

                        <p>
                            Cursolandia es un espacio para explorar cursos, anotarte, crear los tuyos y resolver dudas con otras personas.
                        </p>
                    </div>


                    <!-- Parte derecha - Formulario para registro del usuario nuevo -->
                    <div class="col-lg-4">

                        <form id="registro" method="POST" action="index.php" class="bg-white text-dark p-4 rounded shadow">

                            <h2 class="text-center mb-4">Crear cuenta</h2>

                            <!-- Nombre y apellido -->
                            <div class="row mb-3">

                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa tu nombre">
                                    
                                    <!-- Validación del nombre -->
                                    <?php
                                        if (isset($errores["nombre"])) {
                                                echo '<p class="text-danger mt-1">'.$errores["nombre"].'</p>';
                                        }
                                    ?>

                                </div>

                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingresa tu apellido">
                                    
                                    <!-- Validación del apellido -->
                                    <?php
                                        if (isset($errores["apellido"])) {
                                            echo '<p class="text-danger mt-1">'.$errores["apellido"].'</p>';
                                        }
                                    ?>
                                </div>

                            </div>

                            <!-- Nombre de usuario -->
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Nombre de usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa un nombre de usuario">
                                
                                <!-- Validación del Nombre de usuario -->
                                <?php
                                    if (isset($errores["usuario"])) {
                                        echo '<p class="text-danger mt-1">'.$errores["usuario"].'</p>';
                                    }
                                ?>
                            </div>

                            <!-- Correo electrónico -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Ingresa tu correo electrónico">
                                
                                <!-- Validación del correo electrónico -->
                                 <?php
                                    if (isset($errores["email"])) {
                                        echo '<p class="text-danger mt-1">'.$errores["email"].'</p>';
                                    }
                                ?>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-4">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa una contraseña">
                                <!-- Validación de la contraseña -->
                                <?php
                                    if (isset($errores["password"])) {
                                        echo '<p class="text-danger mt-1">'.$errores["password"].'</p>';
                                    }
                                ?>
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" name="crear_cuenta" class="btn btn-primary">Crear cuenta</button>
                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </section>


        <!-- SECCIÓN 2 -->
        <section class="bg-light py-5">
            <div class="container">
                <h2>Sección 2 - Información</h2>
            </div>
        </section>


        <!-- SECCIÓN 3 -->
        <section class="py-5">
            <div class="container">
                <h2>Sección 3 - Información</h2>
            </div>
        </section>


        <!-- SECCIÓN 4 -->
        <section class="bg-light py-5">
            <div class="container">
                <h2>Sección 4 - Preguntas frecuentes</h2>
            </div>
        </section>

    </main>


    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <h2>Footer</h2>
        </div>
    </footer>


    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>