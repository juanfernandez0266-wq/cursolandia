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
        $sql = "SELECT c.idCurso, c.idUsuario, c.Titulo, c.Descripcion,
               c.TipoAcceso, c.TipoCurso, c.Costo, c.Cupo,
               c.FechaInicio, c.FechaFin,
               CONCAT_WS(' ', u.Nombre, u.Apellido) AS NombreCreador
            FROM curso AS c
            INNER JOIN usuario AS u ON u.idUsuario = c.idUsuario
            WHERE c.idCurso = ?";
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



// CREAMOS UNA PREGUNTA EN EL CURSO

public static function crearPreguntaCurso($idCurso, $idUsuario, $titulo, $contenido, $idMaterial = null) {

    $titulo = trim($titulo);
    $contenido = trim($contenido);

    if ($titulo === "" || mb_strlen($titulo) > 180) {
        throw new DomainException("El título debe tener entre 1 y 180 caracteres.");
    }

    if ($contenido === "" || mb_strlen($contenido) > 5000) {
        throw new DomainException("La descripción debe tener entre 1 y 5000 caracteres.");
    }

    $conexion = self::conectar();


    // COMPROBAMOS QUE EL MATERIAL PERTENEZCA AL CURSO

    if ($idMaterial !== null) {

        $consulta = mysqli_prepare($conexion, "SELECT idMaterial FROM material WHERE idMaterial = ? AND idCurso = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idMaterial, $idCurso);
        mysqli_stmt_execute($consulta);
        $materialEncontrado = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$materialEncontrado) {
            mysqli_close($conexion);
            throw new DomainException("El material seleccionado no pertenece a este curso.");
        }
    }

    try {

        // REGISTRAMOS LA PREGUNTA SOLO SI LA INSCRIPCION ESTA CONFIRMADA

        $sql = "INSERT INTO pregunta (idCurso, idUsuario, idMaterial, Titulo, Contenido)
        SELECT ?, ?, ?, ?, ?
        FROM inscripcion
        WHERE idCurso = ? AND idUsuario = ? AND Estado = 'confirmada'
        LIMIT 1";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "iiissii", $idCurso, $idUsuario, $idMaterial, $titulo, $contenido, $idCurso, $idUsuario);
        mysqli_stmt_execute($consulta);

        $guardada = mysqli_stmt_affected_rows($consulta) === 1;
        mysqli_stmt_close($consulta);

        if (!$guardada) {
            throw new DomainException("Necesitás una inscripción confirmada para publicar preguntas.");
        }

    } finally {
        mysqli_close($conexion);
    }
}


// OBTENEMOS LAS PREGUNTAS Y SUS CANTIDADES DE RESPUESTAS

public static function obtenerPreguntasCurso($idCurso) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT p.idPregunta, p.idUsuario, p.idMaterial,
               p.Titulo, p.Contenido, p.Destacada, p.Estado, p.FechaCreacion,
               CONCAT_WS(' ', u.Nombre, u.Apellido) AS Autor,
               m.Titulo AS TituloMaterial,
               (SELECT COUNT(*) FROM respuesta AS r
                WHERE r.idPregunta = p.idPregunta) AS TotalRespuestas
        FROM pregunta AS p
        INNER JOIN usuario AS u ON u.idUsuario = p.idUsuario
        LEFT JOIN material AS m ON m.idMaterial = p.idMaterial AND m.idCurso = p.idCurso
        WHERE p.idCurso = ?
        ORDER BY p.FechaCreacion DESC, p.idPregunta DESC";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);

        $preguntas = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
        mysqli_stmt_close($consulta);

        return $preguntas;

    } finally {
        mysqli_close($conexion);
    }
}


// OBTENEMOS UNA PREGUNTA Y TODAS SUS RESPUESTAS

public static function obtenerConversacionCurso($idCurso, $idPregunta) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT p.*, CONCAT_WS(' ', u.Nombre, u.Apellido) AS Autor,
               m.Titulo AS TituloMaterial
        FROM pregunta AS p
        INNER JOIN usuario AS u ON u.idUsuario = p.idUsuario
        LEFT JOIN material AS m ON m.idMaterial = p.idMaterial AND m.idCurso = p.idCurso
        WHERE p.idCurso = ? AND p.idPregunta = ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idPregunta);
        mysqli_stmt_execute($consulta);
        $pregunta = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$pregunta) return null;

        // OBTENEMOS LAS RESPUESTAS EN ORDEN CRONOLOGICO

        $sql = "SELECT r.*, CONCAT_WS(' ', u.Nombre, u.Apellido) AS Autor
                FROM respuesta AS r
                INNER JOIN usuario AS u ON u.idUsuario = r.idUsuario
                WHERE r.idPregunta = ?
                ORDER BY r.FechaCreacion ASC, r.idRespuesta ASC";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idPregunta);
        mysqli_stmt_execute($consulta);
        $respuestas = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
        mysqli_stmt_close($consulta);

        return ["pregunta" => $pregunta, "respuestas" => $respuestas];

    } finally {
        mysqli_close($conexion);
    }
}


// REGISTRAMOS UNA RESPUESTA Y EVITAMOS RESPUESTAS CONSECUTIVAS

public static function responderPreguntaCurso($idCurso, $idPregunta, $idUsuario, $contenido) {

    $contenido = trim($contenido);

    if ($contenido === "" || mb_strlen($contenido) > 5000) {
        throw new DomainException("La respuesta debe tener entre 1 y 5000 caracteres.");
    }

    $conexion = self::conectar();
    mysqli_begin_transaction($conexion);

    try {

        // BLOQUEAMOS LA PREGUNTA MIENTRAS VALIDAMOS LA RESPUESTA

        $sql = "SELECT p.Estado, c.idUsuario AS Creador
                FROM pregunta AS p
                INNER JOIN curso AS c ON c.idCurso = p.idCurso
                WHERE p.idPregunta = ? AND p.idCurso = ?
                FOR UPDATE";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idPregunta, $idCurso);
        mysqli_stmt_execute($consulta);
        $pregunta = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$pregunta) throw new DomainException("No encontramos la pregunta.");

        if ($pregunta["Estado"] !== "abierta") {
            throw new DomainException("Esta conversación está cerrada.");
        }

        // COMPROBAMOS SI ES EL CREADOR O UN ALUMNO CONFIRMADO

        if ((int)$pregunta["Creador"] !== (int)$idUsuario) {

            $sql = "SELECT idInscripcion FROM inscripcion
                    WHERE idCurso = ? AND idUsuario = ? AND Estado = 'confirmada'";

            $consulta = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idUsuario);
            mysqli_stmt_execute($consulta);
            $inscripcion = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            if (!$inscripcion) {
                throw new DomainException("No tenés permiso para responder esta pregunta.");
            }
        }

        // COMPROBAMOS QUIEN PUBLICO LA ULTIMA RESPUESTA

        $consulta = mysqli_prepare($conexion, "SELECT idUsuario FROM respuesta WHERE idPregunta = ? ORDER BY idRespuesta DESC LIMIT 1");
        mysqli_stmt_bind_param($consulta, "i", $idPregunta);
        mysqli_stmt_execute($consulta);
        $ultimaRespuesta = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if ($ultimaRespuesta && (int)$ultimaRespuesta["idUsuario"] === (int)$idUsuario) {
            throw new DomainException("No podés responder dos veces seguidas. Podrás editar tu última respuesta.");
        }

        // GUARDAMOS LA RESPUESTA

        $consulta = mysqli_prepare($conexion, "INSERT INTO respuesta (idPregunta, idUsuario, Contenido) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($consulta, "iis", $idPregunta, $idUsuario, $contenido);
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


// EDITAMOS LA ULTIMA RESPUESTA PROPIA

public static function editarUltimaRespuestaCurso($idCurso, $idPregunta, $idUsuario, $idRespuesta, $contenido) {

    $contenido = trim($contenido);

    if ($contenido === "" || mb_strlen($contenido) > 5000) {
        throw new DomainException("La respuesta debe tener entre 1 y 5000 caracteres.");
    }

    $conexion = self::conectar();
    mysqli_begin_transaction($conexion);

    try {

        // BLOQUEAMOS LA PREGUNTA PARA COMPROBAR SU ESTADO

        $consulta = mysqli_prepare($conexion, "SELECT Estado FROM pregunta WHERE idPregunta = ? AND idCurso = ? FOR UPDATE");
        mysqli_stmt_bind_param($consulta, "ii", $idPregunta, $idCurso);
        mysqli_stmt_execute($consulta);
        $pregunta = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$pregunta || $pregunta["Estado"] !== "abierta") {
            throw new DomainException("Esta conversación no permite editar respuestas.");
        }

        // COMPROBAMOS CUAL ES LA ULTIMA RESPUESTA DEL USUARIO

        $consulta = mysqli_prepare($conexion, "SELECT idRespuesta FROM respuesta WHERE idPregunta = ? AND idUsuario = ? ORDER BY idRespuesta DESC LIMIT 1");
        mysqli_stmt_bind_param($consulta, "ii", $idPregunta, $idUsuario);
        mysqli_stmt_execute($consulta);
        $ultima = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$ultima || (int)$ultima["idRespuesta"] !== (int)$idRespuesta) {
            throw new DomainException("Solamente podés editar tu última respuesta.");
        }

        // ACTUALIZAMOS EL CONTENIDO Y LA FECHA DE EDICION

        $consulta = mysqli_prepare($conexion, "UPDATE respuesta SET Contenido = ?, FechaEdicion = NOW() WHERE idRespuesta = ? AND idPregunta = ? AND idUsuario = ?");
        mysqli_stmt_bind_param($consulta, "siii", $contenido, $idRespuesta, $idPregunta, $idUsuario);
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


// DESTACAMOS O QUITAMOS EL DESTACADO DE UNA PREGUNTA

public static function cambiarDestacadoPreguntaCurso($idCurso, $idPregunta, $idUsuario, $destacada) {

    $conexion = self::conectar();

    try {

        // PERMITIMOS EL CAMBIO SOLAMENTE AL CREADOR DEL CURSO

        $sql = "UPDATE pregunta AS p
                INNER JOIN curso AS c ON c.idCurso = p.idCurso
                SET p.Destacada = ?
                WHERE p.idCurso = ? AND p.idPregunta = ? AND c.idUsuario = ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "iiii", $destacada, $idCurso, $idPregunta, $idUsuario);
        mysqli_stmt_execute($consulta);
        $modificadas = mysqli_stmt_affected_rows($consulta);
        mysqli_stmt_close($consulta);

        if ($modificadas === 0) {
            throw new DomainException("No pudimos modificar el destacado de esta pregunta.");
        }

    } finally {
        mysqli_close($conexion);
    }
}


// ELIMINAMOS UNA PREGUNTA Y SUS RESPUESTAS

public static function eliminarPreguntaCurso($idCurso, $idPregunta, $idUsuario) {

    $conexion = self::conectar();

    try {

        // SOLO EL CREADOR PUEDE ELIMINAR PREGUNTAS DEL CURSO

        $sql = "DELETE p FROM pregunta AS p
                INNER JOIN curso AS c ON c.idCurso = p.idCurso
                WHERE p.idCurso = ? AND p.idPregunta = ? AND c.idUsuario = ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "iii", $idCurso, $idPregunta, $idUsuario);
        mysqli_stmt_execute($consulta);
        $eliminadas = mysqli_stmt_affected_rows($consulta);
        mysqli_stmt_close($consulta);

        if ($eliminadas === 0) {
            throw new DomainException("No encontramos una pregunta que puedas eliminar.");
        }

    } finally {
        mysqli_close($conexion);
    }
}


// ACEPTAMOS UNA RESPUESTA Y CERRAMOS LA CONVERSACION

public static function aceptarRespuestaCurso($idCurso, $idPregunta, $idUsuario, $idRespuesta) {

    $conexion = self::conectar();
    mysqli_begin_transaction($conexion);

    try {

        // COMPROBAMOS EL AUTOR DE LA PREGUNTA Y EL CREADOR DEL CURSO

        $sql = "SELECT p.Estado, p.idUsuario AS AutorPregunta, c.idUsuario AS Creador
                FROM pregunta AS p
                INNER JOIN curso AS c ON c.idCurso = p.idCurso
                WHERE p.idCurso = ? AND p.idPregunta = ?
                FOR UPDATE";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idPregunta);
        mysqli_stmt_execute($consulta);
        $pregunta = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$pregunta) throw new DomainException("No encontramos la pregunta.");

        if ($pregunta["Estado"] !== "abierta") {
            throw new DomainException("Esta conversación ya está cerrada.");
        }

        if ((int)$pregunta["AutorPregunta"] !== (int)$idUsuario &&
            (int)$pregunta["Creador"] !== (int)$idUsuario) {
            throw new DomainException("No tenés permiso para aceptar esta respuesta.");
        }

        // COMPROBAMOS QUE LA RESPUESTA PERTENEZCA A ESTA PREGUNTA

        $consulta = mysqli_prepare($conexion, "SELECT idRespuesta FROM respuesta WHERE idRespuesta = ? AND idPregunta = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idRespuesta, $idPregunta);
        mysqli_stmt_execute($consulta);
        $respuesta = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$respuesta) throw new DomainException("No encontramos la respuesta.");

        // DESTACAMOS LA RESPUESTA ACEPTADA

        $consulta = mysqli_prepare($conexion, "UPDATE respuesta SET Aceptada = 1 WHERE idRespuesta = ? AND idPregunta = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idRespuesta, $idPregunta);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);

        // CERRAMOS EL TEMA PARA IMPEDIR NUEVAS RESPUESTAS

        $consulta = mysqli_prepare($conexion, "UPDATE pregunta SET Estado = 'cerrada' WHERE idPregunta = ? AND idCurso = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idPregunta, $idCurso);
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



// OBTENEMOS LOS ALUMNOS INSCRIPTOS EN EL CURSO

public static function obtenerAlumnosInscritosCurso($idCurso) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT u.idUsuario,
                       CONCAT_WS(' ', u.Nombre, u.Apellido) AS NombreCompleto
                FROM inscripcion AS i
                INNER JOIN usuario AS u ON u.idUsuario = i.idUsuario
                INNER JOIN curso AS c ON c.idCurso = i.idCurso
                WHERE i.idCurso = ?
                  AND i.Estado = 'confirmada'
                  AND i.idUsuario <> c.idUsuario
                ORDER BY u.Nombre ASC, u.Apellido ASC";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);

        $alumnos = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);

        mysqli_stmt_close($consulta);

        return $alumnos;

    } finally {
        mysqli_close($conexion);
    }
}


// PUBLICAMOS MATERIAL CON ARCHIVOS Y ENLACES

public static function publicarMaterialCurso($idCurso, $idUsuario, $titulo, $descripcion, $archivos, $enlaces) {

    $titulo = trim($titulo);
    $descripcion = trim($descripcion);

    if ($titulo === "" || mb_strlen($titulo) > 180) throw new DomainException("Ingresá un título de hasta 180 caracteres.");
    if (mb_strlen($descripcion) > 5000) throw new DomainException("La descripción no puede superar los 5000 caracteres.");

    // VALIDAMOS LOS ENLACES ANTES DE GUARDARLOS

    $enlacesValidos = [];

    if (!is_array($enlaces)) throw new DomainException("Los enlaces no son válidos.");

    foreach ($enlaces as $enlace) {
        if (!is_string($enlace)) throw new DomainException("Encontramos un enlace no válido.");

        $enlace = trim($enlace);
        if ($enlace === "") continue;

        $protocolo = strtolower((string)parse_url($enlace, PHP_URL_SCHEME));

        if (strlen($enlace) > 2048 || !filter_var($enlace, FILTER_VALIDATE_URL) || !in_array($protocolo, ["https", "http"], true)) {
            throw new DomainException("Ingresá enlaces HTTP o HTTPS válidos de hasta 2048 caracteres.");
        }

        $enlacesValidos[] = $enlace;
    }

    $enlacesValidos = array_values(array_unique($enlacesValidos));

    // VALIDAMOS LOS ARCHIVOS ADJUNTOS

    $archivosValidos = [];
    $extensionesPermitidas = ["pdf", "png", "jpg", "jpeg", "webp", "txt", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "zip", "mp4"];

    if (isset($archivos["name"]) && is_array($archivos["name"])) {

        foreach ($archivos["name"] as $indice => $nombre) {

            $error = (int)($archivos["error"][$indice] ?? UPLOAD_ERR_NO_FILE);

            if ($error === UPLOAD_ERR_NO_FILE) continue;

            if ($error !== UPLOAD_ERR_OK) {
                throw new DomainException("Un archivo no pudo subirse. Comprobá el tamaño permitido por PHP.");
            }

            $nombre = basename(str_replace("\\", "/", (string)$nombre));
            $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            $tamano = (int)($archivos["size"][$indice] ?? 0);
            $temporal = (string)($archivos["tmp_name"][$indice] ?? "");

            if ($nombre === "" || !in_array($extension, $extensionesPermitidas, true)) {
                throw new DomainException("Tipo de archivo no permitido. Usá PDF, imágenes, documentos, ZIP o MP4.");
            }

            if ($tamano <= 0 || !is_uploaded_file($temporal)) {
                throw new DomainException("Uno de los archivos está vacío o no es válido.");
            }

            $archivosValidos[] = [
                "nombre" => mb_substr($nombre, 0, 255),
                "extension" => $extension,
                "tamano" => $tamano,
                "temporal" => $temporal
            ];
        }
    }

    $conexion = self::conectar();
    $directorioMaterial = null;
    $archivosMovidos = [];

    mysqli_begin_transaction($conexion);

    try {

        // BLOQUEAMOS EL USUARIO PARA CONTROLAR SU LIMITE DE ARCHIVOS

        $consulta = mysqli_prepare($conexion, "SELECT PlanTipo, FechaVencimiento FROM usuario WHERE idUsuario = ? FOR UPDATE");
        mysqli_stmt_bind_param($consulta, "i", $idUsuario);
        mysqli_stmt_execute($consulta);
        $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$usuario) throw new DomainException("No encontramos tu usuario.");

        // COMPROBAMOS QUE SEA EL CREADOR DEL CURSO

        $consulta = mysqli_prepare($conexion, "SELECT idCurso FROM curso WHERE idCurso = ? AND idUsuario = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idUsuario);
        mysqli_stmt_execute($consulta);
        $esCreador = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$esCreador) throw new DomainException("Solamente el creador puede publicar material.");

        $vencimiento = substr((string)($usuario["FechaVencimiento"] ?? ""), 0, 10);
        $hoy = (new DateTimeImmutable("today", new DateTimeZone("America/Argentina/San_Luis")))->format("Y-m-d");
        $tienePro = strtolower((string)$usuario["PlanTipo"]) === "pro" && $vencimiento >= $hoy;

        // APLICAMOS LOS LIMITES DEL PLAN GRATUITO

        if (!$tienePro) {

            foreach ($archivosValidos as $archivo) {
                if ($archivo["tamano"] > 5 * 1024 * 1024) {
                    throw new DomainException("En el plan gratuito, cada archivo puede pesar hasta 5 MB.");
                }
            }

            $sql = "SELECT COUNT(*) AS TotalArchivos, COALESCE(SUM(a.TamanoBytes), 0) AS TotalBytes
                    FROM materialarchivo AS a
                    INNER JOIN material AS m ON m.idMaterial = a.idMaterial
                    WHERE m.idUsuario = ?";

            $consulta = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($consulta, "i", $idUsuario);
            mysqli_stmt_execute($consulta);
            $uso = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            $cantidadTotal = (int)$uso["TotalArchivos"] + count($archivosValidos);
            $bytesNuevos = array_sum(array_column($archivosValidos, "tamano"));

            if ($cantidadTotal > 5 || (int)$uso["TotalBytes"] + $bytesNuevos > 10 * 1024 * 1024) {
                throw new DomainException("Tu plan gratuito admite hasta 5 archivos y 10 MB en total.");
            }
        }

        // CREAMOS LA PUBLICACION

        $consulta = mysqli_prepare($conexion, "INSERT INTO material (idCurso, idUsuario, Titulo, Descripcion) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($consulta, "iiss", $idCurso, $idUsuario, $titulo, $descripcion);
        mysqli_stmt_execute($consulta);
        $idMaterial = mysqli_insert_id($conexion);
        mysqli_stmt_close($consulta);

        // GUARDAMOS LOS ARCHIVOS CON NOMBRES GENERADOS POR EL SERVIDOR

        if ($archivosValidos) {

            $directorioBase = dirname(__DIR__) . "/uploads/materiales";

            if (!is_dir($directorioBase) && !mkdir($directorioBase, 0775, true) && !is_dir($directorioBase)) {
                throw new RuntimeException("No pudimos preparar la carpeta de materiales.");
            }

            // BLOQUEAMOS EL ACCESO DIRECTO A LOS ARCHIVOS EN APACHE

            if (!is_file($directorioBase . "/.htaccess") && file_put_contents($directorioBase . "/.htaccess", "Require all denied\n") === false) {
                throw new RuntimeException("No pudimos proteger la carpeta de materiales.");
            }

            $directorioMaterial = $directorioBase . "/" . $idCurso . "/" . $idMaterial;

            if (!is_dir($directorioMaterial) && !mkdir($directorioMaterial, 0775, true) && !is_dir($directorioMaterial)) {
                throw new RuntimeException("No pudimos crear la carpeta de este material.");
            }

            foreach ($archivosValidos as $archivo) {

                $nombreServidor = bin2hex(random_bytes(16)) . "." . $archivo["extension"];
                $destino = $directorioMaterial . "/" . $nombreServidor;

                if (!move_uploaded_file($archivo["temporal"], $destino)) {
                    throw new RuntimeException("No pudimos guardar uno de los archivos.");
                }

                $archivosMovidos[] = $destino;
                $rutaRelativa = "uploads/materiales/" . $idCurso . "/" . $idMaterial . "/" . $nombreServidor;

                $consulta = mysqli_prepare($conexion, "INSERT INTO materialarchivo (idMaterial, NombreOriginal, Ruta, TamanoBytes) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($consulta, "issi", $idMaterial, $archivo["nombre"], $rutaRelativa, $archivo["tamano"]);
                mysqli_stmt_execute($consulta);
                mysqli_stmt_close($consulta);
            }
        }

        // GUARDAMOS LOS ENLACES

        foreach ($enlacesValidos as $orden => $url) {

            $consulta = mysqli_prepare($conexion, "INSERT INTO materialenlace (idMaterial, URL, Orden) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($consulta, "isi", $idMaterial, $url, $orden);
            mysqli_stmt_execute($consulta);
            mysqli_stmt_close($consulta);
        }

        mysqli_commit($conexion);

        return $idMaterial;

    } catch (Throwable $e) {

        mysqli_rollback($conexion);

        foreach ($archivosMovidos as $ruta) @unlink($ruta);
        if ($directorioMaterial && is_dir($directorioMaterial)) @rmdir($directorioMaterial);

        throw $e;

    } finally {
        mysqli_close($conexion);
    }
}


// OBTENEMOS LOS MATERIALES DEL CURSO

public static function obtenerMaterialesCurso($idCurso) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT m.idMaterial, m.Titulo, m.Descripcion, m.FechaPublicacion,
                       CONCAT_WS(' ', u.Nombre, u.Apellido) AS Autor,
                       (SELECT COUNT(*) FROM materialarchivo a WHERE a.idMaterial = m.idMaterial) AS TotalArchivos,
                       (SELECT COUNT(*) FROM materialenlace e WHERE e.idMaterial = m.idMaterial) AS TotalEnlaces
                FROM material m
                INNER JOIN usuario u ON u.idUsuario = m.idUsuario
                WHERE m.idCurso = ?
                ORDER BY m.FechaPublicacion DESC, m.idMaterial DESC";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $idCurso);
        mysqli_stmt_execute($consulta);

        $materiales = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
        mysqli_stmt_close($consulta);

        return $materiales;

    } finally {
        mysqli_close($conexion);
    }
}


// OBTENEMOS LOS ARCHIVOS Y ENLACES DE UN MATERIAL

public static function obtenerContenidoMaterialCurso($idCurso, $idMaterial) {

    $conexion = self::conectar();

    try {

        $consulta = mysqli_prepare($conexion, "SELECT idMaterial, Titulo, Descripcion, FechaPublicacion FROM material WHERE idCurso = ? AND idMaterial = ?");
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idMaterial);
        mysqli_stmt_execute($consulta);
        $material = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$material) return null;

        $consulta = mysqli_prepare($conexion, "SELECT idArchivo, NombreOriginal, TamanoBytes FROM materialarchivo WHERE idMaterial = ? ORDER BY idArchivo");
        mysqli_stmt_bind_param($consulta, "i", $idMaterial);
        mysqli_stmt_execute($consulta);
        $material["Archivos"] = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
        mysqli_stmt_close($consulta);

        $consulta = mysqli_prepare($conexion, "SELECT URL FROM materialenlace WHERE idMaterial = ? ORDER BY Orden, idEnlace");
        mysqli_stmt_bind_param($consulta, "i", $idMaterial);
        mysqli_stmt_execute($consulta);
        $material["Enlaces"] = mysqli_fetch_all(mysqli_stmt_get_result($consulta), MYSQLI_ASSOC);
        mysqli_stmt_close($consulta);

        return $material;

    } finally {
        mysqli_close($conexion);
    }
}


// OBTENEMOS LAS SOLICITUDES PENDIENTES DEL CURSO

public static function obtenerSolicitudesPendientesCurso($idCurso, $idCreador) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT i.idInscripcion, i.idUsuario, i.FechaInscripcion,
                       CONCAT_WS(' ', u.Nombre, u.Apellido) AS NombreCompleto
                FROM inscripcion AS i
                INNER JOIN usuario AS u ON u.idUsuario = i.idUsuario
                INNER JOIN curso AS c ON c.idCurso = i.idCurso
                WHERE i.idCurso = ?
                  AND c.idUsuario = ?
                  AND i.Estado = 'pendiente'
                ORDER BY i.FechaInscripcion ASC, i.idInscripcion ASC";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idCreador);
        mysqli_stmt_execute($consulta);

        $solicitudes = mysqli_fetch_all(
            mysqli_stmt_get_result($consulta),
            MYSQLI_ASSOC
        );

        mysqli_stmt_close($consulta);

        return $solicitudes;

    } finally {
        mysqli_close($conexion);
    }
}


// CONTAMOS LAS SOLICITUDES PARA MOSTRARLAS EN LA PESTAÑA

public static function contarSolicitudesPendientesCurso($idCurso, $idCreador) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT COUNT(*) AS Total
                FROM inscripcion AS i
                INNER JOIN curso AS c ON c.idCurso = i.idCurso
                WHERE i.idCurso = ?
                  AND c.idUsuario = ?
                  AND i.Estado = 'pendiente'";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idCreador);
        mysqli_stmt_execute($consulta);

        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        return (int)($fila["Total"] ?? 0);

    } finally {
        mysqli_close($conexion);
    }
}


// APROBAMOS O RECHAZAMOS UNA SOLICITUD

public static function resolverSolicitudCurso($idCurso, $idCreador, $idInscripcion, $accion, $motivo = "") {

    $idCurso = (int)$idCurso;
    $idCreador = (int)$idCreador;
    $idInscripcion = (int)$idInscripcion;
    $motivo = trim((string)$motivo);

    if (!in_array($accion, ["aprobar", "rechazar"], true)) {
        throw new DomainException("La acción solicitada no es válida.");
    }

    if ($idCurso < 1 || $idCreador < 1 || $idInscripcion < 1) {
        throw new DomainException("No pudimos identificar la solicitud.");
    }

    if (mb_strlen($motivo) > 500) {
        throw new DomainException("El motivo no puede superar los 500 caracteres.");
    }

    $conexion = self::conectar();
    mysqli_begin_transaction($conexion);

    try {

        // COMPROBAMOS QUE SEA EL CREADOR DEL CURSO

        $sql = "SELECT Cupo, FechaInicio, FechaFin
                FROM curso
                WHERE idCurso = ? AND idUsuario = ?
                FOR UPDATE";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idCurso, $idCreador);
        mysqli_stmt_execute($consulta);

        $curso = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$curso) {
            throw new DomainException(
                "Solamente el creador puede administrar estas solicitudes."
            );
        }

        // COMPROBAMOS QUE LA INSCRIPCION SIGA PENDIENTE

        $sql = "SELECT idUsuario, Estado
                FROM inscripcion
                WHERE idInscripcion = ? AND idCurso = ?
                FOR UPDATE";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idInscripcion, $idCurso);
        mysqli_stmt_execute($consulta);

        $inscripcion = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$inscripcion || $inscripcion["Estado"] !== "pendiente") {
            throw new DomainException(
                "Esta solicitud ya no está pendiente. Actualizá la página."
            );
        }

        if ($accion === "aprobar") {

            // VERIFICAMOS QUE NO SE SUPERE EL CUPO

            if ($curso["Cupo"] !== null) {

                $sql = "SELECT COUNT(*) AS Total
                        FROM inscripcion
                        WHERE idCurso = ? AND Estado = 'confirmada'";

                $consulta = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($consulta, "i", $idCurso);
                mysqli_stmt_execute($consulta);

                $ocupacion = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
                mysqli_stmt_close($consulta);

                if ((int)$ocupacion["Total"] >= (int)$curso["Cupo"]) {
                    throw new DomainException(
                        "No se puede aprobar: el curso ya alcanzó su cupo."
                    );
                }
            }

            // COMPROBAMOS EL PLAN DEL ALUMNO

            $idAlumno = (int)$inscripcion["idUsuario"];

            $sql = "SELECT PlanTipo, FechaVencimiento
                    FROM usuario
                    WHERE idUsuario = ?
                    FOR UPDATE";

            $consulta = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($consulta, "i", $idAlumno);
            mysqli_stmt_execute($consulta);

            $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
            mysqli_stmt_close($consulta);

            if (!$usuario) {
                throw new DomainException("El alumno ya no existe.");
            }

            $hoy = (new DateTimeImmutable(
                "today",
                new DateTimeZone("America/Argentina/San_Luis")
            ))->format("Y-m-d");

            $plan = strtolower(trim((string)$usuario["PlanTipo"]));

            $fechaVencimiento = substr(
                (string)($usuario["FechaVencimiento"] ?? ""),
                0,
                10
            );

            $planProVigente = $plan === "pro"
                && $fechaVencimiento >= $hoy;

            $esteCursoActivo =
                substr((string)$curso["FechaInicio"], 0, 10) <= $hoy
                && substr((string)$curso["FechaFin"], 0, 10) >= $hoy;

            // CONTROLAMOS EL LIMITE DEL PLAN GRATUITO

            if (!$planProVigente && $esteCursoActivo) {

                $sql = "SELECT COUNT(DISTINCT i.idCurso) AS Total
                        FROM inscripcion AS i
                        INNER JOIN curso AS c ON c.idCurso = i.idCurso
                        WHERE i.idUsuario = ?
                          AND i.Estado = 'confirmada'
                          AND DATE(c.FechaInicio) <= ?
                          AND DATE(c.FechaFin) >= ?";

                $consulta = mysqli_prepare($conexion, $sql);

                mysqli_stmt_bind_param(
                    $consulta,
                    "iss",
                    $idAlumno,
                    $hoy,
                    $hoy
                );

                mysqli_stmt_execute($consulta);

                $actuales = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
                mysqli_stmt_close($consulta);

                if ((int)$actuales["Total"] >= 3) {
                    throw new DomainException(
                        "El alumno del plan gratuito ya tiene tres cursos activos."
                    );
                }
            }
        }

        // GUARDAMOS LA DECISION EN LA INSCRIPCION

        $estado = $accion === "aprobar" ? "confirmada" : "rechazada";

        $motivoGuardar = $accion === "rechazar" && $motivo !== ""
            ? $motivo
            : null;

        $sql = "UPDATE inscripcion
                SET Estado = ?,
                    FechaConfirmacion =
                        CASE WHEN ? = 'confirmada' THEN NOW() ELSE NULL END,
                    MotivoRechazo = ?
                WHERE idInscripcion = ?
                  AND idCurso = ?
                  AND Estado = 'pendiente'";

        $consulta = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param(
            $consulta,
            "sssii",
            $estado,
            $estado,
            $motivoGuardar,
            $idInscripcion,
            $idCurso
        );

        mysqli_stmt_execute($consulta);

        $filas = mysqli_stmt_affected_rows($consulta);
        mysqli_stmt_close($consulta);

        if ($filas !== 1) {
            throw new DomainException("No pudimos actualizar la solicitud.");
        }

        // EL TRIGGER DE SQL CREA LA NOTIFICACION AUTOMATICAMENTE

        mysqli_commit($conexion);

        return $estado;

    } catch (Throwable $e) {

        mysqli_rollback($conexion);
        throw $e;

    } finally {
        mysqli_close($conexion);
    }
}


// OBTENEMOS LOS AVISOS DE INSCRIPCION DEL USUARIO

public static function obtenerAvisosInscripcionCurso($idUsuario, $limite = 12) {

    $conexion = self::conectar();

    $limite = max(1, min(30, (int)$limite));

    try {

        $sql = "SELECT n.idNotificacion, n.idCurso, n.Estado,
                       n.TextoReferencia, n.Leida, n.Fecha
                FROM notificacion AS n
                WHERE n.idUsuario = ?
                  AND n.Estado IN (8, 9, 10,11)
                ORDER BY n.Fecha DESC, n.idNotificacion DESC
                LIMIT ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idUsuario, $limite);
        mysqli_stmt_execute($consulta);

        $avisos = mysqli_fetch_all(
            mysqli_stmt_get_result($consulta),
            MYSQLI_ASSOC
        );

        mysqli_stmt_close($consulta);

        return $avisos;

    } finally {
        mysqli_close($conexion);
    }
}


// MARCAMOS LAS NOTIFICACIONES COMO LEIDAS

public static function abrirAvisoInscripcionCurso($idUsuario, $idNotificacion) {

    $conexion = self::conectar();

    try {

        $sql = "SELECT idCurso, Estado
                FROM notificacion
                WHERE idNotificacion = ?
                  AND idUsuario = ?
                  AND Estado IN (8, 9, 10,11)";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idNotificacion, $idUsuario);
        mysqli_stmt_execute($consulta);

        $aviso = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        mysqli_stmt_close($consulta);

        if (!$aviso) {
            throw new DomainException(
                "La notificación no existe o no te pertenece."
            );
        }

        $sql = "UPDATE notificacion
                SET Leida = 1
                WHERE idNotificacion = ? AND idUsuario = ?";

        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ii", $idNotificacion, $idUsuario);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);

        return $aviso;

    } finally {
        mysqli_close($conexion);
    }
}

}
