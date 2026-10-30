<?php

session_start();

require "clases/usuario.php";



// DATOS DEL FORMULARIO

$errores = array();
$email = "";



// INICIAR SESION

if (isset($_POST["iniciar_sesion"])) {

    $email = $_POST["email"];
    $Contraseña = $_POST["Contraseña"];


    // VALIDAR CREDENCIALES

    $resultado = Usuario::validarCredenciales(
        $email,
        $Contraseña
    );


    // SI HAY ERRORES

    if (is_array($resultado)) {

        $errores = $resultado;

    }


    // CREDENCIALES CORRECTAS

    else {

        $usuarioLogueado = $resultado;

        $_SESSION["idUsuario"] = $usuarioLogueado->verId();


        // VOLVER A LA PAGINA QUE SOLICITO INICIAR SESION

        if (isset($_SESSION["volverDespuesLogin"])) {

            $paginaDestino = $_SESSION["volverDespuesLogin"];

            unset($_SESSION["volverDespuesLogin"]);

            header("Location: " . $paginaDestino);
            exit;
        }


        // IR A INICIO

        else {

            header("Location: inicio.php");
            exit;
        }
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


    <!-- CSS PROPIO -->

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



                            <!-- CORREO ELECTRONICO -->

                            <div class="mb-3">


                                <label for="email" class="form-label">Correo electrónico</label>

                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Ingresá tu correo electrónico">


                                <!-- VALIDACION DEL CORREO ELECTRONICO -->

                                <?php

                                if (isset($errores["email"])) {
                                    echo '<p class="text-danger mt-1">' . $errores["email"] . '</p>';
                                }

                                ?>


                            </div>



                            <!-- CONTRASEÑA -->

                            <div class="mb-4">


                                <label for="Contraseña" class="form-label">Contraseña</label>

                                <input type="password" class="form-control" id="Contraseña" name="Contraseña" placeholder="Ingresá tu contraseña">


                                <!-- VALIDACION DE LA CONTRASEÑA -->

                                <?php

                                if (isset($errores["Contraseña"])) {
                                    echo '<p class="text-danger mt-1">' . $errores["Contraseña"] . '</p>';
                                }

                                ?>


                                <!-- CREDENCIALES INCORRECTAS -->

                                <?php

                                if (isset($errores["credenciales"])) {
                                    echo '<p class="text-danger mt-2">' . $errores["credenciales"] . '</p>';
                                }

                                ?>


                            </div>



                            <!-- BOTON -->

                            <div class="d-grid">

                                <button type="submit" name="iniciar_sesion" class="btn btn-dark">Entrar</button>

                            </div>


                        </form>


                    </div>


                </div>



                <!-- PARTE DERECHA -->

                <div class="col-lg-6 imagen-login"></div>


            </div>


        </div>


    </main>



    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>