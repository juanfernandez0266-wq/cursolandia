
<?php

session_start();

// COMPROBAMOS QUE EL CIERRE SE HAYA SOLICITADO DESDE EL MENU

$token = $_POST["token_cerrar_sesion"] ?? "";

if ($_SERVER["REQUEST_METHOD"] !== "POST" ||
    !is_string($token) ||
    !hash_equals($_SESSION["token_cerrar_sesion"] ?? "", $token)) {
    http_response_code(403);
    exit("No pudimos verificar el cierre de sesión.");
}

// ELIMINAMOS LOS DATOS DE LA SESION

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 3600,
        "path" => $parametros["path"],
        "domain" => $parametros["domain"],
        "secure" => $parametros["secure"],
        "httponly" => $parametros["httponly"],
        "samesite" => $parametros["samesite"] ?? "Lax"
    ]);
}

session_destroy();

// REGRESAMOS A LA PORTADA PUBLICA

header("Location: index.php");
exit;