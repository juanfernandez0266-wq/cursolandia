-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-cursolandia.alwaysdata.net
-- Generation Time: Nov 16, 2026 at 07:38 PM
-- Server version: 11.8.9-MariaDB
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cursolandia__`
--

-- --------------------------------------------------------

--
-- Table structure for table `configuracionnotificacion`
--

CREATE TABLE `configuracionnotificacion` (
  `idUsuario` int(11) NOT NULL,
  `PreguntasCreadas` tinyint(1) NOT NULL DEFAULT 1,
  `Respuestas` tinyint(1) NOT NULL DEFAULT 1,
  `PreguntasCerradas` tinyint(1) NOT NULL DEFAULT 1,
  `RespuestasDestacadas` tinyint(1) NOT NULL DEFAULT 1,
  `PreguntasEliminadas` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `configuracionnotificacion`
--

INSERT INTO `configuracionnotificacion` (`idUsuario`, `PreguntasCreadas`, `Respuestas`, `PreguntasCerradas`, `RespuestasDestacadas`, `PreguntasEliminadas`) VALUES
(2, 0, 0, 0, 0, 0),
(3, 1, 1, 1, 1, 1),
(4, 1, 1, 1, 1, 1),
(5, 1, 1, 1, 1, 1),
(6, 1, 1, 1, 1, 1),
(10, 1, 1, 1, 1, 1),
(13, 1, 1, 1, 1, 1),
(14, 1, 1, 1, 1, 1),
(16, 1, 1, 1, 1, 1),
(18, 1, 1, 1, 1, 1),
(21, 1, 1, 1, 1, 1),
(25, 1, 1, 1, 1, 1),
(27, 1, 1, 1, 1, 1),
(32, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `curso`
--

CREATE TABLE `curso` (
  `idCurso` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Descripcion` text NOT NULL,
  `TipoAcceso` enum('publico','privado') NOT NULL,
  `TipoCurso` enum('gratuito','pago') NOT NULL,
  `Costo` decimal(10,2) DEFAULT NULL,
  `Cupo` int(11) DEFAULT NULL,
  `FechaInicio` date NOT NULL,
  `FechaFin` date NOT NULL,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `curso`
--

INSERT INTO `curso` (`idCurso`, `idUsuario`, `Titulo`, `Descripcion`, `TipoAcceso`, `TipoCurso`, `Costo`, `Cupo`, `FechaInicio`, `FechaFin`, `FechaCreacion`) VALUES
(6, 13, 'SALVÁ TU CELULAR: REPARACIÓN BÁSICA SIN GASTAR DE MÁS', '¿Se te rompió la pantalla o la batería ya no carga y los servicios técnicos te cobran una locura? En este curso vas a aprender a diagnosticar y solucionar los problemas más comunes de los smartphones actuales de manera completamente segura. Nos enfocaremos en tareas prácticas como cambiar módulos de pantalla, reemplazar pines de carga y entender el funcionamiento de la placa base sin necesidad de conocimientos previos en ingeniería o microelectrónica. A través de demostraciones prácticas, conocerás las herramientas esenciales que realmente necesitás comprar y cuáles son una pérdida de dinero. Es un espacio ideal para quienes quieren arreglar sus propios dispositivos, ayudar a familiares o incluso empezar a generar un ingreso extra desde la mesa de su casa. Trabajaremos paso a paso, perdiéndole el miedo a desarmar un equipo por primera vez y aplicando buenas prácticas para no dañar componentes sensibles.', 'publico', 'pago', 15000.00, 20, '2026-11-16', '2026-11-30', '2026-11-16 18:34:14'),
(7, 21, 'Tu bici siempre lista: Mantenimiento mecánico para ciclista', 'No dejes que una cadena rota o unos frenos largos te arruinen la salida del fin de semana. Este programa está diseñado para ciclistas urbanos y de montaña que quieren independizarse del taller mecánico para los ajustes de rutina. Aprenderás a calibrar los cambios traseros y delanteros, purgar frenos hidráulicos, parchar cámaras en tiempo récord y realizar una limpieza profunda que extenderá la vida útil de tu transmisión por miles de kilómetros. Veremos cómo identificar desgastes silenciosos antes de que se conviertan en roturas costosas y qué kit de emergencias llevar siempre en la mochila o alforja. Todo está explicado de forma sencilla, yendo al punto y ensuciándonos las manos desde el primer módulo. Perfecto tanto si usas la bicicleta para ir a la universidad todos los días como si haces senderismo los domingos.', 'privado', 'gratuito', NULL, NULL, '2026-11-16', '2026-12-30', '2026-11-16 18:38:03'),
(8, 10, 'Rescate retro: Restaurá equipos de audio y consolas viejas', '¿Encontraste la consola vieja de tu infancia o esa radio vintage de tu abuelo y están juntando polvo? 📻🕹️ ¡No las tires! 🛑 En este taller te enseño la magia del \"rescate retro\". Vamos a meter mano con el soldador de estaño ⚡, limpiar contactos a fondo, revivir placas muertas y entender cómo funcionaba la tecnología de la buena época. 📼✨ Sentí esa satisfacción increíble de enchufar un equipo de los 90s, apretar \"ON\" y que vuelva a la vida. ¡Unite y volvamos en el tiempo! ⏳🔥', 'publico', 'gratuito', NULL, 15, '2026-11-16', '2026-11-30', '2026-11-16 18:41:20'),
(9, 21, '🎧 DE CERO A TU PRIMER TRACK — Producí música electrónica desde tu habitación 🔥', 'Tenés una computadora, unos auriculares y una idea dando vueltas en la cabeza. Eso ya alcanza para empezar. 🎶 En este curso vas a transformar esa idea en tu primera producción completa de música electrónica. Aprenderás a construir ritmos, diseñar líneas de bajo, crear melodías, organizar secciones y darle movimiento a un track para que no suene repetitivo. 💻 También trabajaremos sobre automatizaciones, niveles y estructura musical para entender cómo una simple secuencia puede convertirse en una canción terminada. No necesitás un estudio profesional ni equipos carísimos: el objetivo es aprender a sacar el máximo provecho de las herramientas que ya tenés disponibles. Si alguna vez abriste un programa de producción y no supiste por dónde empezar, este curso está pensado exactamente para vos. 🚀', 'publico', 'pago', 25000.00, NULL, '2026-11-16', '2027-01-26', '2026-11-16 18:46:31'),
(10, 14, '🎹 TOCÁ EL PIANO SIN MIEDO — De tus primeras notas a canciones completas ✨', '¿Siempre quisiste sentarte frente a un piano y tocar algo que realmente suene bien? 🎶 Este curso está pensado para empezar desde cero, sin asumir que ya sabés leer partituras o conocés teoría musical. Vas a aprender a ubicarte en el teclado, reconocer acordes, coordinar ambas manos y acompañar melodías sencillas paso a paso. También vamos a trabajar ritmo, lectura básica y pequeños ejercicios para mejorar la independencia de los dedos. 🎹 La idea es avanzar tocando desde el comienzo, evitando que la teoría se convierta en una barrera. Si alguna vez viste a alguien tocar y pensaste “yo nunca podría hacer eso”, este curso viene justamente a romper esa idea. Con práctica constante y ejercicios cortos, vas a poder empezar a construir tus primeras canciones y ganar seguridad frente al instrumento.', 'publico', 'gratuito', NULL, 39, '2026-11-20', '2026-12-28', '2026-11-16 18:49:36'),
(11, 16, '🔊 MEZCLA PRO EN HOME STUDIO — Hacé que tus temas suenen GIGANTES 🎚️', '¿Terminaste una canción pero sentís que todavía suena apagada, desordenada o demasiado “casera”? 🎧 Ahí es donde entra la mezcla. En este curso vas a aprender a transformar una producción común en un tema mucho más claro, potente y equilibrado, entendiendo qué hacer con cada elemento y por qué. Vamos a trabajar con ecualización, compresión, panorama, reverberación y delay, pero sin caer en el clásico “mové perillas hasta que suene bien”. 🎚️ Vas a descubrir cómo hacer que una voz tenga presencia, cómo evitar que el bajo y el bombo se peleen entre sí y cómo crear profundidad sin llenar todo de efectos. También veremos técnicas para mejorar mezclas trabajando desde una habitación común y con equipo accesible. Ideal para quienes ya producen música y quieren dar ese salto donde el tema deja de sonar como una maqueta y empieza a sentirse realmente terminado. 🔥🎶', 'publico', 'gratuito', NULL, 40, '2026-11-19', '2026-11-30', '2026-11-16 18:53:28'),
(12, 18, '🎙️ AUDIO QUE ATRAPA — Editá podcasts y videos como un profesional ⚡', 'Podés tener el mejor video, entrevista o podcast del mundo, pero si el audio molesta, la gente se va. 🎧 En este curso vas a aprender a transformar grabaciones comunes en voces claras, equilibradas y agradables de escuchar. Trabajaremos sobre eliminación de ruido de fondo, corrección de volumen, ecualización, compresión y limpieza de problemas frecuentes como siseos, golpes o sonidos ambientales. 🎙️ También vas a aprender a organizar un flujo de trabajo más rápido para que editar un episodio o un video no termine convirtiéndose en horas interminables frente a la computadora. Veremos cómo darle mayor presencia y calidez a una voz y cómo recuperar grabaciones que parecían inutilizables. Está pensado para creadores de contenido, podcasters, entrevistadores y personas que trabajan con audio para redes sociales. No necesitás ser ingeniero de sonido: necesitás aprender a reconocer los problemas y saber exactamente qué herramienta utilizar para solucionarlos. 🚀', 'publico', 'gratuito', NULL, NULL, '2026-11-25', '2026-12-07', '2026-11-16 18:54:31'),
(13, 25, '📸 LA MAGIA DEL ROLLO — Descubrí la fotografía analógica 🎞️', '36 fotos. Sin borrar. Sin repetir diez veces la misma toma. Sin saber inmediatamente cómo salió. 🎞️ Esa incertidumbre es justamente parte de la magia de la fotografía analógica. En este curso vas a aprender a utilizar una cámara de película desde cero y descubrir por qué este formato sigue fascinando a fotógrafos de todo el mundo. 📷 Vamos a trabajar con exposición, enfoque manual, tipos de película, medición de luz y composición. También aprenderás qué revisar antes de comprar una cámara antigua y cómo interpretar esas pequeñas imperfecciones —grano, colores particulares o filtraciones de luz— que hacen única a cada fotografía. La propuesta no consiste solamente en apretar un botón: vas a aprender a observar, pensar la imagen y valorar cada disparo antes de hacerlo. Ideal para quienes quieren alejarse un poco de la fotografía instantánea del celular y experimentar un proceso mucho más pausado, físico y creativo. ✨', 'privado', 'gratuito', NULL, 30, '2026-11-16', '2026-11-26', '2026-11-16 18:55:51'),
(14, 27, '🏃 CORRÉ MÁS RÁPIDO, CANSATE MENOS — Técnica y nutrición para runners 🔥', 'Correr más kilómetros no siempre significa correr mejor. 🏃‍♂️ Muchas veces el verdadero progreso aparece cuando empezás a entender cómo entrenar, cómo moverte y cómo recuperar energía. En este curso vamos a trabajar sobre dos pilares fundamentales para mejorar tu rendimiento: técnica de carrera y nutrición deportiva. Aprenderás a organizar sesiones de velocidad, controlar ritmos, entender la importancia del descanso y detectar errores comunes que pueden hacer que gastes más energía de la necesaria. 💪 También hablaremos de hidratación, alimentación antes y después de los entrenamientos, geles deportivos y estrategias para afrontar distancias más largas sin llegar completamente agotado. La idea no es convertir cada salida en una competencia, sino aprender a entrenar con un propósito. Ideal para quienes ya pueden completar algunos kilómetros y quieren mejorar tiempos, aumentar distancia o simplemente terminar cada entrenamiento sintiéndose mejor. 🥤🏅', 'publico', 'pago', 18000.00, NULL, '2026-11-16', '2026-11-18', '2026-11-16 18:57:25'),
(15, 32, '🎂 PASTELERÍA INSTAGRAMEABLE — Tortas que enamoran y venden solas ✨', 'Una torta puede ser deliciosa, pero antes de probarla lo primero que entra por los ojos es su presentación. 🎂✨ En este curso vas a aprender técnicas modernas de decoración para transformar preparaciones simples en tortas visualmente impactantes. Trabajaremos con buttercream, bordes definidos, combinaciones de colores, texturas, flores comestibles y pequeños detalles capaces de cambiar completamente el resultado final. 🌸 También vas a aprender a pensar el diseño antes de empezar a decorar y a combinar distintos elementos sin sobrecargar la composición. Y como hoy una buena presentación también vive en redes sociales, veremos principios básicos para fotografiar tus creaciones utilizando solamente un celular y luz natural. 📱 Está pensado tanto para aficionados como para pequeños emprendedores que quieran mejorar la apariencia de sus productos, desarrollar un estilo reconocible y aumentar el valor percibido de cada creación. Porque una buena torta empieza a llamar la atención mucho antes del primer bocado. 🍰🔥', 'privado', 'pago', 22000.00, 20, '2026-11-21', '2026-11-30', '2026-11-16 18:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `cursoimagen`
--

CREATE TABLE `cursoimagen` (
  `idImagen` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `Ruta` varchar(255) NOT NULL,
  `Orden` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cursoimagen`
--

INSERT INTO `cursoimagen` (`idImagen`, `idCurso`, `Ruta`, `Orden`) VALUES
(4, 6, 'uploads/cursos/6/f5537eea23d7f1fab68456b272d8c55c.jpg', 0),
(5, 6, 'uploads/cursos/6/46206a03774573573ab4bdc10533ffe5.jpg', 1),
(6, 7, 'uploads/cursos/7/8f11f7a84e7870fb096588a92b0cd6ea.jpg', 0),
(7, 7, 'uploads/cursos/7/b8cd108f8d82b15887cf8cacbb813bb5.jpg', 1),
(8, 8, 'uploads/cursos/8/5ba8f4f2b3ec0cc26721c35b5af8c5a1.jpg', 0),
(9, 8, 'uploads/cursos/8/9e59a3cd9c4c3bf493b2c570e3a77332.jpg', 1),
(10, 9, 'uploads/cursos/9/e04aa9ee9f3c5c3c669fa81aa5df3cba.jpg', 0),
(11, 9, 'uploads/cursos/9/6b86f6a4499fcc3709802f3e5cbf39ee.jpg', 1),
(12, 10, 'uploads/cursos/10/8dbe2a2ed2907a40aeb8fee809d5d77a.jpg', 0),
(13, 10, 'uploads/cursos/10/3da3a2a7f61507a833a988776890dc74.jpg', 1),
(14, 11, 'uploads/cursos/11/c8b35c115c33b6093456485f4561dc9c.jpg', 0),
(15, 11, 'uploads/cursos/11/25ed99eae125feb683948725536c6d94.jpg', 1),
(16, 12, 'uploads/cursos/12/c18e5aaedcb4356dc4e44b3804605f16.jpg', 0),
(17, 12, 'uploads/cursos/12/e781d2e2c4f7d03e6e147526d69fea36.jpg', 1),
(18, 13, 'uploads/cursos/13/b44704e40e147b446199a3a4a945d07a.jpg', 0),
(19, 13, 'uploads/cursos/13/2dab8951abcb94505e4268bf50df956a.jpg', 1),
(20, 14, 'uploads/cursos/14/8887e21de9061117d684c0357fabfe52.jpg', 0),
(21, 14, 'uploads/cursos/14/3a1338f19d85b455fe63fc991136e1c2.jpg', 1),
(22, 15, 'uploads/cursos/15/67d6a07a5f80f7bba6addc04c800f1e8.jpg', 0),
(23, 15, 'uploads/cursos/15/5471970461e0814c712d40263b688e93.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cursointeres`
--

CREATE TABLE `cursointeres` (
  `idCurso` int(11) NOT NULL,
  `idInteres` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cursointeres`
--

INSERT INTO `cursointeres` (`idCurso`, `idInteres`) VALUES
(6, 1),
(9, 1),
(11, 1),
(12, 1),
(14, 1),
(15, 1),
(9, 2),
(11, 2),
(6, 3),
(8, 3),
(12, 3),
(14, 4),
(14, 5);

-- --------------------------------------------------------

--
-- Table structure for table `inscripcion`
--

CREATE TABLE `inscripcion` (
  `idInscripcion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `Estado` enum('pendiente','confirmada','rechazada') NOT NULL,
  `FechaInscripcion` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaConfirmacion` datetime DEFAULT NULL,
  `MotivoRechazo` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `inscripcion`
--
DELIMITER $$
CREATE TRIGGER `cursolandia_solicitud_au` AFTER UPDATE ON `inscripcion` FOR EACH ROW INSERT INTO notificacion
    (idUsuario, idUsuarioActor, idCurso, Estado, TextoReferencia, Leida)
SELECT
    CASE
        WHEN NEW.Estado = 'pendiente' THEN c.idUsuario
        ELSE NEW.idUsuario
    END,

    CASE
        WHEN NEW.Estado = 'pendiente' THEN NEW.idUsuario
        ELSE c.idUsuario
    END,

    NEW.idCurso,

    CASE NEW.Estado
        WHEN 'pendiente' THEN 8
        WHEN 'confirmada' THEN 9
        ELSE 10
    END,

    LEFT(
        CASE NEW.Estado
            WHEN 'pendiente' THEN CONCAT(
                'Nueva solicitud de inscripción en: ', c.Titulo
            )

            WHEN 'confirmada' THEN CONCAT(
                'Tu inscripción fue aprobada: ', c.Titulo
            )

            ELSE CONCAT(
                'Tu inscripción fue rechazada: ', c.Titulo,
                IF(
                    NEW.MotivoRechazo IS NOT NULL
                    AND NEW.MotivoRechazo <> '',
                    CONCAT('. Motivo: ', NEW.MotivoRechazo),
                    ''
                )
            )
        END,
        255
    ),

    0

FROM curso AS c
WHERE c.idCurso = NEW.idCurso
  AND c.idUsuario <> NEW.idUsuario
  AND NEW.Estado <> OLD.Estado
  AND (
        (
            NEW.Estado = 'pendiente'
            AND OLD.Estado IN ('rechazada', 'confirmada')
        )
        OR
        (
            OLD.Estado = 'pendiente'
            AND NEW.Estado IN ('confirmada', 'rechazada')
        )
      )
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `interes`
--

CREATE TABLE `interes` (
  `idInteres` int(11) NOT NULL,
  `NombreInteres` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interes`
--

INSERT INTO `interes` (`idInteres`, `NombreInteres`) VALUES
(1, 'Aficiones'),
(5, 'Deportes'),
(2, 'Musica'),
(4, 'Salud'),
(3, 'Tecnologia');

-- --------------------------------------------------------

--
-- Table structure for table `material`
--

CREATE TABLE `material` (
  `idMaterial` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `Titulo` varchar(180) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `FechaPublicacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `material`
--
DELIMITER $$
CREATE TRIGGER `cursolandia_material_ai` AFTER INSERT ON `material` FOR EACH ROW INSERT INTO notificacion (
    idUsuario,
    idUsuarioActor,
    idCurso,
    Estado,
    TextoReferencia,
    Leida
)
SELECT
    i.idUsuario,
    NEW.idUsuario,
    NEW.idCurso,
    11,
    LEFT(CONCAT('Nuevo material: ', NEW.Titulo), 255),
    0
FROM inscripcion AS i
WHERE i.idCurso = NEW.idCurso
  AND i.Estado = 'confirmada'
  AND i.idUsuario <> NEW.idUsuario
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `materialarchivo`
--

CREATE TABLE `materialarchivo` (
  `idArchivo` int(11) NOT NULL,
  `idMaterial` int(11) NOT NULL,
  `NombreOriginal` varchar(255) NOT NULL,
  `Ruta` varchar(500) NOT NULL,
  `TamanoBytes` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materialenlace`
--

CREATE TABLE `materialenlace` (
  `idEnlace` int(11) NOT NULL,
  `idMaterial` int(11) NOT NULL,
  `URL` text NOT NULL,
  `Orden` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificacion`
--

CREATE TABLE `notificacion` (
  `idNotificacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `idUsuarioActor` int(11) DEFAULT NULL,
  `idCurso` int(11) DEFAULT NULL,
  `idPregunta` int(11) DEFAULT NULL,
  `idRespuesta` int(11) DEFAULT NULL,
  `Estado` tinyint(4) NOT NULL,
  `TextoReferencia` varchar(255) DEFAULT NULL,
  `Leida` tinyint(1) NOT NULL DEFAULT 0,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notificacion`
--

INSERT INTO `notificacion` (`idNotificacion`, `idUsuario`, `idUsuarioActor`, `idCurso`, `idPregunta`, `idRespuesta`, `Estado`, `TextoReferencia`, `Leida`, `Fecha`) VALUES
(6, 2, 2, 9999, 101, NULL, 1, '¿Cómo funcionan las sesiones en PHP?', 1, '2026-10-29 16:51:50'),
(7, 2, 2, 9999, 102, NULL, 2, '¿Cuándo conviene utilizar CSS Grid?', 1, '2026-10-29 16:41:50'),
(8, 2, 2, 9999, 103, 201, 3, '¿Cuál es la diferencia entre Grid y Flexbox?', 1, '2026-10-29 16:31:50'),
(9, 2, 2, 9999, 104, 202, 4, '¿Cómo se relacionan las claves foráneas?', 1, '2026-10-29 16:21:50'),
(10, 2, NULL, 9999, 105, NULL, 5, '¿Cómo se representa una relación entre actores?', 1, '2026-10-29 16:11:50'),
(11, 2, 2, 9999, 106, NULL, 5, '¿Es mejor utilizar Flexbox o Grid para una galería?', 1, '2026-10-28 15:06:02'),
(12, 2, 2, 9999, 107, NULL, 7, '¿Cuándo se entrega el trabajo práctico?', 1, '2026-10-28 11:06:02'),
(13, 2, 2, 9999, 108, 203, 6, '¿Para qué sirve una clave primaria?', 1, '2026-10-27 17:06:02'),
(14, 2, 2, 9999, 109, NULL, 1, '¿Qué diferencia hay entre include y extend?', 1, '2026-10-26 17:06:02'),
(15, 2, 2, 9999, 110, 204, 3, '¿Cómo se utiliza password_hash en PHP?', 1, '2026-10-25 14:06:02'),
(16, 4, 2, 3, NULL, NULL, 9, 'Tu inscripción fue aprobada: Pepito', 1, '2026-11-16 02:36:26'),
(17, 4, 2, 2, NULL, NULL, 10, 'Tu inscripción fue rechazada: asdasdasda. Motivo: No hay cupo suficiente. mil disculpas', 1, '2026-11-16 02:38:41'),
(18, 2, 4, 5, NULL, NULL, 11, 'Nuevo material: asdasd', 1, '2026-11-16 02:49:49'),
(19, 4, 2, 5, 12, NULL, 1, '12314312', 1, '2026-11-16 03:03:27'),
(20, 4, 2, 5, 13, NULL, 1, '777777777', 1, '2026-11-16 03:05:49'),
(21, 2, 2, 5, 13, NULL, 2, '777777777', 0, '2026-11-16 03:05:49'),
(23, 4, 2, 5, 14, NULL, 1, '12312312', 1, '2026-11-16 03:06:06'),
(24, 2, 2, 5, 14, NULL, 2, '12312312', 0, '2026-11-16 03:06:06'),
(26, 4, NULL, 5, 9, 5, 6, 'profe por que esto es asi  y no asi?', 1, '2026-11-16 03:07:09'),
(27, 2, NULL, 5, 9, NULL, 5, 'profe por que esto es asi  y no asi?', 0, '2026-11-16 03:07:09'),
(28, 2, 4, 5, NULL, NULL, 11, 'Nuevo material: PRUEBA DE FFOTO', 1, '2026-11-16 03:07:39'),
(29, 4, 2, 5, 15, NULL, 1, 'por que la vida es asi', 1, '2026-11-16 03:08:58'),
(30, 2, 2, 5, 15, NULL, 2, 'por que la vida es asi', 0, '2026-11-16 03:08:58'),
(32, 2, 4, 5, NULL, NULL, 7, 'profe por que esto es asi  y no asi?', 0, '2026-11-16 03:09:49'),
(33, 2, 4, 5, 15, NULL, 12, 'por que la vida es asi', 1, '2026-11-16 03:09:56'),
(34, 2, 4, 5, 15, 8, 3, 'por que la vida es asi', 0, '2026-11-16 03:10:39'),
(35, 4, NULL, 5, 15, 8, 6, 'por que la vida es asi', 1, '2026-11-16 03:11:09'),
(36, 2, NULL, 5, 15, NULL, 5, 'por que la vida es asi', 0, '2026-11-16 03:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `pregunta`
--

CREATE TABLE `pregunta` (
  `idPregunta` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `idMaterial` int(11) DEFAULT NULL,
  `idUsuario` int(11) NOT NULL,
  `Titulo` varchar(180) NOT NULL,
  `Contenido` text NOT NULL,
  `Destacada` tinyint(1) NOT NULL DEFAULT 0,
  `Estado` enum('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `pregunta`
--
DELIMITER $$
CREATE TRIGGER `cursolandia_foro_pregunta_ai` AFTER INSERT ON `pregunta` FOR EACH ROW INSERT INTO notificacion
    (idUsuario, idUsuarioActor, idCurso, idPregunta,
     Estado, TextoReferencia, Leida)

-- NOTIFICAMOS AL CREADOR DEL CURSO

SELECT
    c.idUsuario,
    NEW.idUsuario,
    NEW.idCurso,
    NEW.idPregunta,
    1,
    LEFT(NEW.Titulo, 255),
    0
FROM curso AS c
WHERE c.idCurso = NEW.idCurso
  AND c.idUsuario <> NEW.idUsuario

UNION ALL

-- NOTIFICAMOS AL ALUMNO QUE CREO LA PREGUNTA

SELECT
    NEW.idUsuario,
    NEW.idUsuario,
    NEW.idCurso,
    NEW.idPregunta,
    2,
    LEFT(NEW.Titulo, 255),
    0
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cursolandia_foro_pregunta_au` AFTER UPDATE ON `pregunta` FOR EACH ROW INSERT INTO notificacion
    (idUsuario, idUsuarioActor, idCurso, idPregunta,
     Estado, TextoReferencia, Leida)

-- PREGUNTA CERRADA

SELECT
    NEW.idUsuario,
    NULL,
    NEW.idCurso,
    NEW.idPregunta,
    5,
    LEFT(NEW.Titulo, 255),
    0
FROM curso AS c
WHERE c.idCurso = NEW.idCurso
  AND OLD.Estado <> 'cerrada'
  AND NEW.Estado = 'cerrada'

UNION ALL

-- PREGUNTA DESTACADA POR EL CREADOR

SELECT
    NEW.idUsuario,
    c.idUsuario,
    NEW.idCurso,
    NEW.idPregunta,
    12,
    LEFT(NEW.Titulo, 255),
    0
FROM curso AS c
WHERE c.idCurso = NEW.idCurso
  AND COALESCE(OLD.Destacada, 0) <> 1
  AND NEW.Destacada = 1
  AND c.idUsuario <> NEW.idUsuario
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cursolandia_foro_pregunta_bd` BEFORE DELETE ON `pregunta` FOR EACH ROW INSERT INTO notificacion
    (idUsuario, idUsuarioActor, idCurso, idPregunta,
     Estado, TextoReferencia, Leida)
SELECT
    OLD.idUsuario,
    c.idUsuario,
    OLD.idCurso,
    NULL,
    7,
    LEFT(OLD.Titulo, 255),
    0
FROM curso AS c
WHERE c.idCurso = OLD.idCurso
  AND c.idUsuario <> OLD.idUsuario
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `respuesta`
--

CREATE TABLE `respuesta` (
  `idRespuesta` int(11) NOT NULL,
  `idPregunta` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `Contenido` text NOT NULL,
  `Aceptada` tinyint(1) NOT NULL DEFAULT 0,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaEdicion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `respuesta`
--
DELIMITER $$
CREATE TRIGGER `cursolandia_foro_respuesta_ai` AFTER INSERT ON `respuesta` FOR EACH ROW INSERT INTO notificacion
    (idUsuario, idUsuarioActor, idCurso, idPregunta,
     idRespuesta, Estado, TextoReferencia, Leida)

-- AVISAMOS AL AUTOR DE LA PREGUNTA

SELECT
    p.idUsuario,
    NEW.idUsuario,
    p.idCurso,
    NEW.idPregunta,
    NEW.idRespuesta,
    3,
    LEFT(p.Titulo, 255),
    0
FROM pregunta AS p
WHERE p.idPregunta = NEW.idPregunta
  AND p.idUsuario <> NEW.idUsuario

UNION ALL

-- AVISAMOS AL CREADOR DEL CURSO SI NO ES EL AUTOR NI QUIEN RESPONDE

SELECT
    c.idUsuario,
    NEW.idUsuario,
    p.idCurso,
    NEW.idPregunta,
    NEW.idRespuesta,
    4,
    LEFT(p.Titulo, 255),
    0
FROM pregunta AS p
INNER JOIN curso AS c ON c.idCurso = p.idCurso
WHERE p.idPregunta = NEW.idPregunta
  AND c.idUsuario <> NEW.idUsuario
  AND c.idUsuario <> p.idUsuario
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cursolandia_foro_respuesta_au` AFTER UPDATE ON `respuesta` FOR EACH ROW INSERT INTO notificacion
    (idUsuario, idUsuarioActor, idCurso, idPregunta,
     idRespuesta, Estado, TextoReferencia, Leida)
SELECT
    NEW.idUsuario,
    NULL,
    p.idCurso,
    NEW.idPregunta,
    NEW.idRespuesta,
    6,
    LEFT(p.Titulo, 255),
    0
FROM pregunta AS p
WHERE p.idPregunta = NEW.idPregunta
  AND COALESCE(OLD.Aceptada, 0) <> 1
  AND NEW.Aceptada = 1
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `solicitudplanpro`
--

CREATE TABLE `solicitudplanpro` (
  `idSolicitud` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `Mensaje` text DEFAULT NULL,
  `Comprobante` varchar(255) NOT NULL,
  `NombreComprobante` varchar(255) NOT NULL,
  `Estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `FechaSolicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `MesesAprobados` tinyint(3) UNSIGNED DEFAULT NULL,
  `FechaResolucion` datetime DEFAULT NULL,
  `FechaVencimientoAsignada` date DEFAULT NULL,
  `MotivoRechazo` varchar(500) DEFAULT NULL,
  `idAdministrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solicitudplanpro`
--

INSERT INTO `solicitudplanpro` (`idSolicitud`, `idUsuario`, `Mensaje`, `Comprobante`, `NombreComprobante`, `Estado`, `FechaSolicitud`, `MesesAprobados`, `FechaResolucion`, `FechaVencimientoAsignada`, `MotivoRechazo`, `idAdministrador`) VALUES
(3, 2, 'asdasd', '8243c62497fe30e336799749708a5e87.png', 'Cursolandia_ solicitud de Plan Pro.png', 'pendiente', '2026-11-10 20:01:25', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `Nombre` varchar(20) NOT NULL,
  `Apellido` varchar(20) NOT NULL,
  `FechaNacimiento` date DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `NombreUsuario` varchar(20) NOT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `FotoPerfil` varchar(255) DEFAULT NULL,
  `PlanTipo` varchar(20) NOT NULL DEFAULT 'gratuito',
  `Antecedentes` varchar(500) DEFAULT NULL,
  `FechaVencimiento` date DEFAULT NULL,
  `Rol_Usuario` varchar(20) NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `Nombre`, `Apellido`, `FechaNacimiento`, `email`, `NombreUsuario`, `Contraseña`, `FotoPerfil`, `PlanTipo`, `Antecedentes`, `FechaVencimiento`, `Rol_Usuario`) VALUES
(1, 'Juan', 'Perez', NULL, 'juan@gmail.com', 'juan123', '$2y$10$3lr5ROfl3XhHHuvpwBVSMuF8fsGJdDxxRzuC2IRQzcNO2x42fGOdq', NULL, 'gratuito', NULL, NULL, 'administrador'),
(2, 'Pedro', 'Gomez', '2008-02-06', 'pedro@gmail.com', 'pedro123', '$2y$10$8GyfJsOaqBraQnBfFdxMt.YkEEgVDpXwCY5BTC/TNQhWBgsuQyHKq', 'uploads/perfiles/2_1794795195.jpeg', 'gratuito', 'lkjkj', NULL, 'usuario'),
(3, 'Julieta', 'Rosales', NULL, 'julieta@gmail.com', 'Juli', '$2y$10$JJJ20LQSHEXfYA4FpocJXuqQUFYnzcSEPSsVjaXrUJrnliCOuhCDi', NULL, 'gratuito', NULL, NULL, 'usuario'),
(4, 'Roberto', 'Perez', NULL, 'roberto@gmail.com', 'robertJR', '$2y$10$Ybt7eQwi6j77dQGvNX7aD.PvWSOMM.tOnWjHpW6jw3naK74eUyKZ2', NULL, 'gratuito', NULL, NULL, 'usuario'),
(5, 'Lucia', 'Cabrera', '2008-10-31', 'lucia@gmail.com', 'Luci200', '$2y$10$s0q1QG9HQF15l64cvKuEyeulXCK5f0bnBMrG3FGA2M4pl//IIfTA2', 'uploads/perfiles/5_1793564913.jpeg', 'gratuito', 'me gustan los cursos de estilitas', NULL, 'usuario'),
(6, 'Emma', 'Gomez', '2008-03-11', 'ema@gmail.com', 'emmita', '$2y$10$gb33j8SFY/pl4PuuJKVYmeA8P6Nf1Edz9SiN5Wj78780D8HhkOl/K', 'uploads/perfiles/6_1793757024.jpg', 'gratuito', 'le pegue a una señora', NULL, 'usuario'),
(7, 'Mateo', 'Silva', '1992-04-15', 'mateo.silva@test.com', 'mateo.silva', '$2y$12$ePz1/I01Bbf6uS2Q2f5gPeQbwTHPxY4y6b.q7xFk47xVZhEravwI2', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Trabaja como cajero en un supermercado y en su tiempo libre toca la batería. Quiere aprender producción musical.', NULL, 'usuario'),
(8, 'Valentina', 'Rojas', '1988-08-22', 'valerojas88@test.com', 'valerojas88', '$2y$12$gCxQYhekLI2Tz1JIDWUr4.24YKyv3Jvvbun2bR7UIGtF2VrBb5ghW', 'imagenes/perfil_predeterminado.png', 'pro', 'Enfermera en un hospital público. Le apasiona la nutrición deportiva y la jardinería orgánica.', '2027-05-16', 'usuario'),
(9, 'Lucas', 'Benítez', '2001-11-05', 'lucas_b@test.com', 'lucas_b', '$2y$12$R.m.EkGqG7uqqSEuT1UwqekCUwqF5pRruU9KirqMYYuvjr9G5nvpq', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Estudiante de arquitectura. Busca formarse en herramientas de diseño 3D para complementar su carrera universitaria.', NULL, 'usuario'),
(10, 'Camila', 'Flores', '1995-01-30', 'camiflores.art@test.com', 'camiflores.art', '$2y$12$jmIg6LSpF1s1ZDyA56uH9uEmY/AZnhknMn12ADZA3cilEIWmEkx7m', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Diseñadora gráfica freelance. Disfruta de la fotografía analógica y colecciona discos de vinilo de los años 80.', NULL, 'usuario'),
(11, 'Diego', 'Mendoza', '1980-07-12', 'diego.run@test.com', 'diego.run', '$2y$12$mafs7PWOivpZVEZF77eZS.josvqYcuNozcGJEoBfSEu70FbYFhIVK', 'imagenes/perfil_predeterminado.png', 'pro', 'Entrenador amateur de atletismo y empleado administrativo. Está buscando aprender sobre nutrición para el rendimiento de sus atletas.', '2027-05-16', 'usuario'),
(12, 'Sofía', 'Castro', '1997-09-19', 'sofi.c@test.com', 'sofia.cocina', '$2y$12$S8UFNvYnuPKJKYZHsDpJCug8AnCW4gFIL5pjGxaIi42Rne9j4BphG', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Emprendedora gastronómica que maneja su propia pastelería desde casa. Realizó varios cursos de decoración de pasteles.', NULL, 'usuario'),
(13, 'Javier', 'Torres', '1990-03-25', 'javi_tec@test.com', 'javi_tec', '$2y$12$8trXVYdHadN77m4cewxLze7YxtcyHOBCQrtD0mKJJ6TUlDnLci2ta', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Técnico electrónico que repara dispositivos móviles. Es muy curioso, practica ciclismo, toca la guitarra y lee novelas de ciencia ficción.', NULL, 'usuario'),
(14, 'Martina', 'Luna', '2003-12-08', 'martiluna@test.com', 'marti.luna', '$2y$12$UFv80ZBARJnp3L2jQ.kPNeR/4Ocsukmk0/0FAC88Iv6x3kZBUbKvm', 'imagenes/perfil_predeterminado.png', 'pro', 'Estudiante secundaria recién egresada. Quiere entrar al conservatorio y da clases particulares de piano a niños de su barrio.', '2027-05-16', 'usuario'),
(15, 'Alejandro', 'Gómez', '1993-02-14', 'ale.gomez93@test.com', 'ale.gol', '$2y$12$tPtr09eJcTOUPDEGvdaf1.0WYstPC1paiq3Wrq7e2aD2/Wbr.aIdy', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Vendedor en una tienda de repuestos de autos. Juega fútbol amateur en un torneo local todos los fines de semana.', NULL, 'usuario'),
(16, 'Lucía', 'Fernández', '1985-06-03', 'lucia.fer@test.com', 'lu_docente', '$2y$12$E4neklkjWimgx6mWLGYSMeRazw6abJ/eD4ocJ6JgelOo1PqJBvJv6', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Maestra de primaria con más de 10 años de experiencia frente al aula. Dedica sus fines de semana a la pintura en acuarela y la meditación.', NULL, 'usuario'),
(17, 'Tomás', 'Quiroga', '1989-10-11', 'tomas.q89@test.com', 'tomas.q89', '$2y$12$mf6ajufn5dqF.dYPM.c9S.sNBY6nA5KeP93i/PaluqSb3fHc7e8Ae', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Cerrajero de oficio con su propio taller. Le gusta desarmar consolas antiguas y restaurar muebles de madera abandonados.', NULL, 'usuario'),
(18, 'Florencia', 'Vega', '1991-02-28', 'flor.vega@test.com', 'flor.yoga', '$2y$12$rUCgnbltYQYeaqpSIkLVBOjaTOzvu6z97wN3FQCr5wzN7u40C02xK', 'imagenes/perfil_predeterminado.png', 'pro', 'Instructora de yoga y masajista terapéutica. Se interesa por terapias alternativas y el bienestar integral de sus pacientes.', '2027-05-16', 'usuario'),
(19, 'Agustín', 'Morales', '1999-05-17', 'agus_mor@test.com', 'agus_mor', '$2y$12$.RuyUGbffig2qlv72Aydr.cyJjx10HOh6e5qsZW0TDmzXLZ6juTTu', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Repartidor de mensajería en moto durante el día. Por las noches edita videos musicales para bandas locales de rock independiente.', NULL, 'usuario'),
(20, 'Mariana', 'Ríos', '1982-09-04', 'm.rios@test.com', 'mariana.rios', '$2y$12$j/Wq5MEjVxPq4IOeRQwIoOJwrAehzol5dNrnuKeNl.zX7xc/0c9Dy', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Empleada bancaria que busca salir de la rutina diaria. Recientemente comenzó a tomar clases de costura y diseño de indumentaria.', NULL, 'usuario'),
(21, 'Nicolás', 'Herrera', '1996-11-21', 'nico.herrera@test.com', 'nico.h', '$2y$12$gjWtl3caDvmb/RBVTjMud.EiTVHZDDA5zENqabym7f4uE07xGXAJq', 'imagenes/perfil_predeterminado.png', 'pro', 'Trabaja en atención al cliente para una empresa de telefonía. Es un apasionado de los sintetizadores y la producción de música electrónica.', '2027-05-16', 'usuario'),
(22, 'Valeria', 'Navarro', '1994-01-10', 'vale_nav@test.com', 'vale_nav', '$2y$12$o9O6xvJgFZCwnmuoiTPFHuoefb1U6QeNGCQ5Iee0kkFTvGKK9bIiK', 'imagenes/perfil_predeterminado.png', 'pro', 'Veterinaria dedicada a pequeños animales. En su tiempo libre hace trekking de alta montaña y fotografía de naturaleza.', '2027-05-16', 'usuario'),
(23, 'Gonzalo', 'Peralta', '1975-08-07', 'gonza.pesca@test.com', 'gonza.pesca', '$2y$12$Ghw94Cnbc1FlRLMaTb8PF.5ZGqEiVPm3vvWDF7dnhPX11PrK7Pxsa', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Chofer de transporte de larga distancia. Su mayor pasatiempo durante los francos es la pesca deportiva y la carpintería.', NULL, 'usuario'),
(24, 'Romina', 'Suárez', '1987-12-14', 'romi.s@test.com', 'romi.suarez', '$2y$12$Uoj5b1F8somlLsxMHFjrTO6NhUGoOHSngN3lQdTEVa5vVpfFRj8J2', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Recepcionista en un hotel céntrico. Canta en un coro comunitario y está aprendiendo a leer partituras por su cuenta.', NULL, 'usuario'),
(25, 'Julián', 'Domínguez', '2000-04-02', 'julidominguez@test.com', 'juli_d', '$2y$12$iJWh1s60zJFibBmDQZtva.1dMUHbOpckNtBV6rlO/Y1bI8nqlCNGa', 'imagenes/perfil_predeterminado.png', 'pro', 'Estudiante de comunicación social. Disfruta haciendo podcasts sobre crímenes reales y editando sus propios audios.', '2027-05-16', 'usuario'),
(26, 'Carolina', 'Pinto', '1998-06-26', 'caro.pinto@test.com', 'caro.pinto', '$2y$12$kxaZEnFdoxt3o4YGeVUzvu9tx.a/Z.4khJytznjdCJY3UtHctNqQa', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Estudiante de kinesiología y jugadora de hockey sobre césped. Le interesa la rehabilitación deportiva y el entrenamiento funcional.', NULL, 'usuario'),
(27, 'Facundo', 'Arias', '1986-03-09', 'facu_arias@test.com', 'facu.arias', '$2y$12$Znu3HTujoID/rXGfVCIfnOQLARs6T4OjYWwWwA7BtujjCMq8ubLUS', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Electricista matriculado que busca actualizarse profesionalmente. Está interesado en aprender sobre domótica y automatización de casas.', NULL, 'usuario'),
(28, 'Daniela', 'Vargas', '1992-05-15', 'danivargas@test.com', 'dani_v', '$2y$12$CPIviZPd0/QlkHa7h5QZpOvgItPxRu9SzV.4jImHvw5we0OB7EE2W', 'imagenes/perfil_predeterminado.png', 'pro', 'Peluquera con salón propio. Juega al básquet en un club local y escucha rock nacional de los 90 mientras atiende clientes.', '2027-05-16', 'usuario'),
(29, 'Martín', 'Ceballos', '1981-07-29', 'mceballos@test.com', 'martin_c', '$2y$12$D2eLaPQJOA7lOJFTQwDT6uwWaBIOitYQay9.0/sR.Jc/kCzXi7J6a', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Empleado del rubro de la seguridad privada. Se mantiene en forma corriendo maratones locales todos los meses.', NULL, 'usuario'),
(30, 'Paula', 'Giménez', '1995-10-18', 'paula.gimenez@test.com', 'paula.gim', '$2y$12$HjZehnRqL3uyVMbrDFCd0enz1QwvvZ8rnw53uR0mK6uxhA.dlhOkm', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Secretaria clínica en un centro oftalmológico. Busca aprender herramientas de gestión de datos para agilizar el sistema de turnos.', NULL, 'usuario'),
(31, 'Ignacio', 'Orellana', '2002-01-22', 'nacho.o@test.com', 'nacho_ore', '$2y$12$MyUsWWL41loS5mLDlzl6s.6MN8Z1EJgsFvPU3eR4yn8zpdfgwvkMe', 'imagenes/perfil_predeterminado.png', 'pro', 'Barbero joven que armó su local en el garaje de su casa. Es fanático del rap y suele asistir a competencias de freestyle.', '2027-05-16', 'usuario'),
(32, 'Belén', 'Cáceres', '1984-09-05', 'bcaceres@test.com', 'belu.caceres', '$2y$12$x8DVDRq7U9wbKb/j6RM.mOgUI8mVamT6h2RZsBTToKRH4wvMx0HqG', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Emprendedora que cultiva su propia huerta orgánica. Vende conservas, salsas y mermeladas artesanales en ferias de fin de semana.', NULL, 'usuario'),
(33, 'Sebastián', 'Luna', '1979-11-13', 'seba.luna79@test.com', 'seba.luna', '$2y$12$o7EgRD5CVgwFETyABF5W.eCWu7dtpfMy08p2gfnhlv3/c3.14HMnu', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Operario en una fábrica automotriz. Colecciona relojes antiguos, lee historia universal y dedica horas a reparar radios a transistores.', NULL, 'usuario'),
(34, 'Micaela', 'Ponce', '1999-12-01', 'miki.ponce@test.com', 'miki.ponce', '$2y$12$TPetfjgc0HRdUS/y0Lygq.cH4t6oDil1Pc1/6oPpB4252AO6thVwC', 'imagenes/perfil_predeterminado.png', 'pro', 'Trabaja como asistente de ventas en una farmacia. Es fanática del entrenamiento funcional y lee sobre suplementación natural.', '2027-05-16', 'usuario'),
(35, 'Andrés', 'Villarroel', '1988-02-27', 'andres.v@test.com', 'andres.v', '$2y$12$WwT61vPiBOJuQPAFSDyD/OPTBlzVbZHCOGW4LfbJFVcbuUg401D9m', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Carnicero con negocio familiar heredado. Es un apasionado del ciclismo de ruta y usa aplicaciones GPS para trazar recorridos rurales.', NULL, 'usuario'),
(36, 'Antonella', 'Rivas', '1993-08-19', 'antorivas@test.com', 'anto_rivas', '$2y$12$7x50MCfpK40NsURBgMy.SerusC.IIwZSXBFHIWq98x2XGfdSB7ta6', 'imagenes/perfil_predeterminado.png', 'gratuito', 'Profesora de literatura en varias escuelas secundarias de su ciudad. Escribe cuentos cortos en su tiempo libre y participa de talleres de escritura.', NULL, 'usuario');

-- --------------------------------------------------------

--
-- Table structure for table `usuariointeres`
--

CREATE TABLE `usuariointeres` (
  `idUsuario` int(11) NOT NULL,
  `idInteres` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuariointeres`
--

INSERT INTO `usuariointeres` (`idUsuario`, `idInteres`) VALUES
(5, 1),
(8, 1),
(10, 1),
(12, 1),
(13, 1),
(16, 1),
(17, 1),
(20, 1),
(22, 1),
(23, 1),
(25, 1),
(28, 1),
(32, 1),
(33, 1),
(34, 1),
(36, 1),
(2, 2),
(7, 2),
(10, 2),
(13, 2),
(14, 2),
(19, 2),
(21, 2),
(24, 2),
(28, 2),
(31, 2),
(33, 2),
(36, 2),
(2, 3),
(6, 3),
(9, 3),
(10, 3),
(13, 3),
(17, 3),
(19, 3),
(21, 3),
(25, 3),
(27, 3),
(30, 3),
(33, 3),
(35, 3),
(2, 4),
(6, 4),
(8, 4),
(11, 4),
(13, 4),
(16, 4),
(18, 4),
(22, 4),
(26, 4),
(30, 4),
(34, 4),
(6, 5),
(7, 5),
(11, 5),
(13, 5),
(15, 5),
(19, 5),
(22, 5),
(23, 5),
(26, 5),
(28, 5),
(29, 5),
(31, 5),
(34, 5),
(35, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `configuracionnotificacion`
--
ALTER TABLE `configuracionnotificacion`
  ADD PRIMARY KEY (`idUsuario`);

--
-- Indexes for table `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`idCurso`),
  ADD KEY `idx_curso_creador_fechas` (`idUsuario`,`FechaInicio`,`FechaFin`),
  ADD KEY `idx_curso_titulo` (`Titulo`);

--
-- Indexes for table `cursoimagen`
--
ALTER TABLE `cursoimagen`
  ADD PRIMARY KEY (`idImagen`),
  ADD UNIQUE KEY `uk_curso_imagen_orden` (`idCurso`,`Orden`);

--
-- Indexes for table `cursointeres`
--
ALTER TABLE `cursointeres`
  ADD PRIMARY KEY (`idCurso`,`idInteres`),
  ADD KEY `fk_cursointeres_interes` (`idInteres`);

--
-- Indexes for table `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`idInscripcion`),
  ADD UNIQUE KEY `unica_inscripcion` (`idUsuario`,`idCurso`),
  ADD KEY `indice_usuario_estado` (`idUsuario`,`Estado`),
  ADD KEY `indice_curso_estado` (`idCurso`,`Estado`);

--
-- Indexes for table `interes`
--
ALTER TABLE `interes`
  ADD PRIMARY KEY (`idInteres`),
  ADD UNIQUE KEY `NombreInteres` (`NombreInteres`);

--
-- Indexes for table `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`idMaterial`),
  ADD KEY `indice_material_curso` (`idCurso`,`FechaPublicacion`),
  ADD KEY `fk_material_usuario` (`idUsuario`);

--
-- Indexes for table `materialarchivo`
--
ALTER TABLE `materialarchivo`
  ADD PRIMARY KEY (`idArchivo`),
  ADD KEY `fk_materialarchivo_material` (`idMaterial`);

--
-- Indexes for table `materialenlace`
--
ALTER TABLE `materialenlace`
  ADD PRIMARY KEY (`idEnlace`),
  ADD KEY `fk_materialenlace_material` (`idMaterial`);

--
-- Indexes for table `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`idNotificacion`),
  ADD KEY `idUsuario` (`idUsuario`),
  ADD KEY `idUsuarioActor` (`idUsuarioActor`);

--
-- Indexes for table `pregunta`
--
ALTER TABLE `pregunta`
  ADD PRIMARY KEY (`idPregunta`),
  ADD KEY `indice_curso_fecha` (`idCurso`,`FechaCreacion`),
  ADD KEY `indice_curso_destacada` (`idCurso`,`Destacada`),
  ADD KEY `fk_pregunta_usuario` (`idUsuario`),
  ADD KEY `indice_pregunta_material` (`idMaterial`);

--
-- Indexes for table `respuesta`
--
ALTER TABLE `respuesta`
  ADD PRIMARY KEY (`idRespuesta`),
  ADD KEY `indice_pregunta_fecha` (`idPregunta`,`FechaCreacion`),
  ADD KEY `fk_respuesta_usuario` (`idUsuario`);

--
-- Indexes for table `solicitudplanpro`
--
ALTER TABLE `solicitudplanpro`
  ADD PRIMARY KEY (`idSolicitud`),
  ADD KEY `idUsuario` (`idUsuario`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `NombreUsuario` (`NombreUsuario`);

--
-- Indexes for table `usuariointeres`
--
ALTER TABLE `usuariointeres`
  ADD PRIMARY KEY (`idUsuario`,`idInteres`),
  ADD KEY `idInteres` (`idInteres`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `curso`
--
ALTER TABLE `curso`
  MODIFY `idCurso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cursoimagen`
--
ALTER TABLE `cursoimagen`
  MODIFY `idImagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `inscripcion`
--
ALTER TABLE `inscripcion`
  MODIFY `idInscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `interes`
--
ALTER TABLE `interes`
  MODIFY `idInteres` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material`
--
ALTER TABLE `material`
  MODIFY `idMaterial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `materialarchivo`
--
ALTER TABLE `materialarchivo`
  MODIFY `idArchivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materialenlace`
--
ALTER TABLE `materialenlace`
  MODIFY `idEnlace` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `idNotificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `pregunta`
--
ALTER TABLE `pregunta`
  MODIFY `idPregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `respuesta`
--
ALTER TABLE `respuesta`
  MODIFY `idRespuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `solicitudplanpro`
--
ALTER TABLE `solicitudplanpro`
  MODIFY `idSolicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `configuracionnotificacion`
--
ALTER TABLE `configuracionnotificacion`
  ADD CONSTRAINT `configuracionnotificacion_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);

--
-- Constraints for table `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `fk_curso_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);

--
-- Constraints for table `cursoimagen`
--
ALTER TABLE `cursoimagen`
  ADD CONSTRAINT `cursoimagen_ibfk_1` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`) ON DELETE CASCADE;

--
-- Constraints for table `cursointeres`
--
ALTER TABLE `cursointeres`
  ADD CONSTRAINT `fk_cursointeres_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cursointeres_interes` FOREIGN KEY (`idInteres`) REFERENCES `interes` (`idInteres`);

--
-- Constraints for table `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD CONSTRAINT `fk_inscripcion_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscripcion_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE;

--
-- Constraints for table `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `fk_material_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_material_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE;

--
-- Constraints for table `materialarchivo`
--
ALTER TABLE `materialarchivo`
  ADD CONSTRAINT `fk_materialarchivo_material` FOREIGN KEY (`idMaterial`) REFERENCES `material` (`idMaterial`) ON DELETE CASCADE;

--
-- Constraints for table `materialenlace`
--
ALTER TABLE `materialenlace`
  ADD CONSTRAINT `fk_materialenlace_material` FOREIGN KEY (`idMaterial`) REFERENCES `material` (`idMaterial`) ON DELETE CASCADE;

--
-- Constraints for table `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  ADD CONSTRAINT `notificacion_ibfk_2` FOREIGN KEY (`idUsuarioActor`) REFERENCES `usuario` (`idUsuario`);

--
-- Constraints for table `pregunta`
--
ALTER TABLE `pregunta`
  ADD CONSTRAINT `fk_pregunta_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pregunta_material` FOREIGN KEY (`idMaterial`) REFERENCES `material` (`idMaterial`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pregunta_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE;

--
-- Constraints for table `respuesta`
--
ALTER TABLE `respuesta`
  ADD CONSTRAINT `fk_respuesta_pregunta` FOREIGN KEY (`idPregunta`) REFERENCES `pregunta` (`idPregunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_respuesta_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE;

--
-- Constraints for table `solicitudplanpro`
--
ALTER TABLE `solicitudplanpro`
  ADD CONSTRAINT `solicitudplanpro_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);

--
-- Constraints for table `usuariointeres`
--
ALTER TABLE `usuariointeres`
  ADD CONSTRAINT `usuariointeres_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  ADD CONSTRAINT `usuariointeres_ibfk_2` FOREIGN KEY (`idInteres`) REFERENCES `interes` (`idInteres`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
