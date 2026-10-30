<?php

// OBTENER LA PAGINA ACTUAL

$paginaActual = basename($_SERVER["PHP_SELF"]);

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


    </nav>


</aside>