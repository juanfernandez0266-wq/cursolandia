<?php

session_start();

require "clases/usuario.php";



// COMPROBAR SI EL USUARIO INICIO SESION

if (!isset($_SESSION["idUsuario"])) {

    $_SESSION["volverDespuesLogin"] = "perfil.php";

    header("Location: iniciar_sesion.php");
    exit;
}



// BUSCAR AL USUARIO LOGUEADO

$usuarioActual = Usuario::buscarPorId(
    $_SESSION["idUsuario"]
);


if ($usuarioActual == null) {

    session_unset();
    session_destroy();

    header("Location: iniciar_sesion.php");
    exit;
}



// DATOS DEL USUARIO

$errores = array();

$guardadoCorrecto = false;


$idUsuario = $usuarioActual->idUsuario;

$Nombre = $usuarioActual->Nombre;

$Apellido = $usuarioActual->Apellido;

$NombreUsuario = $usuarioActual->NombreUsuario;

$email = $usuarioActual->email;

$FechaNacimiento = $usuarioActual->FechaNacimiento;

$Intereses = $usuarioActual->Intereses;

$Antecedentes = $usuarioActual->Antecedentes;

$FotoPerfil = $usuarioActual->FotoPerfil;

$PlanTipo = $usuarioActual->PlanTipo;

$FechaVencimiento = $usuarioActual->FechaVencimiento;



// GUARDAR CAMBIOS DEL PERFIL

if (isset($_POST["guardar_cambios"])) {

    $Nombre = $_POST["Nombre"];

    $Apellido = $_POST["Apellido"];

    $NombreUsuario = $_POST["NombreUsuario"];

    $Antecedentes = $_POST["Antecedentes"];


    // FOTO NUEVA

    $FotoPerfilNueva = null;

    if (isset($_FILES["FotoPerfil"])) {

        $FotoPerfilNueva =
            $_FILES["FotoPerfil"];
    }


    // MODIFICAR PERFIL

    $resultado =
        $usuarioActual->modificarperfil(
            $Nombre,
            $Apellido,
            $NombreUsuario,
            $Antecedentes,
            $FotoPerfilNueva
        );


    // SI HAY ERRORES

    if (is_array($resultado)) {

        $errores = $resultado;

    }


    // SI LOS CAMBIOS SE GUARDARON

    else {

        $usuarioActual = $resultado;


        $Nombre =
            $usuarioActual->Nombre;

        $Apellido =
            $usuarioActual->Apellido;

        $NombreUsuario =
            $usuarioActual->NombreUsuario;

        $Antecedentes =
            $usuarioActual->Antecedentes;

        $FotoPerfil =
            $usuarioActual->FotoPerfil;


        $guardadoCorrecto = true;
    }
}



// CAMBIAR CONTRASEÑA

if (isset($_POST["cambiar_contraseña"])) {

    $ContraseñaActual =
        $_POST["ContraseñaActual"];

    $NuevaContraseña =
        $_POST["NuevaContraseña"];

    $ConfirmarContraseña =
        $_POST["ConfirmarContraseña"];


    $resultadoContraseña =
        $usuarioActual->cambiarContraseña(
            $ContraseñaActual,
            $NuevaContraseña,
            $ConfirmarContraseña
        );


    // SI HAY ERRORES

    if (is_array($resultadoContraseña)) {

        $errores = $resultadoContraseña;

    }


    // SI LA CONTRASEÑA SE CAMBIO CORRECTAMENTE

    else {

        session_unset();
        session_destroy();

        header(
            "Location: iniciar_sesion.php?contraseña_cambiada=1"
        );

        exit;
    }
}



// FOTO A MOSTRAR

$rutaFotoMostrar = $FotoPerfil;


// SI NO TIENE FOTO O LA FOTO NO EXISTE, MOSTRAR LA PREDETERMINADA

if (
    $rutaFotoMostrar == null ||
    $rutaFotoMostrar == "" ||
    !file_exists(__DIR__ . "/" . $rutaFotoMostrar)
) {

    $rutaFotoMostrar =
        "imagenes/perfil_predeterminado.png";
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
        Mi Perfil - Cursolandia
    </title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- CSS propio -->

    <link
        rel="stylesheet"
        href="css/perfil.css">

</head>


<body>


    <main class="perfil">


        <div class="container py-5">


            <form
                method="POST"
                action="perfil.php"
                enctype="multipart/form-data">



                <!-- INFORMACIÓN PERSONAL -->

                <section class="perfil-seccion">


                    <div class="seccion-contenido">


                        <h1 class="titulo-seccion">

                            Información personal

                        </h1>


                        <p class="descripcion-seccion">

                            Detalles sobre el perfil e información de la cuenta.

                        </p>



                        <div class="row align-items-start mt-4">



                            <!-- FOTO E ID -->

                            <div class="col-lg-2">


                                <!-- FOTO DE PERFIL -->

                                <div class="perfil-foto">


                                    <div class="foto-edicion">


                                        <img
                                            src="<?php echo htmlspecialchars($rutaFotoMostrar); ?>"
                                            class="foto-perfil-imagen"
                                            id="vistaFotoPerfil"
                                            alt="Foto de perfil"
                                            onerror="this.src='imagenes/perfil_predeterminado.png';">


                                        <!-- LAPIZ PARA CAMBIAR FOTO -->

                                        <label
                                            for="FotoPerfil"
                                            class="foto-editar"
                                            title="Cambiar foto de perfil">

                                            ✎

                                        </label>


                                        <!-- ARCHIVO OCULTO -->

                                        <input
                                            type="file"
                                            id="FotoPerfil"
                                            name="FotoPerfil"
                                            class="foto-input"
                                            accept=".jpg,.jpeg,.png">


                                    </div>


                                </div>



                                <!-- ID USUARIO -->

                                <div class="usuario-id">


                                    <span>

                                        ID usuario:

                                        <strong id="idUsuario">

                                            <?php
                                                echo $idUsuario;
                                            ?>

                                        </strong>

                                    </span>


                                    <button
                                        type="button"
                                        class="btn-copiar"
                                        onclick="copiarId()"
                                        title="Copiar ID">

                                        ⧉

                                    </button>


                                </div>



                                <!-- ERROR FOTO -->

                                <?php

                                if (isset($errores["FotoPerfil"])) {

                                    echo '<p class="text-danger mt-2">'
                                        . $errores["FotoPerfil"]
                                        . '</p>';
                                }

                                ?>


                            </div>



                            <!-- CAMPOS PRINCIPALES -->

                            <div class="col-lg-10">


                                <div class="row g-4">



                                    <!-- NOMBRE COMPLETO -->

                                    <div class="col-md-6">


                                        <label class="form-label">

                                            Nombre completo

                                            <span class="obligatorio">
                                                *
                                            </span>

                                        </label>



                                        <div class="row g-2">



                                            <!-- NOMBRE -->

                                            <div class="col-md-6">


                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="Nombre"
                                                    name="Nombre"
                                                    value="<?php echo htmlspecialchars($Nombre); ?>"
                                                    placeholder="Nombre">


                                            </div>



                                            <!-- APELLIDO -->

                                            <div class="col-md-6">


                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="Apellido"
                                                    name="Apellido"
                                                    value="<?php echo htmlspecialchars($Apellido); ?>"
                                                    placeholder="Apellido">


                                            </div>


                                        </div>



                                        <!-- ERROR NOMBRE -->

                                        <?php

                                        if (isset($errores["Nombre"])) {

                                            echo '<p class="text-danger mt-1">'
                                                . $errores["Nombre"]
                                                . '</p>';
                                        }

                                        ?>



                                        <!-- ERROR APELLIDO -->

                                        <?php

                                        if (isset($errores["Apellido"])) {

                                            echo '<p class="text-danger mt-1">'
                                                . $errores["Apellido"]
                                                . '</p>';
                                        }

                                        ?>


                                    </div>



                                    <!-- NOMBRE DE USUARIO -->

                                    <div class="col-md-6">


                                        <label
                                            for="NombreUsuario"
                                            class="form-label">

                                            Nombre de usuario

                                            <span class="obligatorio">
                                                *
                                            </span>

                                        </label>


                                        <div class="campo-editable">


                                            <input
                                                type="text"
                                                class="form-control"
                                                id="NombreUsuario"
                                                name="NombreUsuario"
                                                value="<?php echo htmlspecialchars($NombreUsuario); ?>">


                                            <span class="icono-editar">

                                                ✎

                                            </span>


                                        </div>



                                        <!-- ERROR NOMBRE DE USUARIO -->

                                        <?php

                                        if (isset($errores["NombreUsuario"])) {

                                            echo '<p class="text-danger mt-1">'
                                                . $errores["NombreUsuario"]
                                                . '</p>';
                                        }

                                        ?>


                                    </div>



                                    <!-- EMAIL -->

                                    <div class="col-md-6">


                                        <label
                                            for="email"
                                            class="form-label">

                                            Email completo

                                        </label>


                                        <input
                                            type="email"
                                            class="form-control campo-bloqueado"
                                            id="email"
                                            value="<?php echo htmlspecialchars($email); ?>"
                                            disabled>


                                    </div>



                                    <!-- FECHA DE NACIMIENTO -->

                                    <div class="col-md-6">


                                        <label
                                            for="FechaNacimiento"
                                            class="form-label">

                                            Fecha de nacimiento

                                        </label>


                                        <input
                                            type="text"
                                            class="form-control campo-bloqueado"
                                            id="FechaNacimiento"
                                            value="<?php

                                                if (
                                                    $FechaNacimiento != null &&
                                                    $FechaNacimiento != ""
                                                ) {

                                                    echo date(
                                                        "d/m/Y",
                                                        strtotime($FechaNacimiento)
                                                    );
                                                }

                                            ?>"
                                            disabled>


                                    </div>


                                </div>


                            </div>


                        </div>



                        <!-- INTERESES -->

                        <div class="mt-4">


                            <label class="form-label">

                                Intereses

                            </label>


                            <div class="intereses-contenedor">


                                <?php

                                foreach ($Intereses as $Interes) {

                                    echo '<span class="interes-etiqueta">'
                                        . htmlspecialchars($Interes)
                                        . '</span>';
                                }

                                ?>


                            </div>


                        </div>



                        <!-- ANTECEDENTES -->

                        <div class="mt-4">


                            <label
                                for="Antecedentes"
                                class="form-label">

                                Antecedentes

                            </label>


                            <textarea
                                class="form-control antecedentes"
                                id="Antecedentes"
                                name="Antecedentes"
                                rows="3"><?php echo htmlspecialchars($Antecedentes); ?></textarea>



                            <!-- ERROR ANTECEDENTES -->

                            <?php

                            if (isset($errores["Antecedentes"])) {

                                echo '<p class="text-danger mt-1">'
                                    . $errores["Antecedentes"]
                                    . '</p>';
                            }

                            ?>


                        </div>


                    </div>


                </section>



                <!-- PRIVACIDAD Y SEGURIDAD -->

                <section class="perfil-seccion">


                    <div class="seccion-contenido">


                        <h2 class="titulo-seccion">

                            Privacidad y seguridad

                        </h2>


                        <p class="descripcion-seccion">

                            Gestioná la contraseña y la seguridad de tu cuenta.

                        </p>



                        <div class="seguridad-contenedor mt-4">


                            <h3>

                                Cambiar contraseña

                            </h3>



                            <div class="aviso-seguridad">


                                <span class="aviso-icono">

                                    ⓘ

                                </span>


                                <span>

                                    Para garantizar tu seguridad, cerraremos tu sesión
                                    cuando cambies tu contraseña.

                                    <strong>

                                        Utilizá la nueva contraseña para iniciar sesión
                                        de nuevo.

                                    </strong>

                                </span>


                            </div>



                            <!-- CONTRASEÑA ACTUAL -->

                            <div class="mt-4 campo-password">


                                <label
                                    for="ContraseñaActual"
                                    class="form-label">

                                    Contraseña actual

                                </label>


                                <div class="password-contenedor">


                                    <input
                                        type="password"
                                        class="form-control"
                                        id="ContraseñaActual"
                                        name="ContraseñaActual"
                                        placeholder="Escribe tu contraseña actual">


                                    <button
                                        type="button"
                                        class="mostrar-password"
                                        onclick="mostrarContraseña('ContraseñaActual')">

                                        ◉

                                    </button>


                                </div>


                                <small>

                                    Escribe la contraseña que usas actualmente.

                                </small>



                                <!-- ERROR CONTRASEÑA ACTUAL -->

                                <?php

                                if (isset($errores["ContraseñaActual"])) {

                                    echo '<p class="text-danger mt-1">'
                                        . $errores["ContraseñaActual"]
                                        . '</p>';
                                }

                                ?>


                            </div>



                            <div class="row g-4 mt-1">



                                <!-- NUEVA CONTRASEÑA -->

                                <div class="col-md-6 campo-password">


                                    <label
                                        for="NuevaContraseña"
                                        class="form-label">

                                        Nueva contraseña

                                    </label>


                                    <div class="password-contenedor">


                                        <input
                                            type="password"
                                            class="form-control"
                                            id="NuevaContraseña"
                                            name="NuevaContraseña"
                                            placeholder="Introduce la nueva contraseña">


                                        <button
                                            type="button"
                                            class="mostrar-password"
                                            onclick="mostrarContraseña('NuevaContraseña')">

                                            ◉

                                        </button>


                                    </div>


                                    <small>

                                        Define una nueva contraseña aquí.

                                    </small>



                                    <!-- ERROR NUEVA CONTRASEÑA -->

                                    <?php

                                    if (isset($errores["NuevaContraseña"])) {

                                        echo '<p class="text-danger mt-1">'
                                            . $errores["NuevaContraseña"]
                                            . '</p>';
                                    }

                                    ?>


                                </div>



                                <!-- CONFIRMAR CONTRASEÑA -->

                                <div class="col-md-6 campo-password">


                                    <label
                                        for="ConfirmarContraseña"
                                        class="form-label">

                                        Confirmar contraseña

                                    </label>


                                    <div class="password-contenedor">


                                        <input
                                            type="password"
                                            class="form-control"
                                            id="ConfirmarContraseña"
                                            name="ConfirmarContraseña"
                                            placeholder="Repite la nueva contraseña">


                                        <button
                                            type="button"
                                            class="mostrar-password"
                                            onclick="mostrarContraseña('ConfirmarContraseña')">

                                            ◉

                                        </button>


                                    </div>


                                    <small>

                                        Repite la nueva contraseña aquí.

                                    </small>



                                    <!-- ERROR CONFIRMAR CONTRASEÑA -->

                                    <?php

                                    if (isset($errores["ConfirmarContraseña"])) {

                                        echo '<p class="text-danger mt-1">'
                                            . $errores["ConfirmarContraseña"]
                                            . '</p>';
                                    }

                                    ?>


                                </div>


                            </div>



                            <!-- CAMBIAR CONTRASEÑA -->

                            <button
                                type="submit"
                                name="cambiar_contraseña"
                                class="btn btn-primary btn-cambiar-password mt-4">

                                Cambiar contraseña

                            </button>



                            <!-- ERROR CAMBIO DE CONTRASEÑA -->

                            <?php

                            if (isset($errores["CambiarContraseña"])) {

                                echo '<p class="text-danger mt-2">'
                                    . $errores["CambiarContraseña"]
                                    . '</p>';
                            }

                            ?>


                        </div>


                    </div>


                </section>



                <!-- PLAN TIPO -->

                <section class="perfil-seccion">


                    <div class="seccion-contenido">


                        <h2 class="titulo-seccion">

                            Plan tipo

                        </h2>



                        <div class="plan-contenedor mt-4">


                            <div class="plan-icono">

                                ★

                            </div>


                            <div>


                                <?php

                                if ($PlanTipo == "pro") {

                                ?>

                                    <h3 class="plan-titulo">

                                        Plan Pro

                                    </h3>


                                    <p>

                                        Estás utilizando el Plan Pro de Cursolandia.

                                    </p>


                                <?php

                                }
                                else {

                                ?>

                                    <h3 class="plan-titulo">

                                        Plan gratuito

                                    </h3>


                                    <p>

                                        Estás utilizando el plan gratuito de Cursolandia.

                                    </p>


                                <?php

                                }

                                ?>


                            </div>


                        </div>



                        <!-- FECHA DE VENCIMIENTO -->

                        <?php

                        if (
                            $PlanTipo == "pro" &&
                            $FechaVencimiento != null
                        ) {

                        ?>

                            <div class="plan-informacion">


                                <span>

                                    ⓘ

                                </span>


                                <span>

                                    Tu Plan Pro vence el

                                    <strong>

                                        <?php

                                        echo date(
                                            "d/m/Y",
                                            strtotime($FechaVencimiento)
                                        );

                                        ?>

                                    </strong>

                                </span>


                            </div>


                        <?php

                        }

                        ?>



                        <!-- MENSAJE GUARDADO -->

                        <?php

                        if ($guardadoCorrecto) {

                            echo '<p class="text-success text-end mt-4">
                                    Cambios guardados correctamente.
                                  </p>';
                        }

                        ?>



                        <!-- ERROR BD -->

                        <?php

                        if (isset($errores["bd"])) {

                            echo '<p class="text-danger text-end mt-4">'
                                . $errores["bd"]
                                . '</p>';
                        }

                        ?>



                        <!-- GUARDAR CAMBIOS -->

                        <div class="guardar-contenedor">


                            <button
                                type="submit"
                                name="guardar_cambios"
                                class="btn btn-primary btn-guardar">

                                Guardar cambios

                            </button>


                        </div>


                    </div>


                </section>


            </form>


        </div>


    </main>



    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
    </script>



    <script>


        // COPIAR ID DEL USUARIO

        function copiarId() {

            let idUsuario =
                document.getElementById("idUsuario").innerText;

            navigator.clipboard.writeText(idUsuario);
        }



        // MOSTRAR U OCULTAR CONTRASEÑA

        function mostrarContraseña(idCampo) {

            let campo =
                document.getElementById(idCampo);


            if (campo.type === "password") {

                campo.type = "text";

            }
            else {

                campo.type = "password";
            }
        }



        // PREVISUALIZAR FOTO DE PERFIL

        document
            .getElementById("FotoPerfil")
            .addEventListener("change", function () {

                if (this.files && this.files[0]) {

                    let lector = new FileReader();


                    lector.onload = function (evento) {

                        document
                            .getElementById("vistaFotoPerfil")
                            .src = evento.target.result;
                    };


                    lector.readAsDataURL(this.files[0]);
                }
            });


    </script>


</body>


</html>