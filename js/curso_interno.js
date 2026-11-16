
// ANIMAMOS EL PANEL AL INGRESAR AL CURSO

window.addEventListener("pageshow", function () {

    let contenido = document.querySelector(".curso-interno-contenido");

    if (contenido !== null) {

        contenido.animate([
            { opacity: 0, transform: "translateY(-28px)", offset: 0 },
            { opacity: 0.45, offset: 0.45 },
            { opacity: 1, transform: "translateY(0)", offset: 1 }
        ], {
            duration: 550,
            easing: "cubic-bezier(0.22, 0.61, 0.36, 1)",
            iterations: 1
        });

    }

});



// MOSTRAMOS Y OCULTAMOS EL FORMULARIO PARA CREAR PREGUNTAS

let botonCrearPregunta = document.getElementById("botonCrearPregunta");
let formularioPregunta = document.getElementById("formCrearPregunta");

if (botonCrearPregunta !== null && formularioPregunta !== null) {

    botonCrearPregunta.addEventListener("click", function () {

        formularioPregunta.hidden = !formularioPregunta.hidden;

        let abierto = !formularioPregunta.hidden;
        botonCrearPregunta.setAttribute("aria-expanded", String(abierto));

        if (abierto) {
            document.getElementById("tituloPregunta").focus();
        }

    });

}



// ABRIMOS Y CERRAMOS EL EDITOR DE MATERIAL

const abrirEditorMaterial = document.getElementById("abrirEditorMaterial");
const volverMaterial = document.getElementById("volverMaterial");
const vistaMaterial = document.getElementById("vistaMaterial");
const editorMaterial = document.getElementById("editorMaterial");

if (abrirEditorMaterial && volverMaterial && vistaMaterial && editorMaterial) {

    abrirEditorMaterial.addEventListener("click", function () {
        vistaMaterial.hidden = true;
        editorMaterial.hidden = false;
        document.getElementById("tituloMaterial").focus();
    });

    volverMaterial.addEventListener("click", function () {
        editorMaterial.hidden = true;
        vistaMaterial.hidden = false;
    });

}


// MOSTRAMOS LOS ARCHIVOS SELECCIONADOS

const archivosMaterial = document.getElementById("archivosMaterial");
const listaArchivosMaterial = document.getElementById("listaArchivosMaterial");

if (archivosMaterial && listaArchivosMaterial) {

    archivosMaterial.addEventListener("change", function () {

        listaArchivosMaterial.replaceChildren();

        Array.from(archivosMaterial.files).forEach(function (archivo) {
            const elemento = document.createElement("li");
            elemento.textContent = archivo.name;
            listaArchivosMaterial.appendChild(elemento);
        });

    });

}


// PERMITIMOS AGREGAR Y QUITAR ENLACES

const mostrarEnlaceMaterial = document.getElementById("mostrarEnlaceMaterial");
const campoEnlaceMaterial = document.getElementById("campoEnlaceMaterial");
const urlMaterial = document.getElementById("urlMaterial");
const agregarEnlaceMaterial = document.getElementById("agregarEnlaceMaterial");
const listaEnlacesMaterial = document.getElementById("listaEnlacesMaterial");
const errorEnlaceMaterial = document.getElementById("errorEnlaceMaterial");

if (mostrarEnlaceMaterial && campoEnlaceMaterial && urlMaterial && agregarEnlaceMaterial && listaEnlacesMaterial) {

    mostrarEnlaceMaterial.addEventListener("click", function () {
        campoEnlaceMaterial.hidden = false;
        urlMaterial.focus();
    });

    agregarEnlaceMaterial.addEventListener("click", function () {

        let direccion;

        try {
            direccion = new URL(urlMaterial.value.trim());

            if (!["http:", "https:"].includes(direccion.protocol)) throw new Error();

        } catch (error) {
            errorEnlaceMaterial.hidden = false;
            return;
        }

        errorEnlaceMaterial.hidden = true;

        const elemento = document.createElement("li");
        const texto = document.createElement("span");
        const quitar = document.createElement("button");
        const campoOculto = document.createElement("input");

        campoOculto.type = "hidden";
        campoOculto.name = "enlacesMaterial[]";
        campoOculto.value = direccion.href;

        texto.textContent = direccion.href;
        quitar.type = "button";
        quitar.textContent = "Quitar";

        quitar.addEventListener("click", function () {
            elemento.remove();
        });

        elemento.append(texto, quitar, campoOculto);
        listaEnlacesMaterial.appendChild(elemento);

        urlMaterial.value = "";
        campoEnlaceMaterial.hidden = true;

    });



}


// ABRIMOS EL FORMULARIO SI VENIMOS DESDE UN MATERIAL

if (botonCrearPregunta !== null && formularioPregunta !== null) {

    const parametros = new URLSearchParams(window.location.search);

    if (parametros.get("crearPregunta") === "1") {

        formularioPregunta.hidden = false;
        botonCrearPregunta.setAttribute("aria-expanded", "true");

        document.getElementById("tituloPregunta").focus();
    }
}
