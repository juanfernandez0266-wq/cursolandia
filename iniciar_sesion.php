<?php

$errores = array();

$email = "";

if (isset($_POST["iniciar_sesion"])) {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email == "") {
        $errores["email"] = "Debe ingresar su correo electrónico.";
    }

    if ($password == "") {
        $errores["password"] = "Debe ingresar su contraseña.";
    }

    if (count($errores) == 0) {
        $datosCorrectos = true;
    }
}

?>










<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - Cursolandia</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/iniciar_sesion.css">
</head>

<body>

    <main class="login">

        <div class="container-fluid">
            <div class="row min-vh-100">

                <!-- PARTE IZQUIERDA -->
                <div class="col-lg-6 d-flex align-items-center justify-content-center">

                    <div class="login-formulario">

                        <h1>Iniciar sesión</h1>

                        <p>Ingresá a tu cuenta de Cursolandia</p>

                        <form id="inicioSesion" method="POST" action="iniciar_sesion.php">

                            <!-- Correo electrónico -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Ingresá tu correo electrónico"> value="<?php echo $email; ?>">

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
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresá tu contraseña">
                                <!-- Validación de la contraseña -->
                                 <?php
                                    if (isset($errores["password"])) {
                                        echo '<p class="text-danger mt-1">'.$errores["password"].'</p>';
                                    }
                                ?>
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" name="iniciar_sesion" class="btn btn-dark">Entrar</button>
                            </div>

                        </form>

                    </div>

                </div>


                <!-- PARTE DERECHA -->
                <div class="col-lg-6 imagen-login">
                </div>

            </div>
        </div>

    </main>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>