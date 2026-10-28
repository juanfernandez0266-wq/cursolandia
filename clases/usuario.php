<?php

class Usuario {

    private $idUsuario;
    private $Nombre;
    private $Apellido;
    private $FechaNacimiento;
    private $email;
    private $NombreUsuario;
    private $Contraseña;
    private $FotoPerfil;
    private $PlanTipo;
    private $Intereses;
    private $Antecedentes;
    private $FechaVencimiento;
    private $Rol_Usuario;


    public function __construct($Nombre, $Apellido, $email, $NombreUsuario, $Contraseña) {

        $this->Nombre = $Nombre;
        $this->Apellido = $Apellido;
        $this->email = $email;
        $this->NombreUsuario = $NombreUsuario;
        $this->Contraseña = $Contraseña;

        $this->FechaNacimiento = null;
        $this->FotoPerfil = null;
        $this->PlanTipo = "gratuito";
        $this->Intereses = array();
        $this->Antecedentes = null;
        $this->FechaVencimiento = null;
        $this->Rol_Usuario = "usuario";
    }


    public static function registrar($Nombre, $Apellido, $email, $NombreUsuario, $Contraseña) {

        $errores = array();

        $Nombre = trim($Nombre);
        $Apellido = trim($Apellido);
        $email = trim($email);
        $NombreUsuario = trim($NombreUsuario);
        $Contraseña = trim($Contraseña);


        // VALIDACION DEL NOMBRE

        if ($Nombre == "") {
            $errores["Nombre"] = "Debe ingresar un nombre.";
        }
        elseif (strlen($Nombre) > 20) {
            $errores["Nombre"] = "El nombre no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $Nombre)) {
            $errores["Nombre"] = "El nombre solo puede contener letras.";
        }


        // VALIDACION DEL APELLIDO

        if ($Apellido == "") {
            $errores["Apellido"] = "Debe ingresar un apellido.";
        }
        elseif (strlen($Apellido) > 20) {
            $errores["Apellido"] = "El apellido no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $Apellido)) {
            $errores["Apellido"] = "El apellido solo puede contener letras.";
        }


        // VALIDACION DEL EMAIL

        if ($email == "") {
            $errores["email"] = "Debe ingresar un correo electrónico.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9._]+@gmail\.com$/", $email)) {
            $errores["email"] = "El correo debe tener el formato usuario@gmail.com.";
        }


        // VALIDACION DEL NOMBRE DE USUARIO

        if ($NombreUsuario == "") {
            $errores["NombreUsuario"] = "Debe ingresar un nombre de usuario.";
        }
        elseif (strlen($NombreUsuario) > 20) {
            $errores["NombreUsuario"] = "El nombre de usuario no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $NombreUsuario)) {
            $errores["NombreUsuario"] = "El nombre de usuario solo puede contener letras, números y guión bajo.";
        }


        // VALIDACION DE LA CONTRASEÑA

        if ($Contraseña == "") {
            $errores["Contraseña"] = "Debe ingresar una contraseña.";
        }
        elseif (strlen($Contraseña) < 8) {
            $errores["Contraseña"] = "La contraseña debe tener al menos 8 caracteres.";
        }
        elseif (strlen($Contraseña) > 20) {
            $errores["Contraseña"] = "La contraseña no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9]+$/", $Contraseña)) {
            $errores["Contraseña"] = "La contraseña solo puede contener letras y números.";
        }


        // SI HAY ERRORES DE VALIDACION

        if (count($errores) > 0) {
            return $errores;
        }


        // CONEXION CON MYSQL

        require __DIR__ . "/../bd/conexion.php";


        // COMPROBAR SI EL EMAIL YA EXISTE

        $sql = "SELECT idUsuario
                FROM usuario
                WHERE email = '$email'";

        $resultado = mysqli_query($conexion, $sql);

        if (mysqli_num_rows($resultado) > 0) {
            $errores["email"] = "El correo electrónico ya se encuentra registrado.";
        }


        // COMPROBAR SI EL NOMBRE DE USUARIO YA EXISTE

        $sql = "SELECT idUsuario
                FROM usuario
                WHERE NombreUsuario = '$NombreUsuario'";

        $resultado = mysqli_query($conexion, $sql);

        if (mysqli_num_rows($resultado) > 0) {
            $errores["NombreUsuario"] = "El nombre de usuario ya se encuentra registrado.";
        }


        // SI HAY ERRORES POR DATOS REPETIDOS

        if (count($errores) > 0) {
            mysqli_close($conexion);
            return $errores;
        }


        // PROTEGER LA CONTRASEÑA

        $ContraseñaHash = password_hash($Contraseña, PASSWORD_DEFAULT);


        // GUARDAR EL USUARIO EN MYSQL

        $sql = "INSERT INTO usuario
                (Nombre, Apellido, email, NombreUsuario, Contraseña)
                VALUES
                ('$Nombre', '$Apellido', '$email', '$NombreUsuario', '$ContraseñaHash')";

        $resultado = mysqli_query($conexion, $sql);


        // COMPROBAR QUE EL INSERT SE REALIZO

        if (!$resultado) {

            $errores["bd"] = "No se pudo registrar el usuario.";

            mysqli_close($conexion);

            return $errores;
        }


        // CREAR EL OBJETO USUARIO

        $usuario = new Usuario(
            $Nombre,
            $Apellido,
            $email,
            $NombreUsuario,
            $ContraseñaHash
        );


        // OBTENER EL ID GENERADO POR MYSQL

        $usuario->idUsuario = mysqli_insert_id($conexion);


        // CERRAR CONEXION

        mysqli_close($conexion);


        return $usuario;
    }
        public static function buscarPorId($idUsuario) {

            require __DIR__ . "/../bd/conexion.php";

            $idUsuario = intval($idUsuario);

            $sql = "SELECT *
                    FROM usuario
                    WHERE idUsuario = $idUsuario";

            $resultado = mysqli_query($conexion, $sql);

            if (mysqli_num_rows($resultado) == 0) {
                mysqli_close($conexion);
                return null;
            }

            $datos = mysqli_fetch_assoc($resultado);

            $usuario = new Usuario(
                $datos["Nombre"],
                $datos["Apellido"],
                $datos["email"],
                $datos["NombreUsuario"],
                $datos["Contraseña"]
            );

            $usuario->idUsuario = $datos["idUsuario"];
            $usuario->FechaNacimiento = $datos["FechaNacimiento"];
            $usuario->FotoPerfil = $datos["FotoPerfil"];
            $usuario->PlanTipo = $datos["PlanTipo"];
            $usuario->Antecedentes = $datos["Antecedentes"];
            $usuario->FechaVencimiento = $datos["FechaVencimiento"];
            $usuario->Rol_Usuario = $datos["Rol_Usuario"];

            //BUSCA LOS INTERESES QUE TIENE ASOCIADOS AL USUARIO
            $usuario->Intereses = array();

            $sqlIntereses = "SELECT interes.NombreInteres
                            FROM usuariointeres
                            INNER JOIN interes
                            ON usuariointeres.idInteres = interes.idInteres
                            WHERE usuariointeres.idUsuario = $idUsuario";

            $resultadoIntereses = mysqli_query(
                $conexion,
                $sqlIntereses
            );

            // GUARDAR LOS INTERESES EN EL OBJETO USUARIO
            while ($filaInteres = mysqli_fetch_assoc($resultadoIntereses)) {

                $usuario->Intereses[] =
                    $filaInteres["NombreInteres"];
            }

            mysqli_close($conexion);

            return $usuario;
        }

    public function completarperfil($FechaNacimiento, $Intereses, $Antecedentes, $FotoPerfil) {

        $errores = array();

        $FechaNacimiento = trim($FechaNacimiento);
        $Antecedentes = trim($Antecedentes);


        // VALIDACION DE FECHA DE NACIMIENTO

        if ($FechaNacimiento == "") {
            $errores["FechaNacimiento"] = "Debe ingresar una fecha de nacimiento.";
        }
        elseif (strtotime($FechaNacimiento) === false) {
            $errores["FechaNacimiento"] = "La fecha ingresada no es válida.";
        }
        elseif (strtotime($FechaNacimiento) > time()) {
            $errores["FechaNacimiento"] = "La fecha de nacimiento no puede ser futura.";
        }


        // VALIDACION DE INTERESES

        $interesesPermitidos = array(
            "Aficiones",
            "Musica",
            "Tecnologia",
            "Salud",
            "Deportes"
        );

        if (!is_array($Intereses) || count($Intereses) == 0) {

            $errores["Intereses"] = "Debe seleccionar al menos un interés.";

        }
        else {

            foreach ($Intereses as $Interes) {

                if (!in_array($Interes, $interesesPermitidos)) {
                    $errores["Intereses"] = "Se seleccionó un interés no válido.";
                }
            }
        }


        // VALIDACION DE ANTECEDENTES

        if ($Antecedentes == "") {
            $errores["Antecedentes"] = "Debe ingresar sus antecedentes.";
        }


        // VALIDACION DE FOTO

        if (!isset($FotoPerfil) || $FotoPerfil["name"] == "") {

            $errores["FotoPerfil"] = "Debe seleccionar una foto de perfil.";

        }
        elseif ($FotoPerfil["error"] != 0) {

            $errores["FotoPerfil"] = "Ocurrió un error al subir la imagen.";

        }
        else {

            $extension = strtolower(
                pathinfo($FotoPerfil["name"], PATHINFO_EXTENSION)
            );

            if (
                $extension != "jpg" &&
                $extension != "jpeg" &&
                $extension != "png"
            ) {
                $errores["FotoPerfil"] = "La imagen debe ser JPG, JPEG o PNG.";
            }
        }


        // SI HAY ERRORES

        if (count($errores) > 0) {
            return $errores;
        }


        // CONEXION

        require __DIR__ . "/../bd/conexion.php";


        // GUARDAR FOTO

        $carpeta = __DIR__ . "/../uploads/perfiles/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreFoto =
            $this->idUsuario .
            "_" .
            time() .
            "." .
            $extension;

        $rutaFoto = $carpeta . $nombreFoto;


        if (!move_uploaded_file($FotoPerfil["tmp_name"], $rutaFoto)) {

            $errores["FotoPerfil"] = "No se pudo guardar la foto de perfil.";

            mysqli_close($conexion);

            return $errores;
        }


        // RUTA QUE SE GUARDARA EN MYSQL

        $FotoPerfilBD = "uploads/perfiles/" . $nombreFoto;


        // PREPARAR ANTECEDENTES PARA MYSQL

        $AntecedentesBD =
            mysqli_real_escape_string($conexion, $Antecedentes);


        // ACTUALIZAR DATOS DEL USUARIO

        $sql = "UPDATE usuario
                SET FechaNacimiento = '$FechaNacimiento',
                    FotoPerfil = '$FotoPerfilBD',
                    Antecedentes = '$AntecedentesBD'
                WHERE idUsuario = $this->idUsuario";

        $resultado = mysqli_query($conexion, $sql);


        if (!$resultado) {

            $errores["bd"] = "No se pudo completar el perfil.";

            mysqli_close($conexion);

            return $errores;
        }


        // ELIMINAR INTERESES ANTERIORES

        $sql = "DELETE FROM usuariointeres
                WHERE idUsuario = $this->idUsuario";

        mysqli_query($conexion, $sql);


        // GUARDAR LOS INTERESES SELECCIONADOS

        foreach ($Intereses as $Interes) {

            $sql = "SELECT idInteres
                    FROM interes
                    WHERE NombreInteres = '$Interes'";

            $resultadoInteres = mysqli_query($conexion, $sql);

            if (mysqli_num_rows($resultadoInteres) > 0) {

                $datosInteres =
                    mysqli_fetch_assoc($resultadoInteres);

                $idInteres = $datosInteres["idInteres"];


                $sql = "INSERT INTO usuariointeres
                        (idUsuario, idInteres)
                        VALUES
                        ($this->idUsuario, $idInteres)";

                mysqli_query($conexion, $sql);
            }
        }


        // ACTUALIZAR EL OBJETO

        $this->FechaNacimiento = $FechaNacimiento;
        $this->Intereses = $Intereses;
        $this->Antecedentes = $Antecedentes;
        $this->FotoPerfil = $FotoPerfilBD;


        mysqli_close($conexion);

        return $this;
    }


    public function modificarperfil( 
        $Nombre,
        $Apellido,
        $NombreUsuario,
        $Antecedentes,
        $FotoPerfil = null,
        
        ) {

        $errores = array();

        $Nombre = trim($Nombre);
        $Apellido = trim($Apellido);
        $NombreUsuario = trim($NombreUsuario);
        $Antecedentes = trim($Antecedentes);


        // VALIDACION DEL NOMBRE

        if ($Nombre == "") {
            $errores["Nombre"] = "Debe ingresar un nombre.";
        }
        elseif (strlen($Nombre) > 20) {
            $errores["Nombre"] = "El nombre no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $Nombre)) {
            $errores["Nombre"] = "El nombre solo puede contener letras.";
        }


        // VALIDACION DEL APELLIDO

        if ($Apellido == "") {
            $errores["Apellido"] = "Debe ingresar un apellido.";
        }
        elseif (strlen($Apellido) > 20) {
            $errores["Apellido"] = "El apellido no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $Apellido)) {
            $errores["Apellido"] = "El apellido solo puede contener letras.";
        }


        // VALIDACION DEL NOMBRE DE USUARIO

        if ($NombreUsuario == "") {
            $errores["NombreUsuario"] = "Debe ingresar un nombre de usuario.";
        }
        elseif (strlen($NombreUsuario) > 20) {
            $errores["NombreUsuario"] = "El nombre de usuario no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $NombreUsuario)) {
            $errores["NombreUsuario"] = "El nombre de usuario solo puede contener letras, números y guión bajo.";
        }


        // VALIDACION DE ANTECEDENTES

        if ($Antecedentes == "") {
            $errores["Antecedentes"] = "Debe ingresar sus antecedentes.";
        }


        if (count($errores) > 0) {
            return $errores;
        }


        // CONEXION

        require __DIR__ . "/../bd/conexion.php";


        // COMPROBAR QUE EL NOMBRE DE USUARIO NO LO TENGA OTRO USUARIO

        $NombreUsuarioBD =
            mysqli_real_escape_string($conexion, $NombreUsuario);

        $sql = "SELECT idUsuario
                FROM usuario
                WHERE NombreUsuario = '$NombreUsuarioBD'
                AND idUsuario != $this->idUsuario";

        $resultado = mysqli_query($conexion, $sql);


        if (mysqli_num_rows($resultado) > 0) {

            $errores["NombreUsuario"] =
                "El nombre de usuario ya se encuentra registrado.";

            mysqli_close($conexion);

            return $errores;
        }


        // MANTENER LA FOTO ACTUAL POR DEFECTO

        $FotoPerfilBD = $this->FotoPerfil;


        


        // SUBIR UNA NUEVA FOTO

        if (
            isset($FotoPerfil) &&
            isset($FotoPerfil["name"]) &&
            $FotoPerfil["name"] != ""
        ) {

            if ($FotoPerfil["error"] != 0) {

                $errores["FotoPerfil"] =
                    "Ocurrió un error al subir la imagen.";
            }
            else {

                $extension = strtolower(
                    pathinfo(
                        $FotoPerfil["name"],
                        PATHINFO_EXTENSION
                    )
                );


                if (
                    $extension != "jpg" &&
                    $extension != "jpeg" &&
                    $extension != "png"
                ) {

                    $errores["FotoPerfil"] =
                        "La imagen debe ser JPG, JPEG o PNG.";
                }
                else {

                    $carpeta =
                        __DIR__ . "/../uploads/perfiles/";


                    if (!is_dir($carpeta)) {

                        mkdir(
                            $carpeta,
                            0777,
                            true
                        );
                    }


                    $nombreFoto =
                        $this->idUsuario .
                        "_" .
                        time() .
                        "." .
                        $extension;


                    $rutaFoto =
                        $carpeta . $nombreFoto;


                    if (
                        move_uploaded_file(
                            $FotoPerfil["tmp_name"],
                            $rutaFoto
                        )
                    ) {

                        $FotoPerfilBD =
                            "uploads/perfiles/" .
                            $nombreFoto;
                    }
                    else {

                        $errores["FotoPerfil"] =
                            "No se pudo guardar la foto de perfil.";
                    }
                }
            }
        }


        if (count($errores) > 0) {

            mysqli_close($conexion);

            return $errores;
        }


        // PREPARAR DATOS PARA MYSQL

        $NombreBD =
            mysqli_real_escape_string($conexion, $Nombre);

        $ApellidoBD =
            mysqli_real_escape_string($conexion, $Apellido);

        $AntecedentesBD =
            mysqli_real_escape_string($conexion, $Antecedentes);

        $FotoPerfilBD =
            mysqli_real_escape_string($conexion, $FotoPerfilBD);


        // ACTUALIZAR USUARIO

        $sql = "UPDATE usuario
                SET Nombre = '$NombreBD',
                    Apellido = '$ApellidoBD',
                    NombreUsuario = '$NombreUsuarioBD',
                    Antecedentes = '$AntecedentesBD',
                    FotoPerfil = '$FotoPerfilBD'
                WHERE idUsuario = $this->idUsuario";


        $resultado =
            mysqli_query($conexion, $sql);


        if (!$resultado) {

            $errores["bd"] =
                "No se pudieron guardar los cambios.";

            mysqli_close($conexion);

            return $errores;
        }


        // ACTUALIZAR OBJETO

        $this->Nombre = $Nombre;
        $this->Apellido = $Apellido;
        $this->NombreUsuario = $NombreUsuario;
        $this->Antecedentes = $Antecedentes;
        $this->FotoPerfil = $FotoPerfilBD;


        mysqli_close($conexion);


        return $this;
    }


    public function cambiarContraseña(
        $ContraseñaActual,
        $NuevaContraseña,
        $ConfirmarContraseña
    ) {

        $errores = array();


        // VALIDACION DE LA CONTRASEÑA ACTUAL

        if ($ContraseñaActual == "") {

            $errores["ContraseñaActual"] =
                "Debe ingresar su contraseña actual.";
        }


        // VALIDACION DE LA NUEVA CONTRASEÑA

        if ($NuevaContraseña == "") {

            $errores["NuevaContraseña"] =
                "Debe ingresar una nueva contraseña.";
        }
        elseif (strlen($NuevaContraseña) < 8) {

            $errores["NuevaContraseña"] =
                "La contraseña debe tener al menos 8 caracteres.";
        }
        elseif (strlen($NuevaContraseña) > 20) {

            $errores["NuevaContraseña"] =
                "La contraseña no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9]+$/", $NuevaContraseña)) {

            $errores["NuevaContraseña"] =
                "La contraseña solo puede contener letras y números.";
        }


        // CONFIRMAR NUEVA CONTRASEÑA

        if ($ConfirmarContraseña == "") {

            $errores["ConfirmarContraseña"] =
                "Debe confirmar la nueva contraseña.";
        }
        elseif ($NuevaContraseña != $ConfirmarContraseña) {

            $errores["ConfirmarContraseña"] =
                "Las contraseñas no coinciden.";
        }


        // SI HAY ERRORES

        if (count($errores) > 0) {

            return $errores;
        }


        // COMPROBAR CONTRASEÑA ACTUAL

        if (!password_verify(
            $ContraseñaActual,
            $this->Contraseña
        )) {

            $errores["ContraseñaActual"] =
                "La contraseña actual es incorrecta.";

            return $errores;
        }


        // GENERAR NUEVO HASH

        $NuevaContraseñaHash =
            password_hash(
                $NuevaContraseña,
                PASSWORD_DEFAULT
            );


        // CONEXION

        require __DIR__ . "/../bd/conexion.php";


        // ACTUALIZAR CONTRASEÑA

        $sql = "UPDATE usuario
                SET Contraseña = '$NuevaContraseñaHash'
                WHERE idUsuario = $this->idUsuario";


        $resultado =
            mysqli_query($conexion, $sql);


        if (!$resultado) {

            $errores["CambiarContraseña"] =
                "No se pudo cambiar la contraseña.";

            mysqli_close($conexion);

            return $errores;
        }


        // ACTUALIZAR OBJETO

        $this->Contraseña =
            $NuevaContraseñaHash;


        mysqli_close($conexion);


        return $this;
    }


    public static function validarCredenciales($email, $contraseña) {

        $errores = array();

        $email = trim($email);
        $contraseña = trim($contraseña);


        // VALIDACION DEL EMAIL

        if ($email == "") {
            $errores["email"] = "Debe ingresar un correo electrónico.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9._]+@gmail\.com$/", $email)) {
            $errores["email"] = "El correo debe tener el formato usuario@gmail.com.";
        }


        // VALIDACION DE LA CONTRASEÑA

        if ($contraseña == "") {
            $errores["Contraseña"] = "Debe ingresar una contraseña.";
        }
        elseif (strlen($contraseña) < 8) {
            $errores["Contraseña"] = "La contraseña debe tener al menos 8 caracteres.";
        }
        elseif (strlen($contraseña) > 20) {
            $errores["Contraseña"] = "La contraseña no puede superar los 20 caracteres.";
        }
        elseif (!preg_match("/^[a-zA-Z0-9]+$/", $contraseña)) {
            $errores["Contraseña"] = "La contraseña solo puede contener letras y números.";
        }


        // SI HAY ERRORES DE FORMATO

        if (count($errores) > 0) {
            return $errores;
        }


        // CONEXION A MYSQL

        require __DIR__ . "/../bd/conexion.php";

        $emailBD = mysqli_real_escape_string($conexion, $email);


        // BUSCAR USUARIO POR EMAIL

        $sql = "SELECT idUsuario, Contraseña
                FROM usuario
                WHERE email = '$emailBD'";

        $resultado = mysqli_query($conexion, $sql);


        // EL EMAIL NO EXISTE

        if (mysqli_num_rows($resultado) == 0) {

            $errores["credenciales"] = "Correo o contraseña incorrectos.";

            mysqli_close($conexion);

            return $errores;
        }


        $datos = mysqli_fetch_assoc($resultado);


        // COMPROBAR CONTRASEÑA

        if (!password_verify($contraseña, $datos["Contraseña"])) {

            $errores["credenciales"] = "Correo o contraseña incorrectos.";

            mysqli_close($conexion);

            return $errores;
        }


        // CREDENCIALES CORRECTAS

        $idUsuario = $datos["idUsuario"];

        mysqli_close($conexion);


        return Usuario::buscarPorId($idUsuario);
    }


    public function __get($atributo) {
        return $this->$atributo;
    }


    public function esPlanPro() {

    }


    public function esAdministrador() {

    }


    public function verId() {

        return $this->idUsuario;
    }
}

?>