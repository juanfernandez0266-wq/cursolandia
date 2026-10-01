<?php

$errores = array();

$fechaNacimiento = "";
$intereses = array();
$antecedentes = "";

if (isset($_POST["finalizar_registro"])) {

    $fechaNacimiento = $_POST["fechaNacimiento"];

    if (isset($_POST["intereses"])) {
        $intereses = $_POST["intereses"];
    }

    $antecedentes = trim($_POST["antecedentes"]);

    if ($fechaNacimiento == "") {
        $errores["fechaNacimiento"] = "Debe ingresar su fecha de nacimiento.";
    }

    if (count($intereses) == 0) {
        $errores["intereses"] = "Debe seleccionar al menos un interés.";
    }

    if ($antecedentes == "") {
        $errores["antecedentes"] = "Debe ingresar sus antecedentes.";
    }

    if (!isset($_FILES["fotoPerfil"]) || $_FILES["fotoPerfil"]["name"] == "") {
        $errores["fotoPerfil"] = "Debe seleccionar una foto de perfil.";
    }

    if (count($errores) == 0) {
        $registroCompleto = true;
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

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/completar_registro.css">
</head>

<body>

    <main class="registro-completo">

        <div class="container">

            <form id="completarRegistro" method="POST" action="completar_registro.php" enctype="multipart/form-data">

                <div id="pasosRegistro" class="carousel slide" data-bs-interval="false">

                    <div class="carousel-inner">

                        <!-- PASO 1 -->
                        <div class="carousel-item active">

                            <div class="registro-contenido">

                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 1/4</p>

                                <h1>¿Cuál es tu fecha de nacimiento?</h1>

                                <p class="registro-descripcion">
                                    Ingresala dándole click al formulario
                                </p>

                                <div class="campo-grande">
                                    <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" value="<?php echo $fechaNacimiento; ?>">

                                    <!-- Validación de la fecha de nacimiento -->
                                     <?php
                                        if (isset($errores["fechaNacimiento"])) {
                                            echo '<p class="text-danger mt-2">'.$errores["fechaNacimiento"].'</p>';
                                        }
                                    ?>
                                </div>

                                <div class="registro-navegacion justify-content-end">
                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="next">→</button>
                                </div>

                            </div>

                        </div>


                        <!-- PASO 2 -->
                        <div class="carousel-item">

                            <div class="registro-contenido">

                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 2/4</p>

                                <h1>¿Cuáles son tus temas de interés?</h1>

                                <p class="registro-descripcion">
                                    Elegí al menos un interés de tu preferencia
                                </p>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <input type="checkbox" class="interes-check" id="aficiones" name="intereses[]" value="Aficiones">
                                        <label class="interes-opcion" for="aficiones">Aficiones</label>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="checkbox" class="interes-check" id="musica" name="intereses[]" value="Musica">
                                        <label class="interes-opcion" for="musica">Música</label>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="checkbox" class="interes-check" id="tecnologia" name="intereses[]" value="Tecnologia">
                                        <label class="interes-opcion" for="tecnologia">Tecnología</label>
                                    </div>

                                    <div class="col-md-6">
                                        <input type="checkbox" class="interes-check" id="salud" name="intereses[]" value="Salud">
                                        <label class="interes-opcion" for="salud">Salud</label>
                                    </div>

                                    <div class="col-md-6">
                                        <input type="checkbox" class="interes-check" id="deportes" name="intereses[]" value="Deportes">
                                        <label class="interes-opcion" for="deportes">Deportes</label>
                                    </div>

                                    <!-- Validación de los intereses -->
                                     <?php
                                        if (isset($errores["intereses"])) {
                                            echo '<p class="text-danger mt-2">'.$errores["intereses"].'</p>';
                                        }
                                    ?>
                                </div>

                                <div class="registro-navegacion">
                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="prev">←</button>
                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="next">→</button>
                                </div>

                            </div>

                        </div>


                        <!-- PASO 3 -->
                        <div class="carousel-item">

                            <div class="registro-contenido">

                                <p class="registro-paso">ENTRANDO A CURSOLANDIA 3/4</p>

                                <h1>¿Cuáles son tus antecedentes?</h1>

                                <p class="registro-descripcion">
                                    Contanos un poco sobre vos
                                </p>

                                <textarea class="form-control antecedentes" id="antecedentes" name="antecedentes" rows="5" placeholder="Escribí un poco sobre tu historia, estudios, experiencia..."> <?php echo $antecedentes; ?></textarea>

                                <!-- Validación de los antecedentes -->
                                 <?php
                                    if (isset($errores["antecedentes"])) {
                                        echo '<p class="text-danger mt-2">'.$errores["antecedentes"].'</p>';
                                    }
                                ?>
                                
                                <div class="registro-navegacion">
                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="prev">←</button>
                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="next">→</button>
                                </div>

                            </div>

                        </div>


                        <!-- PASO 4 -->
                        <div class="carousel-item">

                            <div class="registro-contenido text-center">

                                <p class="registro-paso text-start">ENTRANDO A CURSOLANDIA 4/4</p>

                                <h1>Agregá una foto de perfil</h1>

                                <div class="foto-perfil">

                                    <div class="foto-placeholder">👤</div>

                                    <input type="file" id="fotoPerfil" name="fotoPerfil" accept="image/*">

                                    <label for="fotoPerfil" class="boton-camara">📷</label>

                                    <!-- Validación de la foto de perfil -->
                                     <?php
                                        if (isset($errores["fotoPerfil"])) {
                                            echo '<p class="text-danger mt-2">'.$errores["fotoPerfil"].'</p>';
                                        }
                                    ?>
                                </div>

                                <!-- Validacion temporal de registro -->
                                <?php
                                    if (isset($registroCompleto)) {
                                        echo '<p class="text-success">Registro completado correctamente.</p>';
                                    }
                                ?>
                                
                                
                                <button type="submit" name="finalizar_registro" class="btn btn-finalizar">Finalizar</button>

                                <div class="registro-navegacion justify-content-start">
                                    <button type="button" class="boton-flecha" data-bs-target="#pasosRegistro" data-bs-slide="prev">←</button>
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

</body>

</html>