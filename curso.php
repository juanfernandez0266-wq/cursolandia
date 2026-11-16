
<?php

session_start();

require_once __DIR__ . "/clases/curso.php";

// COMPROBAMOS QUE EL USUARIO HAYA INICIADO SESION

if (!isset($_SESSION["idUsuario"])) {
    header("Location: iniciar_sesion.php");
    exit;
}

$idUsuario = (int)$_SESSION["idUsuario"];
$idCurso = filter_input(INPUT_GET, "idCurso", FILTER_VALIDATE_INT);
$idCurso = $idCurso && $idCurso > 0 ? (int)$idCurso : 0;

// IDENTIFICAMOS LA CONVERSACION SELECCIONADA

$idPregunta = filter_input(INPUT_GET, "idPregunta", FILTER_VALIDATE_INT);
$idPregunta = $idPregunta && $idPregunta > 0 ? (int)$idPregunta : 0;

$seccion = $_GET["seccion"] ?? "principal";
$seccionesPermitidas = ["principal", "material", "personas", "solicitudes"];

if (!is_string($seccion) || !in_array($seccion, $seccionesPermitidas, true)) {
    $seccion = "principal";
}

$paginaActual = basename($_SERVER["PHP_SELF"]);

$curso = null;
$esCreador = false;
$accesoPermitido = false;
$error = "";

// PREPARAMOS LAS PREGUNTAS DEL CURSO

$preguntas = [];
$preguntasDestacadas = [];
$errorPregunta = "";
$preguntaCreada = false;

$conversacion = null;
$errorRespuesta = "";
$respuestaCreada = false;

// PREPARAMOS LAS ACCIONES DE ADMINISTRACION DEL FORO

$errorForo = "";
$avisoForo = "";

if (empty($_SESSION["token_foro"])) {
    $_SESSION["token_foro"] = bin2hex(random_bytes(32));
}

if (empty($_SESSION["token_respuesta"])) {
    $_SESSION["token_respuesta"] = bin2hex(random_bytes(32));
}


if (empty($_SESSION["token_pregunta"])) {
    $_SESSION["token_pregunta"] = bin2hex(random_bytes(32));
}

// DEFINIMOS LAS RUTAS DE NUESTROS ICONOS SVG

$iconos = [
    "principal" => "imagenes/curso_interno/principal.svg",
    "material" => "imagenes/curso_interno/material.svg",
    "personas" => "imagenes/curso_interno/personas.svg",
    "pregunta" => "imagenes/curso_interno/pregunta.svg",
    "respuesta" => "imagenes/curso_interno/respuesta.svg",
    "destacada" => "imagenes/curso_interno/destacada.svg",
    "fecha" => "imagenes/curso_interno/fecha.svg",
    "archivo" => "imagenes/curso_interno/archivo.svg",
    "agregar" => "imagenes/curso_interno/agregar.svg",
    "editar" => "imagenes/curso_interno/editar.svg",
    "eliminar" => "imagenes/curso_interno/eliminar.svg",
    "aceptada" => "imagenes/curso_interno/aceptada.svg",
    "persona" => "imagenes/curso_interno/persona.svg",
    "subir" => "imagenes/curso_interno/subir.svg",
    "enlace" => "imagenes/curso_interno/enlace.svg",
];

function escaparCurso($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// MOSTRAMOS EL TIEMPO TRANSCURRIDO DESDE LA PUBLICACION

function tiempoPublicacionCurso($fecha) {

    $zona = new DateTimeZone("America/Argentina/San_Luis");
    $publicacion = new DateTimeImmutable($fecha, $zona);
    $ahora = new DateTimeImmutable("now", $zona);

    $segundos = max(0, $ahora->getTimestamp() - $publicacion->getTimestamp());

    if ($segundos < 60) return "Hace un momento";
    if ($segundos < 3600) return "Hace " . floor($segundos / 60) . " minutos";
    if ($segundos < 86400 && $publicacion->format("Y-m-d") === $ahora->format("Y-m-d")) {
        return "Hace " . floor($segundos / 3600) . " horas";
    }

    if ($publicacion->format("Y-m-d") === $ahora->modify("-1 day")->format("Y-m-d")) {
        return "Ayer";
    }

    if ($segundos < 604800) return "Hace " . floor($segundos / 86400) . " días";

    return $publicacion->format("d/m/Y");
}

// MOSTRAMOS EL ICONO SOLAMENTE SI EXISTE EL ARCHIVO

function mostrarIconoCurso($nombre, $iconos) {

    $ruta = $iconos[$nombre] ?? "";

    if ($ruta === "" || !is_file(__DIR__ . "/" . $ruta)) return "";

    return '<img src="' . escaparCurso($ruta) . '" class="curso-icono" alt="" aria-hidden="true">';
}

// OBTENEMOS EL CURSO Y COMPROBAMOS LOS PERMISOS

if ($idCurso <= 0) {

    http_response_code(400);
    $error = "No se indicó un curso válido.";

} else {

    try {

        $curso = Curso::obtenerDetalleCurso($idCurso);

        if (!$curso) {

            http_response_code(404);
            $error = "No encontramos el curso solicitado.";

        } else {

            $esCreador = (int)$curso["idUsuario"] === $idUsuario;

            if ($esCreador) {
                $accesoPermitido = true;
            } else {
                $estadoInscripcion = Curso::obtenerEstadoInscripcion($idUsuario, $idCurso);
                $accesoPermitido = $estadoInscripcion === "confirmada";
            }

            if (!$accesoPermitido) {
                http_response_code(403);
                $error = "Necesitás una inscripción confirmada para acceder al contenido de este curso.";
            }
        }

    } catch (Throwable $e) {

        error_log($e->getMessage());
        http_response_code(500);
        $error = "No pudimos cargar el curso. Intentá nuevamente.";
    }
}




// PROCESAMOS LAS PREGUNTAS SI EL USUARIO PUEDE ACCEDER

if ($accesoPermitido) {

    // PROCESAMOS LAS ACCIONES DEL FORO

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accionForo"])) {

        try {

            $token = $_POST["token_foro"] ?? "";
            $accion = $_POST["accionForo"];

            if ($idPregunta <= 0 || !is_string($token) || !hash_equals($_SESSION["token_foro"], $token)) {
                throw new DomainException("No pudimos verificar la operación. Recargá la página.");
            }

            if (!is_string($accion)) {
                throw new DomainException("La acción solicitada no es válida.");
            }

            $idRespuestaAccion = filter_input(INPUT_POST, "idRespuesta", FILTER_VALIDATE_INT);
            $volverAlListado = false;

            switch ($accion) {

                case "editar":

                    $texto = $_POST["contenidoEditado"] ?? "";

                    if (!$idRespuestaAccion || !is_string($texto)) {
                        throw new DomainException("No pudimos identificar la respuesta que querés editar.");
                    }

                    Curso::editarUltimaRespuestaCurso($idCurso, $idPregunta, $idUsuario, $idRespuestaAccion, $texto);
                    $mensaje = "Tu respuesta fue actualizada correctamente.";
                    break;

                case "destacar":

                    $valor = $_POST["valorDestacada"] ?? null;

                    if (!in_array($valor, ["0", "1"], true)) {
                        throw new DomainException("El estado del destacado no es válido.");
                    }

                    Curso::cambiarDestacadoPreguntaCurso($idCurso, $idPregunta, $idUsuario, (int)$valor);
                    $mensaje = $valor === "1" ? "La pregunta fue destacada." : "Se quitó el destacado de la pregunta.";
                    break;

                case "eliminar":

                    Curso::eliminarPreguntaCurso($idCurso, $idPregunta, $idUsuario);
                    $mensaje = "La pregunta y sus respuestas fueron eliminadas.";
                    $volverAlListado = true;
                    break;

                case "aceptar":

                    if (!$idRespuestaAccion) {
                        throw new DomainException("No pudimos identificar la respuesta seleccionada.");
                    }

                    Curso::aceptarRespuestaCurso($idCurso, $idPregunta, $idUsuario, $idRespuestaAccion);
                    $mensaje = "La respuesta fue aceptada y la conversación quedó cerrada.";
                    break;

                default:
                    throw new DomainException("La acción solicitada no existe.");
            }

            $_SESSION["aviso_foro"] = $mensaje;
            $_SESSION["token_foro"] = bin2hex(random_bytes(32));

            $destino = "curso.php?idCurso=" . $idCurso . "&seccion=principal";

            if (!$volverAlListado) {
                $destino .= "&idPregunta=" . $idPregunta;
            }

            header("Location: " . $destino);
            exit;

        } catch (DomainException $e) {
            $errorForo = $e->getMessage();

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errorForo = "No pudimos completar la operación.";
        }
    }

    // PROCESAMOS LAS RESPUESTAS DE LA CONVERSACION

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["publicarRespuesta"])) {

        try {

            $token = $_POST["token_respuesta"] ?? "";
            $contenido = $_POST["contenidoRespuesta"] ?? "";

            if ($idPregunta <= 0 || !is_string($token) || !hash_equals($_SESSION["token_respuesta"], $token)) {
                throw new DomainException("No se pudo verificar la respuesta.");
            }

            if (!is_string($contenido)) {
                throw new DomainException("El contenido de la respuesta no es válido.");
            }

            Curso::responderPreguntaCurso($idCurso, $idPregunta, $idUsuario, $contenido);

            $_SESSION["respuesta_creada"] = $idPregunta;
            $_SESSION["token_respuesta"] = bin2hex(random_bytes(32));

            header("Location: curso.php?idCurso=" . $idCurso . "&seccion=principal&idPregunta=" . $idPregunta);
            exit;

        } catch (DomainException $e) {
            $errorRespuesta = $e->getMessage();

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errorRespuesta = "No pudimos publicar la respuesta.";
        }
    }

    // PROCESAMOS LA CREACION DE PREGUNTAS

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crearPregunta"])) {

        try {

            $token = $_POST["token_pregunta"] ?? "";

            if (!is_string($token) || !hash_equals($_SESSION["token_pregunta"], $token)) {
                throw new DomainException("No pudimos verificar la solicitud. Recargá la página.");
            }

            if ($esCreador) {
                throw new DomainException("Las preguntas las inician los alumnos. Como creador, podrás responderlas.");
            }

            $titulo = $_POST["titulo"] ?? "";
            $contenido = $_POST["contenido"] ?? "";

            if (!is_string($titulo) || !is_string($contenido)) {
                throw new DomainException("Los datos de la pregunta no son válidos.");
            }

            // IDENTIFICAMOS SI LA PREGUNTA SE RELACIONA CON UN MATERIAL

            $idMaterialPregunta = null;

            if (isset($_POST["idMaterial"])) {

                $idMaterialPregunta = filter_input(INPUT_POST, "idMaterial", FILTER_VALIDATE_INT);

                if (!is_int($idMaterialPregunta) || $idMaterialPregunta <= 0) {
                    throw new DomainException("El material seleccionado no es válido.");
                }
            }

            Curso::crearPreguntaCurso($idCurso, $idUsuario, $titulo, $contenido, $idMaterialPregunta);

            $_SESSION["pregunta_creada"] = $idCurso;
            $_SESSION["token_pregunta"] = bin2hex(random_bytes(32));

            header("Location: curso.php?idCurso=" . $idCurso . "&seccion=principal");
            exit;

        } catch (DomainException $e) {
            $errorPregunta = $e->getMessage();

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errorPregunta = "No pudimos publicar la pregunta.";
        }
    }


    // OBTENEMOS LAS PUBLICACIONES Y LAS PREGUNTAS DESTACADAS

    try {

        $preguntas = Curso::obtenerPreguntasCurso($idCurso);

        $preguntasDestacadas = array_values(array_filter($preguntas, function ($pregunta) {
            return (int)$pregunta["Destacada"] === 1;
        }));

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorPregunta = "No pudimos cargar las preguntas del curso.";
    }

    // MOSTRAMOS LA CONFIRMACION DESPUES DE PUBLICAR

    $preguntaCreada = (int)($_SESSION["pregunta_creada"] ?? 0) === $idCurso;

    if ($preguntaCreada) unset($_SESSION["pregunta_creada"]);


    // OBTENEMOS LA CONVERSACION SELECCIONADA

    if ($idPregunta > 0) {

        try {

            $conversacion = Curso::obtenerConversacionCurso($idCurso, $idPregunta);

            if (!$conversacion) {
                $errorRespuesta = "No encontramos esta conversación.";
            }

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errorRespuesta = "No pudimos cargar la conversación.";
        }

        $respuestaCreada = (int)($_SESSION["respuesta_creada"] ?? 0) === $idPregunta;

        if ($respuestaCreada) unset($_SESSION["respuesta_creada"]);
    }

}

// RECUPERAMOS EL RESULTADO DE LA ULTIMA OPERACION

if ($accesoPermitido && isset($_SESSION["aviso_foro"])) {
    $avisoForo = (string)$_SESSION["aviso_foro"];
    unset($_SESSION["aviso_foro"]);
}



// OBTENEMOS LOS PARTICIPANTES PARA LA SECCION PERSONAS

$alumnosInscritos = [];
$errorPersonas = "";

if ($accesoPermitido && $seccion === "personas") {

    try {
        $alumnosInscritos = Curso::obtenerAlumnosInscritosCurso($idCurso);

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorPersonas = "No pudimos cargar los alumnos del curso.";
    }
}


// PREPARAMOS LOS MATERIALES DEL CURSO

$materialesCurso = [];
$contenidoMaterial = null;
$errorMaterial = "";
$materialPublicado = false;

$idMaterialSeleccionado = filter_input(INPUT_GET, "idMaterial", FILTER_VALIDATE_INT);
$idMaterialSeleccionado = $idMaterialSeleccionado && $idMaterialSeleccionado > 0 ? (int)$idMaterialSeleccionado : 0;

if (empty($_SESSION["token_material"])) {
    $_SESSION["token_material"] = bin2hex(random_bytes(32));
}

// PROCESAMOS LA PUBLICACION DE MATERIAL

if ($accesoPermitido && $esCreador && $seccion === "material" &&
    $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["publicarMaterial"])) {

    try {

        $token = $_POST["token_material"] ?? "";

        if (!is_string($token) || !hash_equals($_SESSION["token_material"], $token)) {
            throw new DomainException("No pudimos verificar la publicación. Recargá la página.");
        }

        $titulo = $_POST["tituloMaterial"] ?? "";
        $descripcion = $_POST["descripcionMaterial"] ?? "";
        $enlaces = $_POST["enlacesMaterial"] ?? [];

        if (!is_string($titulo) || !is_string($descripcion) || !is_array($enlaces)) {
            throw new DomainException("Los datos del material no son válidos.");
        }

        Curso::publicarMaterialCurso(
            $idCurso,
            $idUsuario,
            $titulo,
            $descripcion,
            $_FILES["archivosMaterial"] ?? [],
            $enlaces
        );

        $_SESSION["material_publicado"] = $idCurso;
        $_SESSION["token_material"] = bin2hex(random_bytes(32));

        header("Location: curso.php?idCurso=" . $idCurso . "&seccion=material");
        exit;

    } catch (DomainException $e) {
        $errorMaterial = $e->getMessage();

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorMaterial = "No pudimos publicar el material. Revisá los datos e intentá nuevamente.";
    }
}

// OBTENEMOS LOS MATERIALES PUBLICADOS

if ($accesoPermitido && $seccion === "material") {

    try {

        $materialesCurso = Curso::obtenerMaterialesCurso($idCurso);

        if ($idMaterialSeleccionado > 0) {
            $contenidoMaterial = Curso::obtenerContenidoMaterialCurso($idCurso, $idMaterialSeleccionado);

            if (!$contenidoMaterial) {
                $errorMaterial = "No encontramos el material solicitado.";
            }
        }

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorMaterial = "No pudimos cargar los materiales del curso.";
    }

    $materialPublicado = (int)($_SESSION["material_publicado"] ?? 0) === $idCurso;

    if ($materialPublicado) unset($_SESSION["material_publicado"]);
}

// SI FALLA LA PUBLICACION, MANTENEMOS ABIERTO EL FORMULARIO

$editorMaterialAbierto = $esCreador &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["publicarMaterial"]) &&
    $errorMaterial !== "";




// UNIMOS LAS PREGUNTAS Y LOS MATERIALES EN PRINCIPAL

$actividadCurso = [];

if ($accesoPermitido && $seccion === "principal") {

    foreach ($preguntas as $pregunta) {

        $actividadCurso[] = [
            "Tipo" => "pregunta",
            "Fecha" => $pregunta["FechaCreacion"],
            "Id" => (int)$pregunta["idPregunta"],
            "Registro" => $pregunta
        ];
    }

    try {

        $materialesTablon = Curso::obtenerMaterialesCurso($idCurso);

        foreach ($materialesTablon as $material) {

            $actividadCurso[] = [
                "Tipo" => "material",
                "Fecha" => $material["FechaPublicacion"],
                "Id" => (int)$material["idMaterial"],
                "Registro" => $material
            ];
        }

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorPregunta = "No pudimos cargar las novedades de materiales.";
    }

    // ORDENAMOS TODAS LAS PUBLICACIONES POR FECHA

    usort($actividadCurso, function ($a, $b) {

        $comparacion = strcmp($b["Fecha"], $a["Fecha"]);

        if ($comparacion !== 0) return $comparacion;

        return $b["Id"] <=> $a["Id"];
    });
}



// RECUPERAMOS EL MATERIAL DESDE EL QUE SE CREA LA PREGUNTA

$materialRelacionado = null;

if ($accesoPermitido && !$esCreador && $seccion === "principal" && $idMaterialSeleccionado > 0) {

    try {

        $materialRelacionado = Curso::obtenerContenidoMaterialCurso($idCurso, $idMaterialSeleccionado);

        if (!$materialRelacionado) {
            $errorPregunta = "No encontramos el material seleccionado.";
        }

    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errorPregunta = "No pudimos cargar el material seleccionado.";
    }
}

// PREPARAMOS EL CONTROL DE SOLICITUDES

$errorSolicitudes = "";
$mensajeSolicitudes = "";
$solicitudesPendientes = [];
$cantidadSolicitudesPendientes = 0;

if (empty($_SESSION["token_solicitudes"])) {
    $_SESSION["token_solicitudes"] = bin2hex(random_bytes(32));
}


// LIMITAMOS LA SECCION DE SOLICITUDES AL CREADOR

if ($accesoPermitido && $seccion === "solicitudes" && !$esCreador) {

    http_response_code(403);
    exit("Solamente el creador puede ver las solicitudes de inscripción.");
}


// PROCESAMOS LA APROBACION O EL RECHAZO

if (
    $accesoPermitido &&
    $esCreador &&
    $seccion === "solicitudes" &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accionSolicitud"])
) {

    try {

        $token = $_POST["token_solicitudes"] ?? "";

        if (
            !is_string($token) ||
            !hash_equals($_SESSION["token_solicitudes"], $token)
        ) {
            throw new DomainException(
                "La sesión del formulario venció. Actualizá la página."
            );
        }

        $idInscripcionSolicitud = filter_input(
            INPUT_POST,
            "idInscripcion",
            FILTER_VALIDATE_INT
        );

        $accionSolicitud = $_POST["accionSolicitud"] ?? "";
        $motivoSolicitud = $_POST["motivoRechazo"] ?? "";

        if (
            !$idInscripcionSolicitud ||
            !is_string($accionSolicitud) ||
            !is_string($motivoSolicitud)
        ) {
            throw new DomainException(
                "Los datos de la solicitud no son válidos."
            );
        }

        $resultado = Curso::resolverSolicitudCurso(
            $idCurso,
            $idUsuario,
            (int)$idInscripcionSolicitud,
            $accionSolicitud,
            $motivoSolicitud
        );

        $_SESSION["mensaje_solicitudes"] =
            $resultado === "confirmada"
            ? "La solicitud fue aprobada. El alumno ya puede ingresar al curso."
            : "La solicitud fue rechazada. El alumno recibirá la notificación.";

        $_SESSION["token_solicitudes"] = bin2hex(random_bytes(32));

        header(
            "Location: curso.php?idCurso=" .
            $idCurso .
            "&seccion=solicitudes"
        );

        exit;

    } catch (DomainException $e) {

        $errorSolicitudes = $e->getMessage();

    } catch (Throwable $e) {

        error_log($e->getMessage());

        $errorSolicitudes =
            "No pudimos procesar la solicitud. Revisá los datos e intentá nuevamente.";
    }
}


// OBTENEMOS LA CANTIDAD DE SOLICITUDES PENDIENTES

if ($accesoPermitido && $esCreador) {

    try {

        $cantidadSolicitudesPendientes =
            Curso::contarSolicitudesPendientesCurso(
                $idCurso,
                $idUsuario
            );

        if ($seccion === "solicitudes") {

            $solicitudesPendientes =
                Curso::obtenerSolicitudesPendientesCurso(
                    $idCurso,
                    $idUsuario
                );
        }

    } catch (Throwable $e) {

        error_log($e->getMessage());

        $errorSolicitudes =
            "No pudimos consultar las solicitudes pendientes.";
    }
}


// MOSTRAMOS EL MENSAJE DESPUES DE REDIRIGIR

if (
    $accesoPermitido &&
    $esCreador &&
    $seccion === "solicitudes" &&
    isset($_SESSION["mensaje_solicitudes"])
) {

    $mensajeSolicitudes = (string)$_SESSION["mensaje_solicitudes"];

    unset($_SESSION["mensaje_solicitudes"]);
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $accesoPermitido ? escaparCurso($curso["Titulo"]) : "Acceso al curso"; ?> - Cursolandia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menu_lateral.css">
    <link rel="stylesheet" href="css/notificaciones_rapidas.css">
    <link rel="stylesheet" href="css/curso_interno.css?v=11">
    <link rel="stylesheet" href="css/solicitudes_curso.css?v=2">
</head>
<body>

    <?php include __DIR__ . "/componentes/menu_lateral.php"; ?>
    <?php include __DIR__ . "/componentes/notificaciones_rapidas.php"; ?>

    <main class="contenido-con-menu curso-interno-pagina">
        <div class="curso-interno-contenido">
            <div class="curso-interno-panel">

                <?php if (!$accesoPermitido): ?>

                    <div class="curso-interno-error">
                        <h1>Acceso al curso</h1>
                        <p><?php echo escaparCurso($error); ?></p>
                        <a href="<?php echo $curso ? 'detalle_curso.php?idCurso=' . $idCurso : 'inicio.php'; ?>" class="btn btn-primary">Volver</a>
                    </div>

                <?php else: ?>

                    <!-- MOSTRAMOS LA CABECERA DEL CURSO -->

                    <header class="curso-interno-encabezado">
                        <a href="mis_cursos.php" class="curso-interno-volver">← Volver a Mis cursos</a>

                        <div class="curso-interno-titulo">
                            <div>
                                <h1><?php echo escaparCurso($curso["Titulo"]); ?></h1>
                                <p>Creado por <?php echo escaparCurso($curso["NombreCreador"]); ?> · Contenido y actividad del curso</p>
                            </div>
                            <span class="curso-interno-rol"><?php echo $esCreador ? "Creador del curso" : "Alumno inscrito"; ?></span>
                        </div>
                    </header>

                    <!-- NAVEGAMOS ENTRE LAS SECCIONES DEL CURSO -->

                    <nav class="curso-interno-navegacion" aria-label="Secciones del curso">

                        <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal" class="curso-interno-tab <?php echo $seccion === "principal" ? "activo" : ""; ?>" <?php echo $seccion === "principal" ? 'aria-current="page"' : ""; ?>>
                            <?php echo mostrarIconoCurso("principal", $iconos); ?> Principal
                        </a>

                        <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=material" class="curso-interno-tab <?php echo $seccion === "material" ? "activo" : ""; ?>" <?php echo $seccion === "material" ? 'aria-current="page"' : ""; ?>>
                            <?php echo mostrarIconoCurso("material", $iconos); ?> Material
                        </a>

                        <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=personas" class="curso-interno-tab <?php echo $seccion === "personas" ? "activo" : ""; ?>" <?php echo $seccion === "personas" ? 'aria-current="page"' : ""; ?>>
                            <?php echo mostrarIconoCurso("personas", $iconos); ?> Personas
                        </a>

                        <!-- MOSTRAMOS SOLICITUDES SOLAMENTE AL CREADOR -->

                        <?php if ($esCreador): ?>

                            <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=solicitudes"
                            class="curso-interno-tab <?php echo $seccion === "solicitudes" ? "activo" : ""; ?>"
                            <?php echo $seccion === "solicitudes" ? 'aria-current="page"' : ""; ?>>

                                <?php echo mostrarIconoCurso("personas", $iconos); ?>
                                Solicitudes

                                <?php if ($cantidadSolicitudesPendientes > 0): ?>
                                    <span class="curso-solicitudes-numero">
                                        <?php echo $cantidadSolicitudesPendientes; ?>
                                    </span>
                                <?php endif; ?>

                            </a>

                        <?php endif; ?>

                    </nav>
                    
                    <!-- SECCION PRINCIPAL -->

                    <?php if ($seccion === "principal"): ?>

                        <section class="curso-interno-seccion">

                            <div class="curso-interno-introduccion">
                                <h2>Principal</h2>
                                <p>Consultá las novedades y participá en las conversaciones del curso.</p>
                            </div>

                            <?php if ($preguntaCreada): ?>
                                <div class="alert alert-success mt-3">Tu pregunta fue publicada correctamente.</div>
                            <?php endif; ?>

                            <?php if ($errorPregunta !== ""): ?>
                                <div class="alert alert-danger mt-3"><?php echo escaparCurso($errorPregunta); ?></div>
                            <?php endif; ?>

                            <?php if ($avisoForo !== ""): ?>
                                <div class="alert alert-success mt-3"><?php echo escaparCurso($avisoForo); ?></div>
                            <?php endif; ?>

                            <?php if ($errorForo !== ""): ?>
                                <div class="alert alert-danger mt-3"><?php echo escaparCurso($errorForo); ?></div>
                            <?php endif; ?>

                            <!-- ORGANIZAMOS LAS PUBLICACIONES Y LAS PREGUNTAS -->
                            <?php if ($idPregunta > 0): ?>

                            <!-- MOSTRAMOS LA CONVERSACION SELECCIONADA -->

                            <div class="curso-conversacion">

                                <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal" class="curso-interno-volver">← Volver a Principal</a>

                                <?php if ($errorRespuesta !== ""): ?>
                                    <div class="alert alert-danger"><?php echo escaparCurso($errorRespuesta); ?></div>
                                <?php endif; ?>

                                <?php if ($respuestaCreada): ?>
                                    <div class="alert alert-success">Tu respuesta fue publicada correctamente.</div>
                                <?php endif; ?>

                                <?php if ($conversacion): ?>

                                    <article class="curso-pregunta-tarjeta">
                                        <div class="curso-pregunta-meta">
                                            <span><?php echo escaparCurso($conversacion["pregunta"]["Autor"]); ?> creó una pregunta</span>
                                            <span><?php echo escaparCurso(tiempoPublicacionCurso($conversacion["pregunta"]["FechaCreacion"])); ?></span>
                                        </div>

                                        <h3><?php echo escaparCurso($conversacion["pregunta"]["Titulo"]); ?></h3>
                                        <p><?php echo nl2br(escaparCurso($conversacion["pregunta"]["Contenido"])); ?></p>
                                        <?php if (!empty($conversacion["pregunta"]["TituloMaterial"])): ?>
                                            <div class="curso-pregunta-sobre">Sobre: <?php echo escaparCurso($conversacion["pregunta"]["TituloMaterial"]); ?></div>
                                        <?php endif; ?>    
                                        </article>

                                        <!-- MOSTRAMOS LAS HERRAMIENTAS DEL CREADOR -->

                                        <?php if ($esCreador): ?>

                                            <div class="curso-conversacion-administracion">

                                                <form method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo $idPregunta; ?>">
                                                    <input type="hidden" name="token_foro" value="<?php echo escaparCurso($_SESSION["token_foro"]); ?>">
                                                    <input type="hidden" name="valorDestacada" value="<?php echo (int)$conversacion["pregunta"]["Destacada"] === 1 ? '0' : '1'; ?>">
                                                    <button type="submit" name="accionForo" value="destacar" class="curso-accion">
                                                        <?php echo mostrarIconoCurso("destacada", $iconos); ?>
                                                        <?php echo (int)$conversacion["pregunta"]["Destacada"] === 1 ? "Quitar destacado" : "Destacar pregunta"; ?>
                                                    </button>
                                                </form>

                                                <form method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo $idPregunta; ?>" onsubmit="return confirm('¿Eliminar esta pregunta y todas sus respuestas? Esta acción no se puede deshacer.');">
                                                    <input type="hidden" name="token_foro" value="<?php echo escaparCurso($_SESSION["token_foro"]); ?>">
                                                    <button type="submit" name="accionForo" value="eliminar" class="curso-accion curso-accion-eliminar">
                                                        Eliminar pregunta
                                                    </button>
                                                </form>

                                            </div>

                                        <?php endif; ?>

                                        <?php if ($conversacion["pregunta"]["Estado"] === "cerrada"): ?>
                                            <div class="curso-conversacion-cerrada">Esta pregunta está resuelta. Se aceptó una respuesta y la conversación quedó cerrada.</div>
                                        <?php endif; ?>

                                        <h3 class="curso-conversacion-subtitulo"><?php echo count($conversacion["respuestas"]); ?> <?php echo count($conversacion["respuestas"]) === 1 ? "respuesta" : "respuestas"; ?></h3>

                                    <!-- IDENTIFICAMOS NUESTRA ULTIMA RESPUESTA -->

                                    <?php
                                    $ultimaRespuestaPropia = 0;

                                    foreach ($conversacion["respuestas"] as $fila) {
                                        if ((int)$fila["idUsuario"] === $idUsuario) {
                                            $ultimaRespuestaPropia = (int)$fila["idRespuesta"];
                                        }
                                    }
                                    ?>

                                    <!-- MOSTRAMOS LAS RESPUESTAS DE LA CONVERSACION -->

                                    <?php foreach ($conversacion["respuestas"] as $respuesta): ?>

                                        <article class="curso-respuesta-tarjeta <?php echo (int)$respuesta["Aceptada"] === 1 ? 'curso-respuesta-aceptada' : ''; ?>">

                                            <div class="curso-pregunta-meta">
                                                <strong><?php echo escaparCurso($respuesta["Autor"]); ?></strong>
                                                <span><?php echo escaparCurso(tiempoPublicacionCurso($respuesta["FechaCreacion"])); ?></span>
                                            </div>

                                            <p><?php echo nl2br(escaparCurso($respuesta["Contenido"])); ?></p>

                                            <?php if (!empty($respuesta["FechaEdicion"])): ?>
                                                <span class="curso-respuesta-editada">Respuesta editada</span>
                                            <?php endif; ?>

                                            <!-- DESTACAMOS LA RESPUESTA ACEPTADA -->

                                            <?php if ((int)$respuesta["Aceptada"] === 1): ?>
                                                <div class="curso-respuesta-aceptada-etiqueta">
                                                    <?php echo mostrarIconoCurso("aceptada", $iconos); ?>
                                                    Respuesta aceptada
                                                </div>
                                            <?php endif; ?>

                                            <!-- PERMITIMOS EDITAR NUESTRA ULTIMA RESPUESTA -->

                                            <?php if ($conversacion["pregunta"]["Estado"] === "abierta" && (int)$respuesta["idRespuesta"] === $ultimaRespuestaPropia): ?>

                                                <details class="curso-respuesta-editar">

                                                    <summary>Editar mi respuesta</summary>

                                                    <form method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo $idPregunta; ?>" class="curso-pregunta-form">

                                                        <input type="hidden" name="token_foro" value="<?php echo escaparCurso($_SESSION["token_foro"]); ?>">
                                                        <input type="hidden" name="idRespuesta" value="<?php echo (int)$respuesta["idRespuesta"]; ?>">

                                                        <label for="editarRespuesta<?php echo (int)$respuesta["idRespuesta"]; ?>">Modificar respuesta</label>
                                                        <textarea id="editarRespuesta<?php echo (int)$respuesta["idRespuesta"]; ?>" name="contenidoEditado" rows="4" maxlength="5000" required><?php echo escaparCurso($respuesta["Contenido"]); ?></textarea>

                                                        <button type="submit" name="accionForo" value="editar" class="curso-pregunta-boton">Guardar cambios</button>

                                                    </form>

                                                </details>

                                            <?php endif; ?>

                                            <!-- EL CREADOR O EL AUTOR DE LA PREGUNTA PUEDEN ACEPTAR -->

                                            <?php if ($conversacion["pregunta"]["Estado"] === "abierta" && ($esCreador || (int)$conversacion["pregunta"]["idUsuario"] === $idUsuario)): ?>

                                                <form method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo $idPregunta; ?>" class="curso-aceptar-form">

                                                    <input type="hidden" name="token_foro" value="<?php echo escaparCurso($_SESSION["token_foro"]); ?>">
                                                    <input type="hidden" name="idRespuesta" value="<?php echo (int)$respuesta["idRespuesta"]; ?>">

                                                    <button type="submit" name="accionForo" value="aceptar" class="curso-accion curso-accion-aceptar" onclick="return confirm('¿Aceptar esta respuesta y cerrar la conversación?');">
                                                        <?php echo mostrarIconoCurso("aceptada", $iconos); ?>
                                                        Aceptar respuesta
                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        </article>

                                    <?php endforeach; ?>

                                    <?php if ($conversacion["pregunta"]["Estado"] === "abierta"): ?>

                                        <form method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo $idPregunta; ?>" class="curso-pregunta-form curso-respuesta-form">

                                            <input type="hidden" name="token_respuesta" value="<?php echo escaparCurso($_SESSION["token_respuesta"]); ?>">

                                            <label for="contenidoRespuesta">Escribí tu respuesta</label>
                                            <textarea id="contenidoRespuesta" name="contenidoRespuesta" rows="5" maxlength="5000" required placeholder="Compartí tu explicación o respondé la pregunta."><?php echo escaparCurso(is_string($_POST["contenidoRespuesta"] ?? "") ? ($_POST["contenidoRespuesta"] ?? "") : ""); ?></textarea>

                                            <button type="submit" name="publicarRespuesta" value="1" class="curso-pregunta-boton">Publicar respuesta</button>

                                        </form>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </div>

                        <?php else: ?>
                            <div class="curso-interno-distribucion">

                                <div class="curso-interno-publicaciones">

                                    <!-- MOSTRAMOS LAS PREGUNTAS DESTACADAS -->

                                    <?php if (!empty($preguntasDestacadas)): ?>

                                        <div class="curso-interno-grupo">
                                            <h3><?php echo mostrarIconoCurso("destacada", $iconos); ?> Preguntas destacadas</h3>

                                            <?php foreach ($preguntasDestacadas as $pregunta): ?>
                                                <article class="curso-pregunta-tarjeta curso-pregunta-destacada">
                                                    <div class="curso-pregunta-meta">
                                                        <span><?php echo escaparCurso($pregunta["Autor"]); ?></span>
                                                        <time title="<?php echo escaparCurso(date("d/m/Y H:i", strtotime($pregunta["FechaCreacion"]))); ?>"><?php echo escaparCurso(tiempoPublicacionCurso($pregunta["FechaCreacion"])); ?></time>
                                                    </div>
                                                    <h4><a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo (int)$pregunta["idPregunta"]; ?>" class="curso-pregunta-enlace"><?php echo escaparCurso($pregunta["Titulo"]); ?></a></h4>
                                                    <?php if (!empty($pregunta["TituloMaterial"])): ?>
                                                        <div class="curso-pregunta-sobre">Sobre: <?php echo escaparCurso($pregunta["TituloMaterial"]); ?></div>
                                                    <?php endif; ?>
                                                    <div class="curso-pregunta-pie">
                                                        <span><?php echo (int)$pregunta["TotalRespuestas"]; ?> respuestas</span>
                                                        <?php if ($pregunta["Estado"] === "cerrada"): ?><span class="curso-pregunta-resuelta">Resuelta</span><?php endif; ?>
                                                    </div>
                                                </article>
                                            <?php endforeach; ?>

                                        </div>

                                    <?php endif; ?>

                                    <!-- MOSTRAMOS LA ACTIVIDAD RECIENTE -->

                                    <div class="curso-interno-grupo">
                                        <div class="curso-interno-grupo-titulo">
                                            <h3>Actividad reciente</h3>
                                            <span>Más reciente primero</span>
                                        </div>

                                        
                                        <?php if (empty($actividadCurso)): ?>

                                            <div class="curso-interno-vacio">Todavía no hay publicaciones en este curso.</div>

                                        <?php else: ?>

                                            <?php foreach ($actividadCurso as $actividad): ?>

                                                <?php if ($actividad["Tipo"] === "material"): ?>

                                                    <!-- MOSTRAMOS UNA PUBLICACION DE MATERIAL -->

                                                    <?php $material = $actividad["Registro"]; ?>

                                                    <article class="curso-pregunta-tarjeta">

                                                        <div class="curso-pregunta-meta">
                                                            <span><?php echo mostrarIconoCurso("material", $iconos); ?> <?php echo escaparCurso($material["Autor"]); ?> publicó nuevo material</span>
                                                            <time><?php echo escaparCurso(tiempoPublicacionCurso($material["FechaPublicacion"])); ?></time>
                                                        </div>

                                                        <h4><a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=material&idMaterial=<?php echo (int)$material["idMaterial"]; ?>" class="curso-pregunta-enlace"><?php echo escaparCurso($material["Titulo"]); ?></a></h4>

                                                        <?php if (trim((string)$material["Descripcion"]) !== ""): ?>
                                                            <p><?php echo escaparCurso($material["Descripcion"]); ?></p>
                                                        <?php endif; ?>

                                                        <div class="curso-pregunta-pie">
                                                            <span><?php echo mostrarIconoCurso("archivo", $iconos); ?> <?php echo (int)$material["TotalArchivos"]; ?> archivos</span>
                                                            <span><?php echo mostrarIconoCurso("enlace", $iconos); ?> <?php echo (int)$material["TotalEnlaces"]; ?> enlaces</span>
                                                        </div>

                                                    </article>

                                                <?php else: ?>

                                                    <!-- MOSTRAMOS UNA PUBLICACION DE PREGUNTA -->

                                                    <?php $pregunta = $actividad["Registro"]; ?>

                                                    <article class="curso-pregunta-tarjeta">

                                                        <div class="curso-pregunta-meta">
                                                            <span><?php echo mostrarIconoCurso("pregunta", $iconos); ?> <?php echo escaparCurso($pregunta["Autor"]); ?> creó una pregunta</span>
                                                            <time><?php echo escaparCurso(tiempoPublicacionCurso($pregunta["FechaCreacion"])); ?></time>
                                                        </div>

                                                        <h4><a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&idPregunta=<?php echo (int)$pregunta["idPregunta"]; ?>" class="curso-pregunta-enlace"><?php echo escaparCurso($pregunta["Titulo"]); ?></a></h4>

                                                        <p><?php echo nl2br(escaparCurso($pregunta["Contenido"])); ?></p>
                                                        
                                                        <?php if (!empty($pregunta["TituloMaterial"])): ?>
                                                            <div class="curso-pregunta-sobre">Sobre: <?php echo escaparCurso($pregunta["TituloMaterial"]); ?></div>
                                                        
                                                        <?php endif; ?>
                                                        <div class="curso-pregunta-pie">

                                                            <span><?php echo mostrarIconoCurso("respuesta", $iconos); ?> <?php echo (int)$pregunta["TotalRespuestas"]; ?> respuestas</span>

                                                            <?php if ((int)$pregunta["Destacada"] === 1): ?>
                                                                <span><?php echo mostrarIconoCurso("destacada", $iconos); ?> Destacada</span>
                                                            <?php endif; ?>

                                                            <?php if ($pregunta["Estado"] === "cerrada"): ?>
                                                                <span class="curso-pregunta-resuelta">Resuelta</span>
                                                            <?php endif; ?>

                                                        </div>

                                                    </article>

                                                <?php endif; ?>

                                            <?php endforeach; ?>

                                        <?php endif; ?>


                                    

                                    </div>

                                </div>

                                <!-- MOSTRAMOS LA CREACION DE PREGUNTAS A LA DERECHA -->

                                <aside class="curso-interno-preguntar">

                                    <?php if (!$esCreador): ?>

                                        <div class="curso-interno-preguntar-texto">
                                            <h3><?php echo mostrarIconoCurso("pregunta", $iconos); ?> ¿Tenés alguna pregunta?</h3>
                                            <p>Iniciá una conversación sobre el curso. Los demás participantes podrán responderte.</p>
                                        </div>

                                        <button type="button" id="botonCrearPregunta" class="curso-pregunta-boton" aria-expanded="<?php echo $errorPregunta !== "" ? 'true' : 'false'; ?>" aria-controls="formCrearPregunta">Crear pregunta</button>

                                        <form id="formCrearPregunta" class="curso-pregunta-form" method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal<?php echo $materialRelacionado ? '&crearPregunta=1&idMaterial=' . (int)$materialRelacionado["idMaterial"] : ''; ?>" <?php echo $errorPregunta === "" ? "hidden" : ""; ?>>

                                            <input type="hidden" name="token_pregunta" value="<?php echo escaparCurso($_SESSION["token_pregunta"]); ?>">


                                                <!-- MOSTRAMOS EL MATERIAL RELACIONADO -->

                                                <?php if ($materialRelacionado): ?>

                                                    <div class="curso-pregunta-referencia">
                                                        Sobre: <strong><?php echo escaparCurso($materialRelacionado["Titulo"]); ?></strong>
                                                    </div>

                                                    <input type="hidden" name="idMaterial" value="<?php echo (int)$materialRelacionado["idMaterial"]; ?>">

                                                <?php endif; ?>


                                            <label for="tituloPregunta">Título de la pregunta</label>
                                            <input type="text" id="tituloPregunta" name="titulo" maxlength="180" required placeholder="¿Qué necesitás saber?" value="<?php echo escaparCurso($_POST["titulo"] ?? ""); ?>">

                                            <label for="contenidoPregunta">Descripción</label>
                                            <textarea id="contenidoPregunta" name="contenido" rows="5" maxlength="5000" required placeholder="Explicá tu pregunta para que otros participantes puedan responderte."><?php echo escaparCurso(is_string($_POST["contenido"] ?? "") ? ($_POST["contenido"] ?? "") : ""); ?></textarea>

                                            <button type="submit" name="crearPregunta" value="1" class="curso-pregunta-boton">Publicar pregunta</button>

                                        </form>

                                    <?php else: ?>

                                        <div class="curso-interno-preguntar-texto">
                                            <h3><?php echo mostrarIconoCurso("respuesta", $iconos); ?> Preguntas del curso</h3>
                                            <p>Acá podrás consultar las preguntas de tus alumnos y participar en sus conversaciones.</p>
                                        </div>

                                    <?php endif; ?>

                                 </aside>

                            </div>

                        <?php endif; ?>

                        </section>

                    
                        <!-- SECCION MATERIAL -->

                        <?php elseif ($seccion === "material"): ?>

                            <section class="curso-interno-seccion">

                                
                                <!-- INFORMAMOS EL RESULTADO DE LA PUBLICACION -->

                                <?php if ($materialPublicado): ?>
                                    <div class="alert alert-success mt-3">El material fue publicado correctamente.</div>
                                <?php endif; ?>

                                <?php if ($errorMaterial !== ""): ?>
                                    <div class="alert alert-danger mt-3"><?php echo escaparCurso($errorMaterial); ?></div>
                                <?php endif; ?>

                                <!-- MOSTRAMOS EL LISTADO O EL CONTENIDO DE UN MATERIAL -->

                                <div id="vistaMaterial" <?php echo $editorMaterialAbierto ? "hidden" : ""; ?>>

                                    <?php if ($contenidoMaterial): ?>

                                        <!-- MOSTRAMOS LOS ARCHIVOS Y ENLACES DEL MATERIAL -->

                                        <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=material" class="curso-material-volver">← Volver a materiales</a>

                                        <div class="curso-material-detalle">

                                            <h2><?php echo escaparCurso($contenidoMaterial["Titulo"]); ?></h2>

                                            <?php if (trim((string)$contenidoMaterial["Descripcion"]) !== ""): ?>
                                                <p><?php echo nl2br(escaparCurso($contenidoMaterial["Descripcion"])); ?></p>
                                            <?php endif; ?>


                                                <!-- PERMITIMOS CREAR UNA PREGUNTA SOBRE ESTE MATERIAL -->

                                                <?php if (!$esCreador): ?>

                                                    <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=principal&crearPregunta=1&idMaterial=<?php echo (int)$contenidoMaterial["idMaterial"]; ?>" class="curso-material-preguntar">
                                                        <?php echo mostrarIconoCurso("pregunta", $iconos); ?>
                                                        Hacer una pregunta sobre este material
                                                    </a>

                                                <?php endif; ?>


                                            <h3>Archivos adjuntos</h3>

                                            <?php if (empty($contenidoMaterial["Archivos"])): ?>
                                                <p class="curso-material-texto-secundario">Este material no tiene archivos adjuntos.</p>
                                            <?php else: ?>

                                                <?php foreach ($contenidoMaterial["Archivos"] as $archivo): ?>

                                                    <a class="curso-material-adjunto" href="descargar_material.php?idArchivo=<?php echo (int)$archivo["idArchivo"]; ?>">
                                                        <?php echo mostrarIconoCurso("archivo", $iconos); ?>
                                                        <span><?php echo escaparCurso($archivo["NombreOriginal"]); ?></span>
                                                        <small>Descargar</small>
                                                    </a>

                                                <?php endforeach; ?>

                                            <?php endif; ?>

                                            <h3>Enlaces</h3>

                                            <?php if (empty($contenidoMaterial["Enlaces"])): ?>
                                                <p class="curso-material-texto-secundario">Este material no tiene enlaces.</p>
                                            <?php else: ?>

                                                <?php foreach ($contenidoMaterial["Enlaces"] as $enlace): ?>

                                                    <a class="curso-material-adjunto" href="<?php echo escaparCurso($enlace["URL"]); ?>" target="_blank" rel="noopener noreferrer">
                                                        <?php echo mostrarIconoCurso("enlace", $iconos); ?>
                                                        <span><?php echo escaparCurso($enlace["URL"]); ?></span>
                                                        <small>Abrir</small>
                                                    </a>

                                                <?php endforeach; ?>

                                            <?php endif; ?>

                                        </div>

                                    <?php else: ?>

                                        <!-- MOSTRAMOS LOS MATERIALES PUBLICADOS -->

                                        <div class="curso-material-cabecera">

                                            <div>
                                                <h2>Material del curso</h2>
                                                <p>Consultá los archivos y enlaces publicados en el curso.</p>
                                            </div>

                                            <?php if ($esCreador): ?>
                                                <button type="button" id="abrirEditorMaterial" class="curso-material-boton">Crear material</button>
                                            <?php endif; ?>

                                        </div>

                                        <?php if (empty($materialesCurso)): ?>

                                            <div class="curso-material-sin-contenido">
                                                <?php echo mostrarIconoCurso("archivo", $iconos); ?>
                                                <h3>Todavía no hay materiales</h3>
                                                <p><?php echo $esCreador ? "Publicá el primer material para tus alumnos." : "El creador todavía no publicó contenido."; ?></p>
                                            </div>

                                        <?php else: ?>

                                            <div class="curso-material-publicaciones">

                                                <?php foreach ($materialesCurso as $material): ?>

                                                    <a href="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=material&idMaterial=<?php echo (int)$material["idMaterial"]; ?>" class="curso-material-publicacion">

                                                        <?php echo mostrarIconoCurso("material", $iconos); ?>

                                                        <div>
                                                            <strong><?php echo escaparCurso($material["Titulo"]); ?></strong>
                                                            <span><?php echo escaparCurso($material["Autor"]); ?> · <?php echo escaparCurso(tiempoPublicacionCurso($material["FechaPublicacion"])); ?></span>
                                                            <small><?php echo (int)$material["TotalArchivos"]; ?> archivos · <?php echo (int)$material["TotalEnlaces"]; ?> enlaces</small>
                                                        </div>

                                                    </a>

                                                <?php endforeach; ?>

                                            </div>

                                        <?php endif; ?>

                                    <?php endif; ?>

                                </div>


                                <?php if ($esCreador): ?>

                                    <!-- PREPARAMOS EL FORMULARIO DE PUBLICACION -->

                                    <div id="editorMaterial" <?php echo $editorMaterialAbierto ? "" : "hidden"; ?>>
                                        <div class="curso-material-barra">
                                            <div>
                                                <button type="button" id="volverMaterial" class="curso-material-volver">← Volver a materiales</button>
                                                <h2>Nuevo material</h2>
                                            </div>

                                            <button type="submit" name="publicarMaterial" value="1" form="formMaterial" class="curso-material-publicar">Publicar</button>
                                        </div>

                                        <form id="formMaterial" method="POST" action="curso.php?idCurso=<?php echo $idCurso; ?>&seccion=material" enctype="multipart/form-data">
                                            <input type="hidden" name="token_material" value="<?php echo escaparCurso($_SESSION["token_material"]); ?>">

                                            <!-- COMPLETAMOS EL TITULO Y LA DESCRIPCION -->

                                            <div class="curso-material-bloque">

                                                <label for="tituloMaterial">Título *</label>
                                                <input type="text" id="tituloMaterial" name="tituloMaterial" maxlength="180" required placeholder="Escribí el título del material">

                                                <label for="descripcionMaterial">Descripción (opcional)</label>
                                                <textarea id="descripcionMaterial" name="descripcionMaterial" rows="6" maxlength="5000" placeholder="Describí el contenido del material..."></textarea>

                                            </div>

                                            <!-- PREPARAMOS LOS ARCHIVOS Y ENLACES -->

                                            <div class="curso-material-bloque">

                                                <h3>Adjuntar</h3>

                                                <div class="curso-material-adjuntar-opciones">

                                                    <label for="archivosMaterial" class="curso-material-adjuntar">
                                                        <span><?php echo mostrarIconoCurso("subir", $iconos); ?></span>
                                                        Subir archivos
                                                    </label>

                                                    <input type="file" id="archivosMaterial" name="archivosMaterial[]" multiple hidden>

                                                    <button type="button" id="mostrarEnlaceMaterial" class="curso-material-adjuntar">
                                                        <span><?php echo mostrarIconoCurso("enlace", $iconos); ?></span>
                                                        Enlace
                                                    </button>

                                                </div>

                                                <ul id="listaArchivosMaterial" class="curso-material-lista"></ul>

                                                <div id="campoEnlaceMaterial" class="curso-material-enlace-campo" hidden>
                                                    <label for="urlMaterial">Dirección del enlace</label>
                                                    <div>
                                                        <input type="url" id="urlMaterial" placeholder="https://ejemplo.com">
                                                        <button type="button" id="agregarEnlaceMaterial">Agregar</button>
                                                    </div>
                                                    <p id="errorEnlaceMaterial" class="curso-material-error" hidden>Ingresá una dirección HTTP o HTTPS válida.</p>
                                                </div>

                                                <ul id="listaEnlacesMaterial" class="curso-material-lista"></ul>

                                            </div>

                                        </form>

                                    </div>

                                <?php endif; ?>

                            </section>


                    <!-- SECCION SOLICITUDES -->

                    <?php elseif ($seccion === "solicitudes"): ?>

                        <?php include __DIR__ . "/componentes/curso_solicitudes.php"; ?>

                    <!-- SECCION PERSONAS -->

                    <?php else: ?>

                    <section class="curso-interno-seccion">

                        <div class="curso-interno-introduccion">
                            <h2><?php echo mostrarIconoCurso("personas", $iconos); ?> Personas</h2>
                            <p>Conocé al creador y a los participantes del curso.</p>
                        </div>

                        <?php if ($errorPersonas !== ""): ?>
                            <div class="alert alert-danger mt-3"><?php echo escaparCurso($errorPersonas); ?></div>
                        <?php endif; ?>

                        <!-- MOSTRAMOS AL CREADOR DEL CURSO -->

                        <div class="curso-personas-grupo">

                            <h3>Creador del curso</h3>

                            <div class="curso-persona-fila">
                                <span class="curso-persona-avatar"><?php echo mostrarIconoCurso("persona", $iconos) ?: escaparCurso(mb_strtoupper(mb_substr($curso["NombreCreador"], 0, 1))); ?></span>

                                <div class="curso-persona-informacion">
                                    <strong><?php echo escaparCurso($curso["NombreCreador"]); ?></strong>
                                    <span>Creador del curso</span>
                                </div>
                            </div>

                        </div>

                        <!-- MOSTRAMOS LOS ALUMNOS CON INSCRIPCION CONFIRMADA -->

                        <div class="curso-personas-grupo">

                            <h3>Alumnos inscritos <span class="curso-personas-cantidad"><?php echo count($alumnosInscritos); ?></span></h3>

                            <?php if (empty($alumnosInscritos)): ?>

                                <p class="curso-personas-sin-alumnos">Todavía no hay alumnos inscritos en este curso.</p>

                            <?php else: ?>

                                <div class="curso-personas-lista">

                                    <?php foreach ($alumnosInscritos as $alumno): ?>

                                        <div class="curso-persona-fila">

                                            <span class="curso-persona-avatar"><?php echo mostrarIconoCurso("persona", $iconos) ?: escaparCurso(mb_strtoupper(mb_substr($alumno["NombreCompleto"], 0, 1))); ?></span>

                                            <div class="curso-persona-informacion">
                                                <strong><?php echo escaparCurso($alumno["NombreCompleto"]); ?></strong>
                                                <span>Alumno inscrito</span>
                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </div>

                    </section>

                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/menu_lateral.js"></script>
    <script src="js/notificaciones_rapidas.js"></script>
    <script src="js/curso_interno.js?v=7"></script>
</body>
</html>
