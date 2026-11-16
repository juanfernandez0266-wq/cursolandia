
<?php

session_start();

if (!isset($_SESSION["idUsuario"])) {
    http_response_code(403);
    exit("Iniciá sesión para descargar archivos.");
}

$idArchivo = filter_input(INPUT_GET, "idArchivo", FILTER_VALIDATE_INT);

if (!$idArchivo || $idArchivo <= 0) {
    http_response_code(400);
    exit("Archivo no válido.");
}

require __DIR__ . "/bd/conexion.php";

mysqli_set_charset($conexion, "utf8mb4");

$idUsuario = (int)$_SESSION["idUsuario"];

// COMPROBAMOS QUE EL ARCHIVO PERTENEZCA A UN CURSO ACCESIBLE

$sql = "SELECT a.Ruta, a.NombreOriginal,
               c.idUsuario AS Creador,
               i.idInscripcion AS Inscripcion
        FROM materialarchivo AS a
        INNER JOIN material AS m ON m.idMaterial = a.idMaterial
        INNER JOIN curso AS c ON c.idCurso = m.idCurso
        LEFT JOIN inscripcion AS i ON i.idCurso = c.idCurso
            AND i.idUsuario = ? AND i.Estado = 'confirmada'
        WHERE a.idArchivo = ?";

$consulta = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $idArchivo);
mysqli_stmt_execute($consulta);

$archivo = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));

mysqli_stmt_close($consulta);
mysqli_close($conexion);

// IMPEDIMOS DESCARGAS DE USUARIOS SIN PERMISO

if (!$archivo || ((int)$archivo["Creador"] !== $idUsuario && $archivo["Inscripcion"] === null)) {
    http_response_code(403);
    exit("No tenés permiso para descargar este archivo.");
}

// COMPROBAMOS QUE LA RUTA SEA INTERNA Y QUE EL ARCHIVO EXISTA

$base = realpath(__DIR__ . "/uploads/materiales");
$ruta = realpath(__DIR__ . "/" . $archivo["Ruta"]);

if ($base === false || $ruta === false || !str_starts_with($ruta, $base . DIRECTORY_SEPARATOR) || !is_file($ruta)) {
    http_response_code(404);
    exit("El archivo no está disponible.");
}

// ENVIAMOS EL ARCHIVO COMO DESCARGA

$nombre = basename(str_replace("\\", "/", $archivo["NombreOriginal"]));
$nombre = str_replace(["\r", "\n", '"'], "", $nombre);

header("X-Content-Type-Options: nosniff");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"archivo\"; filename*=UTF-8''" . rawurlencode($nombre));
header("Content-Length: " . filesize($ruta));

readfile($ruta);
exit;
