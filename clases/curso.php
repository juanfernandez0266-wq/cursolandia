<?php

class Curso {

    // ATRIBUTOS

    private $idCurso;
    private $idUsuario;
    private $Titulo;
    private $Descripcion;
    private $TipoAcceso;
    private $TipoCurso;
    private $Costo;
    private $Cupo;
    private $FechaInicio;
    private $FechaFin;
    private $Etiquetas;


    // CONSTRUCTOR

    public function __construct($idUsuario, $Titulo, $Descripcion, $TipoAcceso, $TipoCurso, $Costo, $Cupo, $FechaInicio, $FechaFin, $Etiquetas = []) {
        $this->idUsuario = (int) $idUsuario;
        $this->Titulo = trim((string) $Titulo);
        $this->Descripcion = trim((string) $Descripcion);
        $this->TipoAcceso = $TipoAcceso;
        $this->TipoCurso = $TipoCurso;
        $this->Costo = $Costo;
        $this->Cupo = $Cupo;
        $this->FechaInicio = $FechaInicio;
        $this->FechaFin = $FechaFin;
        $this->Etiquetas = array_values(array_unique(array_map('intval', $Etiquetas)));
    }


    // CONSULTAR ATRIBUTOS

    public function __get($atributo) {
        return $this->$atributo;
    }


    // CONEXION A MYSQL

    private static function conectar() {
        require __DIR__ . "/../bd/conexion.php";
        mysqli_set_charset($conexion, "utf8mb4");
        return $conexion;
    }


    // VERIFICAR FECHA

    private static function fechaValida($fecha) {
        if (!is_string($fecha)) return false;
        $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        return $objeto !== false && $objeto->format('Y-m-d') === $fecha;
    }


    // VALIDACIONES DEL CURSO

    private function validar() {

        if ($this->Titulo === '' || mb_strlen($this->Titulo) > 150) {
            throw new DomainException("Ingresá un título de hasta 150 caracteres.");
        }

        if ($this->Descripcion === '') {
            throw new DomainException("Ingresá una descripción para el curso.");
        }

        if (!in_array($this->TipoAcceso, ['publico', 'privado'], true)) {
            throw new DomainException("Seleccioná un tipo de acceso válido.");
        }

        if (!in_array($this->TipoCurso, ['gratuito', 'pago'], true)) {
            throw new DomainException("Seleccioná si el curso es gratuito o pago.");
        }

        if ($this->TipoCurso === 'gratuito') {
            $this->Costo = null;
        } else {
            $costo = (string) $this->Costo;
            if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $costo) || (float) $costo <= 0) {
                throw new DomainException("Ingresá un costo válido, mayor que cero.");
            }
            $this->Costo = $costo;
        }

        if ($this->Cupo === '') $this->Cupo = null;

        if ($this->Cupo !== null) {
            $cupo = (string) $this->Cupo;
            if (!ctype_digit($cupo) || (float) $cupo < 1 || (float) $cupo > 2147483647) {
                throw new DomainException("El cupo debe ser un número entero mayor que cero.");
            }
            $this->Cupo = (int) $cupo;
        }

        // VALIDAR FECHAS

        if (!self::fechaValida($this->FechaInicio) || !self::fechaValida($this->FechaFin)) {
            throw new DomainException("Ingresá fechas válidas para el curso.");
        }

        $hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");

        if ($this->FechaInicio < $hoy) {
            throw new DomainException("La fecha de inicio no puede ser anterior a hoy.");
        }

        if ($this->FechaFin <= $this->FechaInicio) {
            throw new DomainException("La fecha de fin debe ser posterior a la fecha de inicio.");
        }
    }


    // ETIQUETAS DISPONIBLES: UTILIZAMOS LOS INTERESES EXISTENTES

    public static function obtenerEtiquetasDisponibles() {
        $conexion = self::conectar();
        try {
            $resultado = mysqli_query($conexion, "SELECT idInteres, NombreInteres FROM interes ORDER BY NombreInteres");
            return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        } finally {
            mysqli_close($conexion);
        }
    }


    // COMPROBAR QUE LAS ETIQUETAS EXISTEN

    private function validarEtiquetas($conexion) {
        if (count($this->Etiquetas) === 0) return;

        $consulta = mysqli_prepare($conexion, "SELECT idInteres FROM interes WHERE idInteres = ? LIMIT 1");
        foreach ($this->Etiquetas as $idInteres) {
            mysqli_stmt_bind_param($consulta, "i", $idInteres);
            mysqli_stmt_execute($consulta);
            mysqli_stmt_store_result($consulta);
            if (mysqli_stmt_num_rows($consulta) !== 1) {
                mysqli_stmt_close($consulta);
                throw new DomainException("Seleccioná únicamente las etiquetas disponibles.");
            }
            mysqli_stmt_free_result($consulta);
        }
        mysqli_stmt_close($consulta);
    }


    // EVITAR QUE UN USUARIO GRATUITO TENGA 4 CURSOS AL MISMO TIEMPO

    private function validarLimiteCursos($conexion) {

        $consulta = mysqli_prepare($conexion, "SELECT FechaInicio, FechaFin FROM curso WHERE idUsuario = ? AND FechaInicio <= ? AND FechaFin >= ?");
        mysqli_stmt_bind_param($consulta, "iss", $this->idUsuario, $this->FechaFin, $this->FechaInicio);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);

        $eventos = [];

        while ($fila = mysqli_fetch_assoc($resultado)) {
            $inicio = max($fila["FechaInicio"], $this->FechaInicio);
            $fin = min($fila["FechaFin"], $this->FechaFin);
            $despuesFin = (new DateTimeImmutable($fin))->modify('+1 day')->format('Y-m-d');

            $eventos[$inicio] = ($eventos[$inicio] ?? 0) + 1;
            $eventos[$despuesFin] = ($eventos[$despuesFin] ?? 0) - 1;
        }

        mysqli_stmt_close($consulta);
        ksort($eventos);
        $simultaneos = 0;

        foreach ($eventos as $fecha => $cambio) {
            $simultaneos += $cambio;
            if ($fecha <= $this->FechaFin && $simultaneos >= 3) {
                throw new DomainException("No podés crear este curso: durante esas fechas ya tendrías tres cursos activos simultáneamente.");
            }
        }
    }
    // GUARDAR IMAGENES DEL CURSO

    private function guardarImagenes($conexion, $archivos, &$rutasGuardadas, &$directorioCurso) {

        $lista = [];


        // IMAGEN PRINCIPAL

        $principal = $archivos["ImagenPrincipal"] ?? null;

        if ($principal !== null && $principal["error"] != UPLOAD_ERR_NO_FILE) {
            $principal["orden"] = 0;
            $lista[] = $principal;
        }


        // CINCO IMAGENES ADICIONALES

        if (isset($archivos["ImagenesExtra"])) {

            $extras = $archivos["ImagenesExtra"];

            if (!is_array($extras["error"] ?? null) || count($extras["error"]) > 5) {
                throw new DomainException("Se permiten hasta cinco imágenes adicionales.");
            }

            foreach ($extras["error"] as $indice => $error) {

                if ($error == UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $lista[] = [
                    "orden" => $indice + 1,
                    "error" => $error,
                    "tmp_name" => $extras["tmp_name"][$indice] ?? "",
                    "size" => $extras["size"][$indice] ?? 0
                ];
            }
        }

        // NO HAY IMAGENES

        if (count($lista) == 0) {
            return;
        }


        // SI HAY MINIATURAS, DEBE EXISTIR LA PRINCIPAL

        if ($lista[0]["orden"] !== 0) {
            throw new DomainException("Agregá la imagen principal antes de subir imágenes adicionales.");
        }


        // VALIDAR ARCHIVOS

        $formatos = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        $detector = new finfo(FILEINFO_MIME_TYPE);

        foreach ($lista as &$imagen) {

            if ($imagen["error"] != UPLOAD_ERR_OK) {
                throw new DomainException("No se pudo cargar una de las imágenes.");
            }

            if ($imagen["size"] <= 0 || $imagen["size"] > 5 * 1024 * 1024) {
                throw new DomainException("Cada imagen debe tener un tamaño máximo de 5 MB.");
            }

            if (!is_uploaded_file($imagen["tmp_name"])) {
                throw new DomainException("No se pudo verificar una de las imágenes.");
            }

            $tipo = $detector->file($imagen["tmp_name"]);

            if (!isset($formatos[$tipo]) || getimagesize($imagen["tmp_name"]) === false) {
                throw new DomainException("Solo se permiten imágenes JPG, PNG o WEBP válidas.");
            }

            $imagen["extension"] = $formatos[$tipo];
        }

        unset($imagen);

        // CREAR CARPETA DEL CURSO

        $directorioCurso = dirname(__DIR__) . "/uploads/cursos/" . $this->idCurso;

        if (!is_dir($directorioCurso) && !mkdir($directorioCurso, 0755, true)) {
            throw new RuntimeException("No se pudo crear la carpeta del curso.");
        }


        // REGISTRAR LAS IMAGENES

        $consulta = mysqli_prepare($conexion, "INSERT INTO cursoimagen (idCurso, Ruta, Orden) VALUES (?, ?, ?)");

        foreach ($lista as $imagen) {

            $nombre = bin2hex(random_bytes(16)) . "." . $imagen["extension"];

            $rutaRelativa = "uploads/cursos/" . $this->idCurso . "/" . $nombre;
            $rutaAbsoluta = dirname(__DIR__) . "/" . $rutaRelativa;

            if (!move_uploaded_file($imagen["tmp_name"], $rutaAbsoluta)) {
                throw new RuntimeException("No se pudo guardar una de las imágenes.");
            }

            $rutasGuardadas[] = $rutaAbsoluta;

            $orden = $imagen["orden"];

            mysqli_stmt_bind_param($consulta, "isi", $this->idCurso, $rutaRelativa, $orden);
            mysqli_stmt_execute($consulta);
        }

        mysqli_stmt_close($consulta);
    }
    
    // OBTENEMOS CURSOS DISPONIBLES PARA INICIO

    
      public static function obtenerCursosParaInicio($busqueda = "", $idEtiqueta = 0) {

        $conexion = self::conectar();

        try {

            $hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");

            $busqueda = trim((string) $busqueda);
            $idEtiqueta = (int) $idEtiqueta;
            $patron = "%" . $busqueda . "%";

            $sql = "SELECT
                        c.idCurso,
                        c.Titulo,
                        c.Descripcion,
                        c.TipoAcceso,
                        c.TipoCurso,
                        c.FechaInicio,
                        c.FechaFin,
                        ci.Ruta AS ImagenPrincipal

                    FROM curso AS c

                    LEFT JOIN cursoimagen AS ci
                        ON ci.idCurso = c.idCurso
                        AND ci.Orden = 0

                    WHERE c.FechaFin >= ?

                    AND (
                        ? = ''
                        OR c.Titulo LIKE ?
                        OR EXISTS (
                            SELECT 1
                            FROM cursointeres AS ce
                            INNER JOIN interes AS i
                                ON ce.idInteres = i.idInteres
                            WHERE ce.idCurso = c.idCurso
                            AND i.NombreInteres LIKE ?
                        )
                    )

                    AND (
                        ? = 0
                        OR EXISTS (
                            SELECT 1
                            FROM cursointeres AS cf
                            WHERE cf.idCurso = c.idCurso
                            AND cf.idInteres = ?
                        )
                    )

                    ORDER BY c.idCurso DESC";

            $consulta = mysqli_prepare($conexion, $sql);

            mysqli_stmt_bind_param(
                $consulta,
                "ssssii",
                $hoy,
                $busqueda,
                $patron,
                $patron,
                $idEtiqueta,
                $idEtiqueta
            );

            mysqli_stmt_execute($consulta);

            $resultado = mysqli_stmt_get_result($consulta);
            $cursos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

            mysqli_stmt_close($consulta);

            return $cursos;

        } finally {

            mysqli_close($conexion);

        }
        
    }
    // CREAR Y GUARDAR CURSO

    public function crear($imagenes =[]) {
        $this->validar();

        $rutasGuardadas = [];
        $directorioCurso = null;

        $conexion = self::conectar();

        try {
            mysqli_begin_transaction($conexion);

            // BLOQUEAR EL REGISTRO DEL CREADOR DURANTE LA COMPROBACION
            $consultaUsuario = mysqli_prepare($conexion, "SELECT PlanTipo, FechaVencimiento FROM usuario WHERE idUsuario = ? FOR UPDATE");
            mysqli_stmt_bind_param($consultaUsuario, "i", $this->idUsuario);
            mysqli_stmt_execute($consultaUsuario);
            $resultadoUsuario = mysqli_stmt_get_result($consultaUsuario);
            $usuario = mysqli_fetch_assoc($resultadoUsuario);
            mysqli_stmt_close($consultaUsuario);

            if (!$usuario) throw new DomainException("No se encontró el usuario que crea el curso.");

            // SOLO SE CONSIDERA PRO SI SU PLAN CUBRE TODO EL PERIODO
            $esPro = strtolower((string) $usuario["PlanTipo"]) === 'pro'
                && !empty($usuario["FechaVencimiento"])
                && substr($usuario["FechaVencimiento"], 0, 10) >= $this->FechaFin;

            if (!$esPro) $this->validarLimiteCursos($conexion);

            $this->validarEtiquetas($conexion);

            $sql = "INSERT INTO curso (idUsuario, Titulo, Descripcion, TipoAcceso, TipoCurso, Costo, Cupo, FechaInicio, FechaFin)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $consulta = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($consulta, "isssssiss", $this->idUsuario, $this->Titulo, $this->Descripcion,
                $this->TipoAcceso, $this->TipoCurso, $this->Costo, $this->Cupo, $this->FechaInicio, $this->FechaFin);
            mysqli_stmt_execute($consulta);
            $this->idCurso = mysqli_insert_id($conexion);
            mysqli_stmt_close($consulta);

            if (count($this->Etiquetas) > 0) {
                $consultaEtiqueta = mysqli_prepare($conexion, "INSERT INTO cursointeres (idCurso, idInteres) VALUES (?, ?)");
                foreach ($this->Etiquetas as $idInteres) {
                    mysqli_stmt_bind_param($consultaEtiqueta, "ii", $this->idCurso, $idInteres);
                    mysqli_stmt_execute($consultaEtiqueta);
                }
                mysqli_stmt_close($consultaEtiqueta);
            }

            // GUARDAR IMAGENES DEL CURSO

            $this->guardarImagenes($conexion, $imagenes, $rutasGuardadas, $directorioCurso);
            
            mysqli_commit($conexion);
            return $this->idCurso;

        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            // ELIMINAR IMAGENES SI FALLA LA CREACION
            foreach ($rutasGuardadas as $ruta) {
                if (is_file($ruta)) {
                    unlink($ruta);
                }
            }

            if ($directorioCurso !== null && is_dir($directorioCurso)) {
                @rmdir($directorioCurso);
            }
            throw $e;
        } finally {
            mysqli_close($conexion);
        }
    }

        // OBTENEMOS LOS CURSOS CREADOS POR UN USUARIO

        public static function obtenerCursosCreadosPorUsuario($idUsuario) {

            $conexion = self::conectar();

            try {

                $sql = "SELECT c.idCurso, c.Titulo, c.TipoAcceso, c.TipoCurso,
                            c.FechaInicio, c.FechaFin,
                            ci.Ruta AS ImagenPrincipal
                        FROM curso AS c
                        LEFT JOIN cursoimagen AS ci
                            ON ci.idCurso = c.idCurso AND ci.Orden = 0
                        WHERE c.idUsuario = ?
                        ORDER BY c.idCurso DESC";

                $consulta = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($consulta, "i", $idUsuario);
                mysqli_stmt_execute($consulta);

                $resultado = mysqli_stmt_get_result($consulta);
                $cursos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

                mysqli_stmt_close($consulta);

                return $cursos;

            } finally {

                mysqli_close($conexion);

            }
        }


        // OBTENEMOS LOS CURSOS SEGUN EL ESTADO DE INSCRIPCION

        public static function obtenerCursosPorEstadoInscripcion($idUsuario, $estado) {

            if (!in_array($estado, ["confirmada", "pendiente"], true)) {
                throw new DomainException("Estado de inscripción inválido.");
            }

            $conexion = self::conectar();

            try {

                $sql = "SELECT c.idCurso, c.Titulo, c.TipoCurso, c.TipoAcceso,
                            c.FechaInicio, c.FechaFin,
                            ci.Ruta AS ImagenPrincipal,
                            i.Estado, i.FechaInscripcion
                        FROM inscripcion AS i
                        INNER JOIN curso AS c ON c.idCurso = i.idCurso
                        LEFT JOIN cursoimagen AS ci
                            ON ci.idCurso = c.idCurso AND ci.Orden = 0
                        WHERE i.idUsuario = ? AND i.Estado = ?
                        AND c.idUsuario <> i.idUsuario
                        ORDER BY i.FechaInscripcion DESC, i.idInscripcion DESC";

                $consulta = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($consulta, "is", $idUsuario, $estado);
                mysqli_stmt_execute($consulta);

                $resultado = mysqli_stmt_get_result($consulta);
                $cursos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

                mysqli_stmt_close($consulta);

                return $cursos;

            } finally {
                mysqli_close($conexion);
            }
        }


        // COMPROBAMOS SI EL USUARIO TIENE PLAN PRO VIGENTE

        public static function usuarioTienePlanProVigente($idUsuario) {

            $conexion = self::conectar();

            try {

                $sql = "SELECT PlanTipo, FechaVencimiento
                        FROM usuario
                        WHERE idUsuario = ?";

                $consulta = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($consulta, "i", $idUsuario);
                mysqli_stmt_execute($consulta);

                $resultado = mysqli_stmt_get_result($consulta);
                $usuario = mysqli_fetch_assoc($resultado);

                mysqli_stmt_close($consulta);

                $hoy = (new DateTimeImmutable(
                    "today",
                    new DateTimeZone("America/Argentina/San_Luis")
                ))->format("Y-m-d");

                return $usuario !== null
                    && $usuario["PlanTipo"] === "pro"
                    && !empty($usuario["FechaVencimiento"])
                    && substr($usuario["FechaVencimiento"], 0, 10) >= $hoy;

            } finally {
                mysqli_close($conexion);
            }
        }

// OBTENEMOS EL DETALLE Y LAS IMAGENES DEL CURSO

public static function obtenerDetalleCurso($idCurso) {

    $conexion = self::conectar();

    try {
        $sql = "SELECT idCurso, idUsuario, Titulo, Descripcion, TipoAcceso,
                       TipoCurso, Costo, Cupo, FechaInicio, FechaFin
                FROM curso WHERE idCurso = ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);
        $curso = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$curso) return null;

        $sql = "SELECT Ruta FROM cursoimagen WHERE idCurso = ? ORDER BY Orden ASC";
        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);
        $curso["Imagenes"] = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
        mysqli_stmt_close($consulta);

        return $curso;

    } finally {
        mysqli_close($conexion);
    }
}

// COMPROBAMOS SI EL USUARIO YA TIENE UNA INSCRIPCION

public static function obtenerEstadoInscripcion($idUsuario, $idCurso) {

    $conexion = self::conectar();

    try {
        $sql = "SELECT Estado FROM inscripcion WHERE idUsuario = ? AND idCurso = ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $idCurso);
        mysqli_stmt_execute($consulta);
        $registro = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        return $registro["Estado"] ?? null;

    } finally {
        mysqli_close($conexion);
    }
}


// INSCRIBIMOS AL USUARIO EN UN CURSO PUBLICO Y GRATUITO

public static function inscribirEnCursoGratuito($idUsuario, $idCurso) {

    $conexion = self::conectar();
    $hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");

    mysqli_begin_transaction($conexion);

    try {

        // BLOQUEAMOS EL USUARIO MIENTRAS VALIDAMOS SU LIMITE

        $consulta = mysqli_prepare($conexion, "SELECT PlanTipo, FechaVencimiento FROM usuario WHERE idUsuario = ? FOR UPDATE");
        mysqli_stmt_bind_param($consulta, "i", $idUsuario);
        mysqli_stmt_execute($consulta);
        $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$usuario) throw new DomainException("No encontramos tu cuenta.");

        // BLOQUEAMOS EL CURSO PARA CONTROLAR EL CUPO

        $consulta = mysqli_prepare($conexion, "SELECT idUsuario, TipoCurso, TipoAcceso, Cupo, FechaInicio, FechaFin FROM curso WHERE idCurso = ? FOR UPDATE");
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);
        $curso = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$curso) throw new DomainException("El curso no existe.");

        if ((int)$curso["idUsuario"] === (int)$idUsuario) {
            throw new DomainException("No podés inscribirte en un curso que creaste.");
        }

        if ($curso["TipoCurso"] !== "gratuito" || $curso["TipoAcceso"] !== "publico") {
            throw new DomainException("Este curso no permite inscripción gratuita directa.");
        }

        $inicio = substr($curso["FechaInicio"], 0, 10);
        $fin = substr($curso["FechaFin"], 0, 10);

        if ($hoy < $inicio || $hoy > $fin) {
            throw new DomainException("La inscripción solo está disponible durante el período activo del curso.");
        }

        // EVITAMOS INSCRIPCIONES DUPLICADAS

        $consulta = mysqli_prepare($conexion, "SELECT Estado FROM inscripcion WHERE idUsuario = ? AND idCurso = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $idCurso);
        mysqli_stmt_execute($consulta);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if ($existente) {
            throw new DomainException("Ya registraste una inscripción en este curso.");
        }

        // CONTROLAMOS EL CUPO DISPONIBLE

        if ($curso["Cupo"] !== null) {

            $consulta = mysqli_prepare($conexion, "SELECT COUNT(*) AS Total FROM inscripcion WHERE idCurso = ? AND Estado = 'confirmada'");
            mysqli_stmt_bind_param($consulta, "i", $idCurso);
            mysqli_stmt_execute($consulta);
            $cantidad = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            if ((int)$cantidad["Total"] >= (int)$curso["Cupo"]) {
                throw new DomainException("El curso ya alcanzó su cupo máximo.");
            }
        }

        // COMPROBAMOS SI EL PLAN PRO CUBRE TODO EL CURSO

        $vencimiento = substr((string)($usuario["FechaVencimiento"] ?? ""), 0, 10);
        $tienePro = $usuario["PlanTipo"] === "pro" && $vencimiento >= $fin;

        if (!$tienePro) {

            // REVISAMOS LOS PERIODOS DE LAS INSCRIPCIONES CONFIRMADAS

            $sql = "SELECT c.FechaInicio, c.FechaFin
                    FROM inscripcion AS i
                    INNER JOIN curso AS c ON c.idCurso = i.idCurso
                    WHERE i.idUsuario = ? AND i.Estado = 'confirmada'
                      AND c.FechaFin >= ? AND c.FechaInicio <= ?";

            $consulta = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($consulta, "iss", $idUsuario, $hoy, $fin);
            mysqli_stmt_execute($consulta);
            $cursosActuales = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
            mysqli_stmt_close($consulta);

            $eventos = [];

            // CONTAMOS EL NUEVO CURSO Y LOS PERIODOS QUE SE SUPERPONEN

            $sumarPeriodo = function ($desde, $hasta) use (&$eventos) {
                $eventos[$desde] = ($eventos[$desde] ?? 0) + 1;
                $siguiente = (new DateTimeImmutable($hasta))->modify("+1 day")->format("Y-m-d");
                $eventos[$siguiente] = ($eventos[$siguiente] ?? 0) - 1;
            };

            $sumarPeriodo($hoy, $fin);

            foreach ($cursosActuales as $actual) {

                $desde = max($hoy, substr($actual["FechaInicio"], 0, 10));
                $hasta = min($fin, substr($actual["FechaFin"], 0, 10));

                if ($desde <= $hasta) $sumarPeriodo($desde, $hasta);
            }

            ksort($eventos);
            $simultaneos = 0;

            foreach ($eventos as $variacion) {
                $simultaneos += $variacion;

                if ($simultaneos > 3) {
                    throw new DomainException("Tu plan gratuito permite hasta tres cursos activos simultáneamente.");
                }
            }
        }

        // GUARDAMOS LA INSCRIPCION CONFIRMADA

        $consulta = mysqli_prepare($conexion, "INSERT INTO inscripcion (idUsuario, idCurso, Estado, FechaConfirmacion) VALUES (?, ?, 'confirmada', NOW())");
        mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $idCurso);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);

        mysqli_commit($conexion);

    } catch (Throwable $e) {
        mysqli_rollback($conexion);
        throw $e;

    } finally {
        mysqli_close($conexion);
    }
}


// REGISTRAMOS LA SOLICITUD DE INSCRIPCION A UN CURSO PAGO

public static function solicitarInscripcionCursoPago($idUsuario, $idCurso) {

    $conexion = self::conectar();
    $hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");

    mysqli_begin_transaction($conexion);

    try {

        // OBTENEMOS Y BLOQUEAMOS EL CURSO

        $consulta = mysqli_prepare($conexion, "SELECT idUsuario, TipoAcceso, TipoCurso, Cupo, FechaInicio, FechaFin FROM curso WHERE idCurso = ? FOR UPDATE");
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);
        $curso = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$curso) throw new DomainException("El curso no existe.");

        if ((int)$curso["idUsuario"] === (int)$idUsuario) {
            throw new DomainException("No podés inscribirte en un curso que creaste.");
        }

        if ($curso["TipoAcceso"] !== "publico" || $curso["TipoCurso"] !== "pago") {
            throw new DomainException("Este curso no permite esta modalidad de inscripción.");
        }

        if ($hoy < $curso["FechaInicio"] || $hoy > $curso["FechaFin"]) {
            throw new DomainException("La inscripción no está disponible fuera del período activo del curso.");
        }

        // COMPROBAMOS SI YA EXISTE UNA INSCRIPCION

        $consulta = mysqli_prepare($conexion, "SELECT Estado FROM inscripcion WHERE idUsuario = ? AND idCurso = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $idCurso);
        mysqli_stmt_execute($consulta);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if ($existente) {
            throw new DomainException("Ya registraste una inscripción en este curso.");
        }

        // COMPROBAMOS SI QUEDAN CUPOS DISPONIBLES

        if ($curso["Cupo"] !== null) {

            $consulta = mysqli_prepare($conexion, "SELECT COUNT(*) AS Total FROM inscripcion WHERE idCurso = ? AND Estado = 'confirmada'");
            mysqli_stmt_bind_param($consulta, "i", $idCurso);
            mysqli_stmt_execute($consulta);
            $cantidad = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            if ((int)$cantidad["Total"] >= (int)$curso["Cupo"]) {
                throw new DomainException("El curso ya alcanzó su cupo máximo.");
            }
        }

        // GUARDAMOS LA INSCRIPCION COMO PENDIENTE

        $consulta = mysqli_prepare($conexion, "INSERT INTO inscripcion (idUsuario, idCurso, Estado) VALUES (?, ?, 'pendiente')");
        mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $idCurso);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);

        mysqli_commit($conexion);

    } catch (Throwable $e) {
        mysqli_rollback($conexion);
        throw $e;
    } finally {
        mysqli_close($conexion);
    }
}
}
