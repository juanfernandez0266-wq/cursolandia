
<?php

session_start();

require_once __DIR__ . "/clases/usuario.php";

// PREPARAMOS LOS DATOS DEL FORMULARIO DE REGISTRO

$errores = [];
$Nombre = "";
$Apellido = "";
$NombreUsuario = "";
$email = "";

// PROCESAMOS EL REGISTRO DE NUEVOS USUARIOS

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_cuenta"])) {

    $Nombre = $_POST["Nombre"] ?? "";
    $Apellido = $_POST["Apellido"] ?? "";
    $NombreUsuario = $_POST["NombreUsuario"] ?? "";
    $email = $_POST["email"] ?? "";
    $Contraseña = $_POST["Contraseña"] ?? "";

    if (!is_string($Nombre) || !is_string($Apellido) ||
        !is_string($NombreUsuario) || !is_string($email) ||
        !is_string($Contraseña)) {

        $errores["bd"] = "Los datos del formulario no son válidos.";

    } else {

        $resultado = Usuario::registrar(
            $Nombre,
            $Apellido,
            $email,
            $NombreUsuario,
            $Contraseña
        );

        if (is_array($resultado)) {
            $errores = $resultado;

        } else {

            session_regenerate_id(true);
            $_SESSION["idUsuario"] = $resultado->verId();

            header("Location: completar_registro.php");
            exit;
        }
    }
}

// PROTEGEMOS LOS TEXTOS QUE MOSTRAMOS EN LA PAGINA

function escaparIndex($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Cursolandia es una comunidad donde podés descubrir cursos, aprender, compartir conocimientos y crear tus propias propuestas.">

    <title>Cursolandia - Aprendé y compartí</title>

    <!-- INCORPORAMOS BOOTSTRAP Y NUESTROS ESTILOS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css?v=6">
</head>

<body class="landing-body">

    <!-- ORGANIZAMOS EL MENU DE LA PORTADA -->

    <header>

        <nav class="navbar navbar-expand-lg navbar-light fixed-top landing-navbar">

            <div class="container">

                <!-- MOSTRAMOS EL LOGO EN LA NAVEGACION PRINCIPAL -->

                <a class="navbar-brand landing-marca" href="index.php" aria-label="Cursolandia - Inicio">
                    <img src="imagenes/logo_cursolandia.svg"
                        class="landing-logo"
                        alt="Cursolandia">
                </a>

                <button class="navbar-toggler" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#menuPrincipal"
                        aria-controls="menuPrincipal"
                        aria-expanded="false"
                        aria-label="Abrir menú de navegación">

                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menuPrincipal">

                    <ul class="navbar-nav ms-auto align-items-lg-center landing-enlaces">

                        <li class="nav-item">
                            <a class="nav-link" href="inicio.php">Ver cursos</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#como-funciona">Cómo funciona</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#preguntas">Preguntas frecuentes</a>
                        </li>

                        <li class="nav-item">
                            <a class="btn btn-outline-primary landing-boton-registro" href="#registro">
                                Registrarse
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="btn btn-primary landing-boton-entrar" href="iniciar_sesion.php">
                                Entrar
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

        </nav>

    </header>


    <main>

        <!-- SECCION 1: PRESENTAMOS CURSOLANDIA Y EL REGISTRO -->

        <section class="seccion-principal landing-hero text-white">

            <div class="container">

                <div class="row align-items-center landing-hero-fila">

                    <div class="col-lg-7 col-xl-8">

                        <div class="landing-hero-texto">

                            <span class="landing-etiqueta-hero">
                                APRENDÉ · CREÁ · COMPARTÍ
                            </span>

                            <h1>Aprendé y compartí lo que sabés</h1>

                            <p>
                                Cursolandia es un espacio para explorar cursos,
                                anotarte, crear los tuyos y resolver dudas
                                con otras personas.
                            </p>

                            <a href="#como-funciona" class="landing-conocer">
                                Conocé Cursolandia
                                <span aria-hidden="true">→</span>
                            </a>

                        </div>

                    </div>

                    <!-- MANTENEMOS EL FORMULARIO DE REGISTRO -->

                    <div class="col-lg-5 col-xl-4">

                        <form id="registro" method="POST" action="index.php#registro"
                              class="landing-formulario bg-white text-dark">

                            <h2>Crear cuenta</h2>

                            <p class="landing-formulario-descripcion">
                                Registrate y comenzá a explorar nuestra comunidad.
                            </p>

                            <?php if (isset($errores["bd"])): ?>
                                <div class="alert alert-danger">
                                    <?php echo escaparIndex($errores["bd"]); ?>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3 mb-3">

                                <div class="col-sm-6">

                                    <label for="Nombre" class="form-label">Nombre</label>

                                    <input type="text" class="form-control"
                                           id="Nombre" name="Nombre" maxlength="20"
                                           autocomplete="given-name" required
                                           placeholder="Tu nombre"
                                           value="<?php echo escaparIndex($Nombre); ?>">

                                    <?php if (isset($errores["Nombre"])): ?>
                                        <p class="text-danger landing-error">
                                            <?php echo escaparIndex($errores["Nombre"]); ?>
                                        </p>
                                    <?php endif; ?>

                                </div>

                                <div class="col-sm-6">

                                    <label for="Apellido" class="form-label">Apellido</label>

                                    <input type="text" class="form-control"
                                           id="Apellido" name="Apellido" maxlength="20"
                                           autocomplete="family-name" required
                                           placeholder="Tu apellido"
                                           value="<?php echo escaparIndex($Apellido); ?>">

                                    <?php if (isset($errores["Apellido"])): ?>
                                        <p class="text-danger landing-error">
                                            <?php echo escaparIndex($errores["Apellido"]); ?>
                                        </p>
                                    <?php endif; ?>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label for="NombreUsuario" class="form-label">
                                    Nombre de usuario
                                </label>

                                <input type="text" class="form-control"
                                       id="NombreUsuario" name="NombreUsuario"
                                       maxlength="20" autocomplete="username" required
                                       placeholder="Elegí un nombre de usuario"
                                       value="<?php echo escaparIndex($NombreUsuario); ?>">

                                <?php if (isset($errores["NombreUsuario"])): ?>
                                    <p class="text-danger landing-error">
                                        <?php echo escaparIndex($errores["NombreUsuario"]); ?>
                                    </p>
                                <?php endif; ?>

                            </div>

                            <div class="mb-3">

                                <label for="email" class="form-label">
                                    Correo electrónico
                                </label>

                                <input type="email" class="form-control"
                                       id="email" name="email"
                                       autocomplete="email" required
                                       placeholder="tuusuario@gmail.com"
                                       value="<?php echo escaparIndex($email); ?>">

                                <?php if (isset($errores["email"])): ?>
                                    <p class="text-danger landing-error">
                                        <?php echo escaparIndex($errores["email"]); ?>
                                    </p>
                                <?php endif; ?>

                            </div>

                            <div class="mb-4">

                                <label for="Contraseña" class="form-label">
                                    Contraseña
                                </label>

                                <input type="password" class="form-control"
                                       id="Contraseña" name="Contraseña"
                                       minlength="8" maxlength="20"
                                       autocomplete="new-password" required
                                       placeholder="Mínimo 8 caracteres">

                                <?php if (isset($errores["Contraseña"])): ?>
                                    <p class="text-danger landing-error">
                                        <?php echo escaparIndex($errores["Contraseña"]); ?>
                                    </p>
                                <?php endif; ?>

                            </div>

                            <button type="submit" name="crear_cuenta"
                                    value="1" class="btn btn-primary landing-crear">
                                Crear cuenta
                            </button>

                            <p class="landing-ya-cuenta">
                                ¿Ya tenés una cuenta?
                                <a href="iniciar_sesion.php">Iniciá sesión</a>
                            </p>

                        </form>

                    </div>

                </div>

            </div>

        </section>


        <!-- SECCION 2: EXPLICAMOS QUE ES CURSOLANDIA -->

        <section id="como-funciona" class="landing-seccion landing-seccion-clara">

            <div class="container">

                <div class="landing-encabezado">

                    <span class="landing-etiqueta">CONOCÉ LA PLATAFORMA</span>

                    <h2>Un lugar para aprender, crear y compartir</h2>

                    <p>
                        Cursolandia es una plataforma donde los cursos nacen de las
                        personas que forman parte de ella. Cualquier usuario puede
                        encontrar nuevas propuestas para aprender y, al mismo tiempo,
                        utilizar sus conocimientos, experiencias o habilidades para
                        crear un curso y compartirlo con los demás.
                    </p>

                </div>

                <!-- DIVIDIMOS LA INFORMACION PARA FACILITAR LA LECTURA -->

                <div class="row g-4 landing-tarjetas">

                    <div class="col-md-6">

                        <article class="landing-tarjeta">

                            <span class="landing-numero">01</span>
                            <h3>Descubrí cursos a tu manera</h3>

                            <p>
                                La plataforma reúne cursos de diferentes temáticas
                                y características. Podés encontrar propuestas gratuitas
                                o pagas, explorar por título o etiquetas y descubrir
                                contenido relacionado con los intereses que seleccionaste
                                en tu perfil.
                            </p>

                            <p>
                                De esta manera, cada usuario puede recorrer Cursolandia
                                de acuerdo con aquello que realmente le interesa aprender.
                            </p>

                        </article>

                    </div>

                    <div class="col-md-6">

                        <article class="landing-tarjeta">

                            <span class="landing-numero">02</span>
                            <h3>Un perfil que habla de vos</h3>

                            <p>
                                Además de tus datos personales, podés contar tus
                                antecedentes, seleccionar tus intereses y presentarte
                                frente a otros usuarios.
                            </p>

                            <p>
                                Cursolandia relaciona esas preferencias con las etiquetas
                                de los cursos para ofrecerte recomendaciones cercanas
                                a tus intereses.
                            </p>

                        </article>

                    </div>

                    <div class="col-md-6">

                        <article class="landing-tarjeta">

                            <span class="landing-numero">03</span>
                            <h3>Compartí lo que sabés</h3>

                            <p>
                                Si tenés conocimientos o experiencia sobre un tema,
                                también podés crear tu propia propuesta. Cada curso
                                cuenta con una descripción, fechas de inicio y finalización,
                                etiquetas y opciones para organizarlo.
                            </p>

                            <p>
                                Como creador, podés compartir materiales y acompañar
                                a las personas que decidan participar.
                            </p>

                        </article>

                    </div>

                    <div class="col-md-6">

                        <article class="landing-tarjeta">

                            <span class="landing-numero">04</span>
                            <h3>Aprendé con otras personas</h3>

                            <p>
                                Dentro de los cursos, aprender no significa únicamente
                                acceder a contenido. Los alumnos pueden realizar preguntas,
                                responder a otros participantes y formar parte
                                de conversaciones.
                            </p>

                            <p>
                                El creador también participa como docente, respondiendo
                                consultas y ayudando a organizar ese intercambio.
                            </p>

                        </article>

                    </div>

                </div>

                <div class="landing-cierre-seccion">

                    <p>
                        Una misma persona puede aprender algo nuevo, participar junto
                        con otros usuarios y, cuando tenga algo para compartir,
                        convertirse también en creador.
                    </p>

                    <strong>
                        Cada nuevo curso amplía las posibilidades de la comunidad.
                    </strong>

                </div>

            </div>

        </section>


        <!-- SECCION 3: EXPLICAMOS LA EXPERIENCIA DENTRO DE UN CURSO -->

        <section id="experiencia" class="landing-seccion landing-seccion-experiencia">

            <div class="container">

                <div class="landing-encabezado">

                    <span class="landing-etiqueta">LA EXPERIENCIA</span>

                    <h2>Mucho más que entrar a un curso</h2>

                    <p>
                        Cada curso en Cursolandia tiene su propia propuesta.
                        Antes de participar podés conocer de qué trata, quién lo creó,
                        durante qué fechas estará disponible y qué características tiene.
                    </p>

                </div>

                <div class="landing-pasos">

                    <article class="landing-paso">

                        <div class="landing-paso-numero">01</div>

                        <div>
                            <h3>Encontrá una propuesta que te interese</h3>

                            <p>
                                Algunos cursos son gratuitos y otros pagos. También
                                pueden contar con un cupo determinado y utilizan etiquetas
                                que ayudan a identificar los temas con los que están
                                relacionados.
                            </p>
                        </div>

                    </article>

                    <article class="landing-paso">

                        <div class="landing-paso-numero">02</div>

                        <div>
                            <h3>Inscribite y comenzá a participar</h3>

                            <p>
                                Podés sumarte mientras el curso se encuentre activo.
                                En los cursos gratuitos, la incorporación puede
                                realizarse directamente. En los pagos, la inscripción
                                permanece pendiente hasta que el creador la confirme.
                            </p>

                            <p>
                                El acuerdo del pago se realiza entre las personas
                                involucradas, por fuera de la plataforma.
                            </p>
                        </div>

                    </article>

                    <article class="landing-paso">

                        <div class="landing-paso-numero">03</div>

                        <div>
                            <h3>Accedé a materiales y recursos</h3>

                            <p>
                                Una vez dentro, el creador puede compartir materiales
                                mediante archivos adjuntos y enlaces para complementar
                                su propuesta. Los alumnos pueden consultar esos recursos
                                durante el desarrollo del curso.
                            </p>
                        </div>

                    </article>

                    <article class="landing-paso">

                        <div class="landing-paso-numero">04</div>

                        <div>
                            <h3>Hacé preguntas y compartí respuestas</h3>

                            <p>
                                Los alumnos pueden iniciar preguntas cuando necesitan
                                resolver una duda y responder a las consultas de otros
                                participantes. Las conversaciones no dependen solamente
                                del creador: todos pueden aportar experiencias, ideas
                                y conocimientos.
                            </p>
                        </div>

                    </article>

                    <article class="landing-paso">

                        <div class="landing-paso-numero">05</div>

                        <div>
                            <h3>Resolvé consultas con el docente</h3>

                            <p>
                                El creador también participa como docente. Puede
                                responder consultas, organizar preguntas y destacar
                                publicaciones útiles. Cuando una respuesta resuelve
                                una duda, puede aceptarse y dar por cerrada
                                la conversación.
                            </p>
                        </div>

                    </article>

                    <article class="landing-paso">

                        <div class="landing-paso-numero">06</div>

                        <div>
                            <h3>Mantenete al tanto de las novedades</h3>

                            <p>
                                Cursolandia incorpora notificaciones sobre preguntas,
                                respuestas y cambios importantes en las conversaciones,
                                para facilitar el seguimiento de la actividad de los cursos.
                            </p>
                        </div>

                    </article>

                </div>

                <div class="landing-cierre-seccion">
                    <p>
                        Cada curso reúne en un mismo lugar la propuesta de quien enseña,
                        los materiales que decide compartir y la participación de quienes
                        quieren aprender.
                    </p>
                    <strong>El conocimiento crece cuando se comparte.</strong>
                </div>

            </div>

        </section>


        <!-- SECCION 4: ORGANIZAMOS LAS PREGUNTAS FRECUENTES -->

        <section id="preguntas" class="landing-seccion landing-seccion-faq">

            <div class="container">

                <div class="landing-encabezado">

                    <span class="landing-etiqueta">RESOLVÉ TUS DUDAS</span>

                    <h2>Preguntas frecuentes</h2>

                    <p>
                        Conocé cómo funcionan los cursos, las inscripciones,
                        los planes y las herramientas de Cursolandia.
                    </p>

                </div>

                <div class="landing-faq-lista">

                    <details class="landing-faq-item">
                        <summary>¿Qué diferencia hay entre el Plan Gratuito y el Plan Pro?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Con el Plan Gratuito podés crear tus propios cursos
                                y participar como alumno en otras propuestas.
                                Podés mantener hasta tres cursos activos creados
                                y participar simultáneamente en hasta tres cursos activos.
                            </p>
                            <p>
                                El Plan Pro elimina esos límites y permite crear
                                y participar en más cursos al mismo tiempo.
                                Además, los cursos de creadores Pro pueden aparecer
                                destacados dentro de la plataforma.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Cómo funcionan los cursos pagos?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Cada creador puede decidir si su curso será gratuito
                                o pago. Si te inscribís en uno pago, tu inscripción
                                queda pendiente hasta que el creador la confirme.
                            </p>
                            <p>
                                El pago se acuerda y gestiona por fuera de Cursolandia.
                                La plataforma se encarga de reflejar el estado
                                de la inscripción.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Todos los cursos son iguales?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                No. Los cursos pueden ser gratuitos o pagos, públicos
                                o privados. Cada creador define su descripción,
                                etiquetas, fechas de inicio y finalización y,
                                cuando lo considera necesario, un cupo máximo.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Cómo encuentro cursos relacionados con mis intereses?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Al completar tu perfil podés seleccionar tus intereses.
                                Cursolandia relaciona esa información con las etiquetas
                                de los cursos para mostrarte propuestas recomendadas.
                            </p>
                            <p>
                                También podés buscar directamente por título o etiquetas.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Puedo crear cursos y también participar como alumno?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Sí. Un mismo usuario puede crear sus cursos e inscribirse
                                en propuestas de otras personas. No podés inscribirte
                                como alumno en un curso creado por vos mismo.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Los cursos pueden tener material para los alumnos?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Sí. Los creadores pueden compartir archivos y enlaces.
                                En el Plan Gratuito, el límite de adjuntos es de cinco
                                archivos, con un máximo de 5 MB por archivo y 10 MB
                                en total.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Cuándo puedo inscribirme en un curso?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Mientras esté activo, dentro de sus fechas de inicio
                                y finalización. También debés cumplir las condiciones
                                de tu plan y no ser el creador del curso.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Puedo hacer preguntas dentro de un curso?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Sí. Los alumnos pueden crear consultas y responder
                                a otros participantes. El creador puede responder,
                                organizar las conversaciones y destacar preguntas.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Qué significa que una respuesta fue aceptada?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Significa que el creador de la pregunta o el docente
                                consideró que esa respuesta resolvió la consulta.
                                La respuesta queda destacada y la conversación se cierra.
                            </p>
                        </div>
                    </details>

                    <details class="landing-faq-item">
                        <summary>¿Cómo me entero de las novedades de un curso?</summary>
                        <div class="landing-faq-respuesta">
                            <p>
                                Cursolandia incorpora notificaciones para informar
                                sobre nuevas preguntas, respuestas y cambios importantes
                                en conversaciones relacionadas con tus cursos.
                            </p>
                        </div>
                    </details>

                </div>

            </div>

        </section>


        <!-- INVITAMOS A FORMAR PARTE DE LA COMUNIDAD -->

        <section class="landing-invitacion">

            <div class="container">

                <div class="landing-invitacion-contenido">

                    <h2>Tu próximo aprendizaje puede empezar acá</h2>

                    <p>
                        Explorá nuevas propuestas, compartí lo que sabés
                        y formá parte de Cursolandia.
                    </p>

                    <a href="#registro" class="btn btn-light">
                        Crear mi cuenta
                    </a>

                </div>

            </div>

        </section>

    </main>


    <!-- COMPLETAMOS EL PIE DE PAGINA -->

    <footer class="landing-footer">

        <div class="container">

            <div class="row gy-4">

                <div class="col-lg-5">

                    <!-- INCORPORAMOS EL LOGO EN EL PIE DE PAGINA -->

                    <a href="index.php" class="landing-footer-logo-enlace">
                        <img src="imagenes/logo_cursolandia.svg"
                            class="landing-footer-logo"
                            alt="Cursolandia">
                    </a>

                    <p>
                        Un espacio para aprender, enseñar y compartir
                        conocimientos con otras personas.
                    </p>

                </div>

                <div class="col-sm-6 col-lg-3">

                    <h3>Explorar</h3>

                    <a href="inicio.php">Ver cursos</a>
                    <a href="#como-funciona">Cómo funciona</a>
                    <a href="#experiencia">La experiencia</a>

                </div>

                <div class="col-sm-6 col-lg-4">

                    <h3>Tu cuenta</h3>

                    <a href="#registro">Registrarse</a>
                    <a href="iniciar_sesion.php">Iniciar sesión</a>
                    <a href="#preguntas">Preguntas frecuentes</a>

                </div>

            </div>

            <div class="landing-footer-inferior">

                <span>Cursolandia · 2026</span>

                <span>
                    Proyecto académico de Programación III
                </span>

            </div>

        </div>

    </footer>


    <!-- ACTIVAMOS EL MENU RESPONSIVE DE BOOTSTRAP -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
