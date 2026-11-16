<?php

// OBTENER LA PAGINA ACTUAL

$paginaActual = basename($_SERVER["PHP_SELF"]);

// PREPARAMOS UN TOKEN PARA CERRAR LA SESION

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION["token_cerrar_sesion"])) {
    $_SESSION["token_cerrar_sesion"] = bin2hex(random_bytes(32));
}

// COMPROBAMOS SI DEBEMOS MOSTRAR LA OPCION DE ADMINISTRACION

require_once __DIR__ . "/../clases/usuario.php";

$usuarioMenu = isset($_SESSION["idUsuario"])
    ? Usuario::buscarPorId((int)$_SESSION["idUsuario"])
    : null;

$mostrarAdministracion = $usuarioMenu && $usuarioMenu->esAdministrador();
?>


<!-- MENU LATERAL -->

<aside class="menu-lateral" id="menuLateral">


    <!-- BOTON DEL MENU -->

    <div class="menu-superior">

        <button type="button" class="boton-menu" id="botonMenu" aria-label="Abrir o cerrar menú">
            ☰
        </button>

    </div>


    <!-- OPCIONES DEL MENU -->

        </div>

    <!-- MOSTRAMOS EL LOGO DE CURSOLANDIA -->

    <div class="menu-logo">

        <a href="inicio.php"
           class="menu-logo-enlace"
           title="Cursolandia - Inicio"
           aria-label="Ir al inicio de Cursolandia">

            <img src="imagenes/logo_cursolandia.svg"
                 class="menu-logo-imagen"
                 alt="Cursolandia">

        </a>

    </div>

    <!-- OPCIONES DEL MENU -->

    <nav class="menu-opciones">


        <!-- INICIO -->

        <a href="inicio.php"
           class="menu-opcion <?php if ($paginaActual == "inicio.php") { echo "menu-opcion-activa"; } ?>"
           title="Inicio">

            <img src="imagenes/menu/inicio.svg" class="menu-icono" alt="">

            <span class="menu-texto">Inicio</span>

        </a>


        <!-- PERFIL -->

        <a href="perfil.php"
           class="menu-opcion <?php if ($paginaActual == "perfil.php") { echo "menu-opcion-activa"; } ?>"
           title="Perfil">

            <img src="imagenes/menu/perfil.svg" class="menu-icono" alt="">

            <span class="menu-texto">Perfil</span>

        </a>


        <!-- MIS CURSOS -->

        <a href="mis_cursos.php"
           class="menu-opcion <?php if ($paginaActual == "mis_cursos.php") { echo "menu-opcion-activa"; } ?>"
           title="Mis cursos">

            <img src="imagenes/menu/mis_cursos.svg" class="menu-icono icono-mis-cursos" alt="">

            <span class="menu-texto">Mis cursos</span>

        </a>


        <!-- NOTIFICACIONES -->

        <a href="notificaciones.php"
           class="menu-opcion <?php if ($paginaActual == "notificaciones.php") { echo "menu-opcion-activa"; } ?>"
           title="Notificaciones">

            <img src="imagenes/menu/notificaciones.svg" class="menu-icono" alt="">

            <span class="menu-texto">Notificaciones</span>

        </a>


        <!-- SOLICITAR PLAN PRO -->

        <a href="solicitar_plan_pro.php"
           class="menu-opcion <?php if ($paginaActual == "solicitar_plan_pro.php") { echo "menu-opcion-activa"; } ?>"
           title="Solicitar Plan Pro">

            <img src="imagenes/menu/solicitar_plan_pro.svg" class="menu-icono" alt="">

            <span class="menu-texto">Solicitar Plan Pro</span>

        </a>

        <!-- MOSTRAMOS ADMINISTRACION SOLAMENTE A LOS ADMINISTRADORES -->

        <?php if ($mostrarAdministracion): ?>

            <a href="admin.php"
            class="menu-opcion <?php echo $paginaActual === "admin.php" ? "menu-opcion-activa" : ""; ?>"
            title="Administración">

                <img src="imagenes/menu/administracion.svg" class="menu-icono" alt="">

                <span class="menu-texto">Administración</span>

            </a>

        <?php endif; ?>

     
        
        <!-- AGREGAMOS LA OPCION PARA CERRAR SESION -->

        <form method="POST" action="cerrar_sesion.php" class="menu-cerrar-form">

            <input type="hidden" name="token_cerrar_sesion"
                value="<?php echo htmlspecialchars($_SESSION["token_cerrar_sesion"], ENT_QUOTES, "UTF-8"); ?>">

            <button type="submit" class="menu-opcion menu-cerrar-boton">

                <svg class="menu-cerrar-icono" width="22" height="22"
                    viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M10 17l5-5-5-5M15 12H3M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>
                </svg>

                <span class="menu-texto">Cerrar sesión</span>

            </button>

        </form>

       
    </nav>


</aside>