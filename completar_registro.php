<?php

session_start();

require "clases/usuario.php";


// COMPROBAR QUE HAYA UN USUARIO REGISTRANDOSE

if (!isset($_SESSION["idUsuario"])) {

    header("Location: index.php");
    exit;
}


// RECUPERAR AL USUARIO DESDE MYSQL

$usuarioActual =
    Usuario::buscarPorId($_SESSION["idUsuario"]);


if ($usuarioActual == null) {

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;
}


$errores = array();

$FechaNacimiento = "";
$Intereses = array();
$Antecedentes = "";


// FINALIZAR REGISTRO

if (isset($_POST["finalizar_registro"])) {

    $FechaNacimiento = $_POST["FechaNacimiento"];

    if (isset($_POST["Intereses"])) {
        $Intereses = $_POST["Intereses"];
    }

    $Antecedentes = $_POST["Antecedentes"];


    $FotoPerfil = null;

    if (isset($_FILES["FotoPerfil"])) {
        $FotoPerfil = $_FILES["FotoPerfil"];
    }


    $resultado = $usuarioActual->completarperfil(
        $FechaNacimiento,
        $Intereses,
        $Antecedentes,
        $FotoPerfil
    );


    if (is_array($resultado)) {

        $errores = $resultado;

    }
    else {

        header("Location: inicio.php");
        exit;
    }
}

?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Completar registro - Cursolandia
    </title>


    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- CSS propio -->
    <link
        rel="stylesheet"
        href="css/completar_registro.css">

</head>


<body>


    <main class="registro-completo">

        <div class="container">


            <form
                id="completarRegistro"
                method="POST"
                action="completar_registro.php"
                enctype="multipart/form-data">


                <div
                    id="pasosRegistro"
                    class="carousel slide"
                    data-bs-interval="false">


                    <div class="carousel-inner">


                        <!-- PASO 1 -->

                        <div class="carousel-item active">

                            <div class="registro-contenido">


                                <p class="registro-paso">
                                    ENTRANDO A CURSOLANDIA 1/4
                                </p>


                                <h1>
                                    ¿Cuál es tu fecha de nacimiento?
                                </h1>


                                <p class="registro-descripcion">
                                    Ingresala dándole click al formulario
                                </p>


                                <div class="campo-grande">

                                    <input
                                        type="date"
                                        class="form-control"
                                        id="FechaNacimiento"
                                        name="FechaNacimiento"
                                        value="<?php echo $FechaNacimiento; ?>">


                                    <!-- Validación -->

                                    <?php

                                    if (isset($errores["FechaNacimiento"])) {

                                        echo '<p class="text-danger mt-2">'
                                            . $errores["FechaNacimiento"] .
                                            '</p>';
                                    }

                                    ?>

                                </div>


                                <div class="registro-navegacion justify-content-end">

                                    <button
                                        type="button"
                                        class="boton-flecha"
                                        data-bs-target="#pasosRegistro"
                                        data-bs-slide="next">

                                        →

                                    </button>

                                </div>


                            </div>

                        </div>



                        <!-- PASO 2 -->

                        <div class="carousel-item">

                            <div class="registro-contenido">


                                <p class="registro-paso">
                                    ENTRANDO A CURSOLANDIA 2/4
                                </p>


                                <h1>
                                    ¿Cuáles son tus temas de interés?
                                </h1>


                                <p class="registro-descripcion">
                                    Elegí al menos un interés de tu preferencia
                                </p>


                                <div class="row g-3">


                                    <div class="col-md-4">

                                        <input
                                            type="checkbox"
                                            class="interes-check"
                                            id="aficiones"
                                            name="Intereses[]"
                                            value="Aficiones"
                                            <?php
                                            if (in_array("Aficiones", $Intereses)) {
                                                echo "checked";
                                            }
                                            ?>>

                                        <label
                                            class="interes-opcion"
                                            for="aficiones">

                                            Aficiones

                                        </label>

                                    </div>



                                    <div class="col-md-4">

                                        <input
                                            type="checkbox"
                                            class="interes-check"
                                            id="musica"
                                            name="Intereses[]"
                                            value="Musica"
                                            <?php
                                            if (in_array("Musica", $Intereses)) {
                                                echo "checked";
                                            }
                                            ?>>

                                        <label
                                            class="interes-opcion"
                                            for="musica">

                                            Música

                                        </label>

                                    </div>



                                    <div class="col-md-4">

                                        <input
                                            type="checkbox"
                                            class="interes-check"
                                            id="tecnologia"
                                            name="Intereses[]"
                                            value="Tecnologia"
                                            <?php
                                            if (in_array("Tecnologia", $Intereses)) {
                                                echo "checked";
                                            }
                                            ?>>

                                        <label
                                            class="interes-opcion"
                                            for="tecnologia">

                                            Tecnología

                                        </label>

                                    </div>



                                    <div class="col-md-6">

                                        <input
                                            type="checkbox"
                                            class="interes-check"
                                            id="salud"
                                            name="Intereses[]"
                                            value="Salud"
                                            <?php
                                            if (in_array("Salud", $Intereses)) {
                                                echo "checked";
                                            }
                                            ?>>

                                        <label
                                            class="interes-opcion"
                                            for="salud">

                                            Salud

                                        </label>

                                    </div>



                                    <div class="col-md-6">

                                        <input
                                            type="checkbox"
                                            class="interes-check"
                                            id="deportes"
                                            name="Intereses[]"
                                            value="Deportes"
                                            <?php
                                            if (in_array("Deportes", $Intereses)) {
                                                echo "checked";
                                            }
                                            ?>>

                                        <label
                                            class="interes-opcion"
                                            for="deportes">

                                            Deportes

                                        </label>

                                    </div>


                                    <!-- Validación -->

                                    <?php

                                    if (isset($errores["Intereses"])) {

                                        echo '<p class="text-danger mt-2">'
                                            . $errores["Intereses"] .
                                            '</p>';
                                    }

                                    ?>


                                </div>


                                <div class="registro-navegacion">

                                    <button
                                        type="button"
                                        class="boton-flecha"
                                        data-bs-target="#pasosRegistro"
                                        data-bs-slide="prev">

                                        ←

                                    </button>


                                    <button
                                        type="button"
                                        class="boton-flecha"
                                        data-bs-target="#pasosRegistro"
                                        data-bs-slide="next">

                                        →

                                    </button>

                                </div>


                            </div>

                        </div>



                        <!-- PASO 3 -->

                        <div class="carousel-item">

                            <div class="registro-contenido">


                                <p class="registro-paso">
                                    ENTRANDO A CURSOLANDIA 3/4
                                </p>


                                <h1>
                                    ¿Cuáles son tus antecedentes?
                                </h1>


                                <p class="registro-descripcion">
                                    Contanos un poco sobre vos
                                </p>


                                <textarea
                                    class="form-control antecedentes"
                                    id="Antecedentes"
                                    name="Antecedentes"
                                    rows="5"
                                    placeholder="Escribí un poco sobre tu historia, estudios, experiencia..."><?php echo htmlspecialchars($Antecedentes); ?></textarea>


                                <!-- Validación -->

                                <?php

                                if (isset($errores["Antecedentes"])) {

                                    echo '<p class="text-danger mt-2">'
                                        . $errores["Antecedentes"] .
                                        '</p>';
                                }

                                ?>


                                <div class="registro-navegacion">

                                    <button
                                        type="button"
                                        class="boton-flecha"
                                        data-bs-target="#pasosRegistro"
                                        data-bs-slide="prev">

                                        ←

                                    </button>


                                    <button
                                        type="button"
                                        class="boton-flecha"
                                        data-bs-target="#pasosRegistro"
                                        data-bs-slide="next">

                                        →

                                    </button>

                                </div>


                            </div>

                        </div>



                        <!-- PASO 4 -->

                        <div class="carousel-item">

                            <div class="registro-contenido text-center">


                                <p class="registro-paso text-start">
                                    ENTRANDO A CURSOLANDIA 4/4
                                </p>


                                <h1>
                                    Agregá una foto de perfil
                                </h1>


                                <div class="foto-perfil">


                                    <div class="foto-placeholder">
                                        👤
                                    </div>


                                    <input
                                        type="file"
                                        id="FotoPerfil"
                                        name="FotoPerfil"
                                        accept=".jpg,.jpeg,.png">


                                    <label
                                        for="FotoPerfil"
                                        class="boton-camara">

                                        📷

                                    </label>


                                    <!-- Validación -->

                                    <?php

                                    if (isset($errores["FotoPerfil"])) {

                                        echo '<p class="text-danger mt-2">'
                                            . $errores["FotoPerfil"] .
                                            '</p>';
                                    }

                                    ?>


                                </div>


                                <?php

                                if (isset($errores["bd"])) {

                                    echo '<p class="text-danger mt-2">'
                                        . $errores["bd"] .
                                        '</p>';
                                }

                                ?>


                                <button
                                    type="submit"
                                    name="finalizar_registro"
                                    class="btn btn-finalizar">

                                    Finalizar

                                </button>


                                <div class="registro-navegacion justify-content-start">

                                    <button
                                        type="button"
                                        class="boton-flecha"
                                        data-bs-target="#pasosRegistro"
                                        data-bs-slide="prev">

                                        ←

                                    </button>

                                </div>


                            </div>

                        </div>


                    </div>

                </div>


            </form>


        </div>

    </main>


    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>