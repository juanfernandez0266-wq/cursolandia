<?php

session_start();

require_once __DIR__ . "/clases/curso.php";


// COMPROBAR SESION

if (!isset($_SESSION["idUsuario"])) {
    $_SESSION["volverDespuesLogin"] = "crear_curso.php";
    header("Location: iniciar_sesion.php");
    exit;
}

$idUsuario = (int) $_SESSION["idUsuario"];

if (!isset($_SESSION["token_crear_curso"])) {
    $_SESSION["token_crear_curso"] = bin2hex(random_bytes(32));
}

$cursoCreado = $_SESSION["curso_creado"] ?? null;
unset($_SESSION["curso_creado"]);

$errores = [];

$Titulo = "";
$Descripcion = "";
$TipoAcceso = "";
$TipoCurso = "";
$Costo = "";
$Cupo = "";
$TieneCupo = "";
$hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");
$FechaInicio = "";
$FechaFin = "";
$Etiquetas = [];


// GUARDAR CURSO

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_curso"])) {

    $tokenEnviado = $_POST["token_crear_curso"] ?? "";

    if (!is_string($tokenEnviado) || !hash_equals($_SESSION["token_crear_curso"], $tokenEnviado)) {
        $errores[] = "No se pudo verificar el formulario. Recargá la página.";
    } else {

        $Titulo = is_string($_POST["Titulo"] ?? null) ? trim($_POST["Titulo"]) : "";
        $Descripcion = is_string($_POST["Descripcion"] ?? null) ? trim($_POST["Descripcion"]) : "";
        $TipoAcceso = is_string($_POST["TipoAcceso"] ?? null) ? $_POST["TipoAcceso"] : "";
        $TipoCurso = is_string($_POST["TipoCurso"] ?? null) ? $_POST["TipoCurso"] : "";
        $Costo = is_string($_POST["Costo"] ?? null) ? trim($_POST["Costo"]) : "";
        $Cupo = is_string($_POST["Cupo"] ?? null) ? trim($_POST["Cupo"]) : "";
        $TieneCupo = is_string($_POST["TieneCupo"] ?? null) ? $_POST["TieneCupo"] : "";
        $FechaInicio = is_string($_POST["FechaInicio"] ?? null) ? $_POST["FechaInicio"] : "";
        $FechaFin = is_string($_POST["FechaFin"] ?? null) ? $_POST["FechaFin"] : "";
        $Etiquetas = is_array($_POST["Etiquetas"] ?? null) ? $_POST["Etiquetas"] : [];

        try {
            
        // CONTROLAMOS SI EL CURSO TIENE CUPO LIMITADO

            if ($TieneCupo === "si" && $Cupo === "") {
                throw new DomainException("Ingresá la cantidad de alumnos permitidos.");
            }

            if ($TieneCupo === "no") {
                $Cupo = "";
            }

            if (!in_array($TieneCupo, ["si", "no"], true)) {
                throw new DomainException("Seleccioná una opción de cupo válida.");
            }

            // COMPROBAMOS QUE SE HAYA SUBIDO LA IMAGEN PRINCIPAL

            $imagenPrincipal = $_FILES["ImagenPrincipal"] ?? null;

            if (
                !is_array($imagenPrincipal) ||
                !isset($imagenPrincipal["error"]) ||
                $imagenPrincipal["error"] === UPLOAD_ERR_NO_FILE
            ) {
                throw new DomainException("Agregá una imagen principal para el curso.");
            }

            if ($imagenPrincipal["error"] !== UPLOAD_ERR_OK) {
                throw new DomainException("No se pudo recibir la imagen principal.");
            }
        
            $curso = new Curso($idUsuario, $Titulo, $Descripcion, $TipoAcceso, $TipoCurso,
                $Costo, $Cupo, $FechaInicio, $FechaFin, $Etiquetas);
            
            
            $idCurso = $curso->crear($_FILES);
            $_SESSION["curso_creado"] = $idCurso;
            $_SESSION["token_crear_curso"] = bin2hex(random_bytes(32));

            header("Location: crear_curso.php");
            exit;

        } catch (DomainException $e) {
            $errores[] = $e->getMessage();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errores[] = "No se pudo guardar el curso. Revisá el registro del servidor.";
        }
    }
}

try {
    $etiquetasDisponibles = Curso::obtenerEtiquetasDisponibles();
} catch (Throwable $e) {
    error_log($e->getMessage());
    $etiquetasDisponibles = [];
    $errores[] = "No se pudieron cargar las etiquetas disponibles.";
}

$paginaActual = basename($_SERVER["PHP_SELF"]);

function escapar($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear curso - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menu_lateral.css">
    <link rel="stylesheet" href="css/notificaciones_rapidas.css">
    <link rel="stylesheet" href="css/crear_curso.css?v=9">
</head>
<body>

    <?php include __DIR__ . "/componentes/menu_lateral.php"; ?>
    <?php include __DIR__ . "/componentes/notificaciones_rapidas.php"; ?>

    <main class="contenido-con-menu container py-5 crear-curso-pagina">

    <!-- ANIMAMOS EL CONTENIDO DE CREAR CURSO -->

    <div class="crear-curso-contenido">

        <!-- ENCABEZADO DE CREAR CURSO -->
         <div class="crear-curso-encabezado">
            <h1>Crear curso</h1>
            <p>Completá la información principal de tu curso.</p>
        </div>
        <?php if ($cursoCreado != null): ?>
            <div class="alert alert-success">Curso creado correctamente. ID: <strong><?php echo (int) $cursoCreado; ?></strong>.</div>
        <?php endif; ?>

        <?php foreach ($errores as $error): ?>
            <div class="alert alert-danger"><?php echo escapar($error); ?></div>
        <?php endforeach; ?>

        <form method="POST" action="crear_curso.php" enctype="multipart/form-data" class="crear-curso-form" novalidate>

            <input type="hidden" name="token_crear_curso" value="<?php echo escapar($_SESSION['token_crear_curso']); ?>">

            <div class="crear-curso-grid">

                <!-- PREVISUALIZACION DEL CURSO -->

                <section class="crear-curso-panel">

                    <!-- TITULO -->

                    <div class="mb-4">
                        <label for="Titulo" class="form-label">Título *</label>
                        <input type="text" id="Titulo" name="Titulo" class="form-control" maxlength="150" required value="<?php echo escapar($Titulo); ?>">
                    </div>


                    <!-- IMAGEN PRINCIPAL -->

                    <div class="imagen-contenedor-principal">

                        <label class="curso-imagen-principal">

                            <input type="file" id="ImagenPrincipal" name="ImagenPrincipal"
                                class="imagen-input" accept=".jpg,.jpeg,.png,.webp" required>

                            <img class="imagen-preview" alt="Vista previa de la imagen principal"
                                draggable="false" hidden>

                            <img src="imagenes/curso/agregar_imagen.svg"
                                class="imagen-simbolo" alt="">

                            <span class="imagen-texto">Agregar imagen</span>
                            <span class="imagen-ayuda">O arrastrá una imagen aquí</span>

                        </label>

                        <button type="button" class="imagen-quitar"
                            aria-label="Quitar imagen principal" hidden>×</button>

                    </div>


                    <!-- MINIATURAS -->

                    <div class="curso-miniaturas">

                        <?php for ($i = 1; $i <= 5; $i++): ?>

                            <div class="imagen-contenedor-miniatura">

                                <label class="curso-miniatura">

                                    <input type="file" name="ImagenesExtra[]"
                                        class="imagen-input" accept=".jpg,.jpeg,.png,.webp">

                                    <img class="imagen-preview" alt="Vista previa de la imagen"
                                        draggable="false" hidden>

                                    <span class="miniatura-simbolo">+</span>

                                </label>

                                <button type="button" class="imagen-quitar"
                                    aria-label="Quitar miniatura <?php echo $i; ?>" hidden>×</button>

                            </div>

                        <?php endfor; ?>

                    </div>

                    <p id="errorImagenes" class="error-campo" hidden></p>


                
                    <!-- DESCRIPCION -->

                    <div class="mt-4">
                        <label for="Descripcion" class="form-label">Descripción *</label>
                        <textarea id="Descripcion" name="Descripcion" class="form-control" rows="5" required><?php echo escapar($Descripcion); ?></textarea>
                    </div>

                </section>


                <!-- PANEL DERECHO -->

                <aside class="crear-curso-panel">

                    <!-- ETIQUETAS -->

                    <fieldset class="mb-4">

                        <legend class="form-label">Seleccioná etiquetas</legend>

                        <div class="crear-etiquetas">

                            <?php foreach ($etiquetasDisponibles as $etiqueta): ?>

                                <label class="crear-etiqueta">
                                    <input type="checkbox" name="Etiquetas[]" value="<?php echo (int) $etiqueta["idInteres"]; ?>" <?php echo in_array((string) $etiqueta["idInteres"], array_map("strval", $Etiquetas), true) ? "checked" : ""; ?>>
                                    <span><?php echo escapar($etiqueta["NombreInteres"]); ?></span>
                                </label>

                            <?php endforeach; ?>

                        </div>

                    </fieldset>

                    <!-- ACCESO, CUPO Y COSTO -->

                    <div class="curso-configuracion-lateral">

                        <!-- ACCESO -->

                        <div class="configuracion-opcion">

                            <div class="configuracion-linea">

                                <img src="imagenes/curso/candado_abierto.svg" id="iconoAcceso" class="configuracion-icono" alt="">

                                <span id="textoAcceso">Acceso</span>
                                <span class="configuracion-flecha">⌄</span>

                                <select id="TipoAcceso" name="TipoAcceso" class="configuracion-select" aria-label="Tipo de acceso" required>
                                    <option value="" disabled <?php echo $TipoAcceso === "" ? "selected" : ""; ?>>Acceso</option>
                                    <option value="publico" <?php echo $TipoAcceso === "publico" ? "selected" : ""; ?>>Público</option>
                                    <option value="privado" <?php echo $TipoAcceso === "privado" ? "selected" : ""; ?>>Privado</option>
                                </select>

                            </div>

                        </div>


                        <!-- CUPO -->

                        <div class="configuracion-opcion">

                            <div class="configuracion-linea">

                                <svg class="configuracion-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M1 21v-2a7 7 0 0 1 14 0v2"/>
                                    <path d="M17 3.5a4 4 0 0 1 0 7"/>
                                    <path d="M18 14a7 7 0 0 1 5 7"/>
                                </svg>

                                <span id="textoCupo">Cupo</span>
                                <span class="configuracion-flecha">⌄</span>

                                <select id="TieneCupo" name="TieneCupo" class="configuracion-select" aria-label="Tipo de cupo" required>
                                    <option value="" disabled <?php echo $TieneCupo === "" ? "selected" : ""; ?>>Cupo</option>
                                    <option value="no" <?php echo $TieneCupo === "no" ? "selected" : ""; ?>>Sin límite</option>
                                    <option value="si" <?php echo $TieneCupo === "si" ? "selected" : ""; ?>>Con límite</option>
                                </select>

                            </div>

                            <div id="contenedorCupo" class="configuracion-extra" <?php echo $TieneCupo === "si" ? "" : "hidden"; ?>>
                                <label for="Cupo" class="form-label">Cantidad de alumnos</label>
                                <input type="number" id="Cupo" name="Cupo" class="form-control" min="1" step="1" placeholder="Ej: 30" value="<?php echo escapar($Cupo); ?>" <?php echo $TieneCupo === "si" ? "required" : "disabled"; ?>>
                            </div>

                        </div>
                                    <!-- COSTO -->

                        <div class="configuracion-opcion">

                            <div class="configuracion-linea">

                                <img src="imagenes/curso/icono_costo.svg" class="configuracion-icono" alt="">

                                <span id="textoCosto">Costo</span>
                                <span class="configuracion-flecha">⌄</span>

                                <select id="TipoCurso" name="TipoCurso" class="configuracion-select" aria-label="Tipo de curso" required>
                                    <option value="" disabled <?php echo $TipoCurso === "" ? "selected" : ""; ?>>Costo</option>
                                    <option value="gratuito" <?php echo $TipoCurso === "gratuito" ? "selected" : ""; ?>>Gratuito</option>
                                    <option value="pago" <?php echo $TipoCurso === "pago" ? "selected" : ""; ?>>Pago</option>
                                </select>

                            </div>

                            <div id="contenedorCosto" class="configuracion-extra" <?php echo $TipoCurso === "pago" ? "" : "hidden"; ?>>
                                <label for="Costo" class="form-label">Importe</label>
                                <input type="number" id="Costo" name="Costo" class="form-control" min="0.01" max="99999999.99" step="0.01" placeholder="Ej: 15000" value="<?php echo escapar($Costo); ?>" <?php echo $TipoCurso === "pago" ? "required" : "disabled"; ?>>
                            </div>

                        </div>

                    </div>            
                    


                    <!-- FECHA DE INICIO -->

                    <div class="mb-4">
                        <label for="FechaInicio" class="form-label">Fecha de inicio *</label>
                        <input type="date" id="FechaInicio" name="FechaInicio" class="form-control" min="<?php echo escapar($hoy); ?>" required value="<?php echo escapar($FechaInicio); ?>">
                    </div>


                    <!-- FECHA DE FIN -->

                    <div class="mb-4">
                        <label for="FechaFin" class="form-label">Fecha de fin *</label>
                        <input type="date" id="FechaFin" name="FechaFin" class="form-control" required value="<?php echo escapar($FechaFin); ?>">
                    </div>

                </aside>

            </div>


            <!-- CREAR CURSO -->

            <div class="crear-curso-acciones">
                <button type="submit" name="crear_curso" value="1" class="btn btn-primary">Crear curso</button>
            </div>

         </form>

        </div>
    </main>

    <script src="js/menu_lateral.js"></script>
    <script src="js/notificaciones_rapidas.js"></script>
    <script src="js/crear_curso.js?v=10"></script>
</body>
</html>
