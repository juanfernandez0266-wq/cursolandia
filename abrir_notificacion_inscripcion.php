
<?php

// INICIAMOS LA SESION DEL USUARIO

session_start();

if (!isset($_SESSION["idUsuario"])) {

    header("Location: iniciar_sesion.php");
    exit;
}


// PERMITIMOS SOLAMENTE SOLICITUDES POST

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);
    exit("Método no permitido.");
}


// VALIDAMOS EL TOKEN DE SEGURIDAD

$token = $_POST["token_avisos_inscripcion"] ?? "";

if (
    !is_string($token) ||
    empty($_SESSION["token_avisos_inscripcion"]) ||
    !hash_equals(
        $_SESSION["token_avisos_inscripcion"],
        $token
    )
) {

    http_response_code(403);
    exit("Solicitud no válida.");
}


// IDENTIFICAMOS LA NOTIFICACION

$idNotificacion = filter_input(
    INPUT_POST,
    "idNotificacion",
    FILTER_VALIDATE_INT
);

if (!$idNotificacion || $idNotificacion <= 0) {

    http_response_code(400);
    exit("Notificación no válida.");
}


require_once __DIR__ . "/clases/curso.php";


// MARCAMOS LA NOTIFICACION COMO LEIDA

try {

    $aviso = Curso::abrirAvisoInscripcionCurso(
        (int)$_SESSION["idUsuario"],
        (int)$idNotificacion
    );

    // SI ES UNA SOLICITUD, REDIRIGIMOS AL CREADOR

    
    // DIRIGIMOS AL USUARIO SEGUN EL TIPO DE NOTIFICACION

    if ((int)$aviso["Estado"] === 8) {

        // ABRIMOS LAS SOLICITUDES DEL CREADOR

        header(
            "Location: curso.php?idCurso=" .
            (int)$aviso["idCurso"] .
            "&seccion=solicitudes"
        );

    } elseif ((int)$aviso["Estado"] === 11) {

        // ABRIMOS EL MATERIAL DEL CURSO

        header(
            "Location: curso.php?idCurso=" .
            (int)$aviso["idCurso"] .
            "&seccion=material"
        );

    } else {

        // MOSTRAMOS EL RESULTADO DE LA INSCRIPCION

        header("Location: mis_cursos.php");
    }


    exit;

} catch (DomainException $e) {

    http_response_code(404);
    exit("Notificación no encontrada.");

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("No pudimos abrir la notificación.");
}
