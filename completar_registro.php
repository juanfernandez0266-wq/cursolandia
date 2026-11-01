<?php

session_start();
$_SESSION["idUsuario"] = 2;

require "clases/usuario.php";



// COMPROBAR QUE HAYA UN USUARIO REGISTRANDOSE

if (!isset($_SESSION["idUsuario"])) {

    header("Location: index.php");
    exit;
}



// RECUPERAR AL USUARIO DESDE MYSQL

$usuarioActual = Usuario::buscarPorId($_SESSION["idUsuario"]);

if ($usuarioActual == null) {

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;
}



// DATOS DEL FORMULARIO

$errores = array();

$FechaNacimiento = "";
$Intereses = array();
$Antecedentes = "";

$pasoActual = 1;

$fechaMaxima = date("Y-m-d", strtotime("-18 years"));



// FINALIZAR REGISTRO

if (isset($_POST["finalizar_registro"])) {

    $FechaNacimiento = $_POST["FechaNacimiento"];

    if (isset($_POST["Intereses"])) {
        $Intereses = $_POST["Intereses"];
    }

    $Antecedentes = $_POST["Antecedentes"];


    // VALIDAR EDAD

    if ($FechaNacimiento == "") {
        $errores["FechaNacimiento"] = "Ingresá tu fecha de nacimiento.";
    }
    else if ($FechaNacimiento > $fechaMaxima) {
        $errores["FechaNacimiento"] = "Debés tener al menos 18 años.";
    }


    // FOTO DE PERFIL

    $FotoPerfil = null;

    if (isset($_FILES["FotoPerfil"]) && $_FILES["FotoPerfil"]["error"] != UPLOAD_ERR_NO_FILE) {
        $FotoPerfil = $_FILES["FotoPerfil"];
    }


    // COMPLETAR PERFIL

    if (count($errores) == 0) {

        $resultado = $usuarioActual->completarperfil(
            $FechaNacimiento,
            $Intereses,
            $Antecedentes,
            $FotoPerfil
        );


        // SI HAY ERRORES

        if (is_array($resultado)) {
            $errores = $resultado;
        }


        // SI EL REGISTRO SE COMPLETO CORRECTAMENTE

        else {

            header("Location: inicio.php");
            exit;
        }
    }


    // VOLVER AL PASO QUE TIENE EL ERROR

    if (isset($errores["FechaNacimiento"])) {
        $pasoActual = 1;
    }
    else if (isset($errores["Intereses"])) {
        $pasoActual = 2;
    }
    else if (isset($errores["Antecedentes"])) {
        $pasoActual = 3;
    }
    else {
        $pasoActual = 4;
    }
}

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Completar registro - Cursolandia</title>


    <!-- Bootstrap CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- CSS PROPIO -->

    <link rel="stylesheet" href="css/completar_registro.css?v=2">

</head>


<body>


    <main class="registro-completo">


        <div class="container">


            <form id="completarRegistro" method="POST" action="completar_registro.php" enctype="multipart/form-data">


                <div id="pasosRegistro" class="carousel slide carousel-fade" data-bs-interval="false" data-bs-touch="false">


                    <div class="carousel-inner">



                        <!-- PASO 1 -->

                        <div class="carousel-item <?php if ($pasoActual == 1) { echo "active"; } ?>">


                            <div class="registro-contenido">


                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 1/4</p>

                                <h1>¿Cuál es tu fecha de nacimiento?</h1>

                                <p class="registro-descripcion">Ingresala dándole click al formulario</p>


                                <div class="campo-grande">

                                    <input type="date" class="form-control" id="FechaNacimiento" name="FechaNacimiento" max="<?php echo $fechaMaxima; ?>" value="<?php echo htmlspecialchars($FechaNacimiento); ?>">
                                    <p class="campo-error" id="errorFecha"></p>

                                    <!-- VALIDACION -->

                                    <?php

                                    if (isset($errores["FechaNacimiento"])) {
                                        echo '<p class="text-danger mt-2">' . $errores["FechaNacimiento"] . '</p>';
                                    }

                                    ?>

                                </div>


                                <div class="registro-navegacion justify-content-end">

                                    <button type="button" class="boton-flecha" id="siguienteFecha"> → </button>

                                </div>


                            </div>


                        </div>



                        <!-- PASO 2 -->

                        <div class="carousel-item <?php if ($pasoActual == 2) { echo "active"; } ?>">


                            <div class="registro-contenido">


                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 2/4</p>

                                <h1>¿Cuáles son tus temas de interés?</h1>

                                <p class="registro-descripcion">Elegí al menos un interés de tu preferencia</p>


                                <div class="row g-3" id="grupoIntereses">


                                    <!-- AFICIONES -->

                                    <div class="col-md-4">

                                        <input type="checkbox" class="interes-check" id="aficiones" name="Intereses[]" value="Aficiones" <?php if (in_array("Aficiones", $Intereses)) { echo "checked"; } ?>>

                                        <label class="interes-opcion" for="aficiones">Aficiones</label>

                                    </div>


                                    <!-- MUSICA -->

                                    <div class="col-md-4">

                                        <input type="checkbox" class="interes-check" id="musica" name="Intereses[]" value="Musica" <?php if (in_array("Musica", $Intereses)) { echo "checked"; } ?>>

                                        <label class="interes-opcion" for="musica">Música</label>

                                    </div>


                                    <!-- TECNOLOGIA -->

                                    <div class="col-md-4">

                                        <input type="checkbox" class="interes-check" id="tecnologia" name="Intereses[]" value="Tecnologia" <?php if (in_array("Tecnologia", $Intereses)) { echo "checked"; } ?>>

                                        <label class="interes-opcion" for="tecnologia">Tecnología</label>

                                    </div>


                                    <!-- SALUD -->

                                    <div class="col-md-6">

                                        <input type="checkbox" class="interes-check" id="salud" name="Intereses[]" value="Salud" <?php if (in_array("Salud", $Intereses)) { echo "checked"; } ?>>

                                        <label class="interes-opcion" for="salud">Salud</label>

                                    </div>


                                    <!-- DEPORTES -->

                                    <div class="col-md-6">

                                        <input type="checkbox" class="interes-check" id="deportes" name="Intereses[]" value="Deportes" <?php if (in_array("Deportes", $Intereses)) { echo "checked"; } ?>>

                                        <label class="interes-opcion" for="deportes">Deportes</label>

                                    </div>


                                    <!-- VALIDACION -->

                                    <?php

                                    if (isset($errores["Intereses"])) {
                                        echo '<p class="text-danger mt-2">' . $errores["Intereses"] . '</p>';
                                    }

                                    ?>


                                </div>


                                <div class="registro-navegacion">

                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="prev"> ← </button>

                                    <button type="button" class="boton-flecha" id="siguienteIntereses"> → </button>

                                </div>


                            </div>


                        </div>



                        <!-- PASO 3 -->

                        <div class="carousel-item <?php if ($pasoActual == 3) { echo "active"; } ?>">


                            <div class="registro-contenido">


                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 3/4</p>

                                <h1>¿Cuáles son tus antecedentes?</h1>

                                <p class="registro-descripcion">Contanos un poco sobre vos</p>


                                <textarea class="form-control antecedentes" id="Antecedentes" name="Antecedentes" rows="5" placeholder="Escribí un poco sobre tu historia, estudios, experiencia..."><?php echo htmlspecialchars($Antecedentes); ?></textarea>
                                <p class="campo-error" id="errorAntecedentes"></p>

                                <!-- VALIDACION -->

                                <?php

                                if (isset($errores["Antecedentes"])) {
                                    echo '<p class="text-danger mt-2">' . $errores["Antecedentes"] . '</p>';
                                }

                                ?>

                                <p class="intereses-error" id="errorIntereses">Elegí al menos un interés para continuar.</p>

                                <div class="registro-navegacion">

                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="prev"> ← </button>

                                    <button type="button" class="boton-flecha" id="siguienteAntecedentes"> → </button>

                                </div>


                            </div>


                        </div>



                        <!-- PASO 4 -->

                        <div class="carousel-item <?php if ($pasoActual == 4) { echo "active"; } ?>">


                            <div class="registro-contenido registro-contenido-foto">


                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 4/4</p>

                                <h1 class="titulo-foto">Agregá una foto de perfil</h1>


                                <!-- FOTO DE PERFIL -->

                                <div class="foto-perfil">

                                    <label for="FotoPerfil" class="foto-seleccion">

                                        <img src="imagenes/perfil_predeterminado.png" class="foto-preview" id="vistaFotoPerfil" alt="Foto de perfil">

                                    </label>

                                    <input type="file" id="FotoPerfil" name="FotoPerfil" class="foto-input" accept=".jpg,.jpeg,.png">

                                    <label for="FotoPerfil" class="boton-camara" title="Elegir foto">📷</label>

                                </div>



                                <!-- VALIDACION -->

                                <?php

                                if (isset($errores["FotoPerfil"])) {
                                    echo '<p class="text-danger text-center mt-2">' . $errores["FotoPerfil"] . '</p>';
                                }

                                ?>



                                <!-- ERROR BD -->

                                <?php

                                if (isset($errores["bd"])) {
                                    echo '<p class="text-danger text-center mt-2">' . $errores["bd"] . '</p>';
                                }

                                ?>



                                <!-- NAVEGACION -->

                                <div class="registro-navegacion registro-navegacion-final">

                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="prev"> ← </button>

                                    <button type="submit" name="finalizar_registro" class="btn-finalizar">Finalizar</button>

                                </div>


                            </div>


                        </div>


                    </div>


                </div>


            </form>


        </div>


    </main>



    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>


    <!-- JS PROPIO -->

    <script src="js/completar_registro.js?v=5"></script>


</body>

</html>