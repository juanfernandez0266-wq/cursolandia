<?php

session_start();

require_once __DIR__ . "/clases/usuario.php";

if (!isset($_SESSION["idUsuario"])) {
    http_response_code(403);
    exit("Acceso denegado.");
}

$usuario = Usuario::buscarPorId((int)$_SESSION["idUsuario"]);

if (!$usuario || !$usuario->esAdministrador()) {
    http_response_code(403);
    exit("No tenés permisos para consultar comprobantes.");
}

// CONSULTAMOS EL COMPROBANTE SOLICITADO

$idSolicitud = filter_input(INPUT_GET, "idSolicitud", FILTER_VALIDATE_INT);

if (!$idSolicitud || $idSolicitud <= 0) {
    http_response_code(400);
    exit("Solicitud no válida.");
}

require __DIR__ . "/bd/conexion.php";

$consulta = mysqli_prepare($conexion, "SELECT Comprobante FROM solicitudplanpro WHERE idSolicitud = ?");
mysqli_stmt_bind_param($consulta, "i", $idSolicitud);
mysqli_stmt_execute($consulta);

$solicitud = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));

mysqli_stmt_close($consulta);
mysqli_close($conexion);

if (!$solicitud) {
    http_response_code(404);
    exit("No encontramos el comprobante.");
}

// VALIDAMOS EL NOMBRE INTERNO Y CONSULTAMOS EL ARCHIVO PROTEGIDO

$nombre = (string)$solicitud["Comprobante"];

if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|pdf)$/i', $nombre)) {
    http_response_code(404);
    exit("El comprobante no es válido.");
}

$raizPublica = realpath($_SERVER["DOCUMENT_ROOT"]);
$carpeta = dirname($raizPublica) . "/cursolandia_comprobantes";
$ruta = $carpeta . "/" . $nombre;

if (!is_file($ruta) || !is_readable($ruta)) {
    http_response_code(404);
    exit("El archivo no está disponible en el servidor.");
}

// COMPROBAMOS EL TIPO REAL ANTES DE MOSTRARLO

$tipos = [
    "image/jpeg",
    "image/png",
    "application/pdf"
];

$tipo = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);

if (!in_array($tipo, $tipos, true)) {
    http_response_code(415);
    exit("El tipo de archivo no está permitido.");
}

header("Content-Type: " . $tipo);
header('Content-Disposition: inline; filename="comprobante.' . pathinfo($nombre, PATHINFO_EXTENSION) . '"');
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, no-store");
header("Content-Length: " . filesize($ruta));

readfile($ruta);
exit;