// MENU LATERAL

let menuLateral = document.getElementById("menuLateral");

let botonMenu = document.getElementById("botonMenu");



// RECUPERAR ESTADO DEL MENU

let estadoMenu = localStorage.getItem("estadoMenu");

if (estadoMenu == "abierto") {

    menuLateral.classList.add("menu-abierto");

    document.body.classList.add("menu-abierto");
}



// ABRIR O CERRAR MENU

botonMenu.addEventListener("click", function () {

    menuLateral.classList.toggle("menu-abierto");

    document.body.classList.toggle("menu-abierto");


    // GUARDAR ESTADO DEL MENU

    if (menuLateral.classList.contains("menu-abierto")) {

        localStorage.setItem("estadoMenu", "abierto");

    }
    else {

        localStorage.setItem("estadoMenu", "cerrado");
    }

});