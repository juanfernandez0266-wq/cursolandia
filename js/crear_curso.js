// CONTROLAR LAS FECHAS

let fechaInicio = document.getElementById("FechaInicio");
let fechaFin = document.getElementById("FechaFin");


function diaSiguiente(fecha) {

    let partes = fecha.split("-").map(Number);
    let siguiente = new Date(Date.UTC(partes[0], partes[1] - 1, partes[2] + 1));

    return siguiente.toISOString().slice(0, 10);
}


function actualizarFechaFin() {

    let fechaBase = fechaInicio.value || fechaInicio.min;

    fechaFin.min = diaSiguiente(fechaBase);

    if (fechaFin.value && fechaFin.value < fechaFin.min) {
        fechaFin.value = "";
    }
}

fechaInicio.addEventListener("change", actualizarFechaFin);
actualizarFechaFin();

// MOSTRAR LA OPCION SELECCIONADA

function actualizarTextoSelector(idSelect, idTexto, textoInicial) {

    let selector = document.getElementById(idSelect);
    let texto = document.getElementById(idTexto);

    function actualizar() {

        if (selector.value === "") {
            texto.textContent = textoInicial;
        } else {
            texto.textContent = selector.options[selector.selectedIndex].textContent;
        }

    }

    selector.addEventListener("change", actualizar);
    actualizar();

}


actualizarTextoSelector("TipoAcceso", "textoAcceso", "Acceso");
actualizarTextoSelector("TieneCupo", "textoCupo", "Cupo");
actualizarTextoSelector("TipoCurso", "textoCosto", "Costo");

// CONTROLAR EL CUPO

let tieneCupo = document.getElementById("TieneCupo");
let contenedorCupo = document.getElementById("contenedorCupo");
let campoCupo = document.getElementById("Cupo");


function actualizarCupo() {

    let conLimite = tieneCupo.value === "si";

    contenedorCupo.hidden = !conLimite;
    campoCupo.disabled = !conLimite;
    campoCupo.required = conLimite;

    if (!conLimite) {
        campoCupo.value = "";
    }

}

tieneCupo.addEventListener("change", actualizarCupo);
actualizarCupo();



// CONTROLAR EL COSTO

let tipoCurso = document.getElementById("TipoCurso");
let contenedorCosto = document.getElementById("contenedorCosto");
let campoCosto = document.getElementById("Costo");


function actualizarCosto() {

    let esPago = tipoCurso.value === "pago";

    contenedorCosto.hidden = !esPago;
    campoCosto.disabled = !esPago;
    campoCosto.required = esPago;

    if (!esPago) {
        campoCosto.value = "";
    }

}

tipoCurso.addEventListener("change", actualizarCosto);
actualizarCosto();

// PREVISUALIZAR IMAGENES DEL CURSO

let camposImagenes = document.querySelectorAll(".imagen-input");
let errorImagenes = document.getElementById("errorImagenes");

camposImagenes.forEach(function (campo) {

    campo.addEventListener("change", function () {

        let archivo = campo.files[0];
        let contenedor = campo.closest("label");
        let vista = contenedor.querySelector(".imagen-preview");

        errorImagenes.hidden = true;

        // SIN ARCHIVO SELECCIONADO

        if (!archivo) {
            vista.removeAttribute("src");
            vista.hidden = true;
            contenedor.classList.remove("con-imagen");
            return;
        }

        // VALIDAR FORMATO Y TAMAÑO

        let tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];

        if (!tiposPermitidos.includes(archivo.type) || archivo.size === 0 || archivo.size > 5 * 1024 * 1024) {

            errorImagenes.textContent = "Seleccioná una imagen JPG, PNG o WEBP de hasta 5 MB.";
            errorImagenes.hidden = false;

            campo.value = "";
            vista.removeAttribute("src");
            vista.hidden = true;
            contenedor.classList.remove("con-imagen");

            return;
        }

        // MOSTRAR VISTA PREVIA

        let lector = new FileReader();

        lector.onload = function (evento) {
            vista.src = evento.target.result;
            vista.hidden = false;
            contenedor.classList.add("con-imagen");
        };

        lector.readAsDataURL(archivo);

    });

});


// VALIDAR FORMULARIO ANTES DE CREAR CURSO

let formularioCrearCurso = document.querySelector(".crear-curso-form");

if (formularioCrearCurso != null) {

    formularioCrearCurso.addEventListener("submit", function (evento) {

        let primerCampoInvalido = null;


        // QUITAR ERRORES ANTERIORES

        formularioCrearCurso.querySelectorAll(".error-campo").forEach(function (error) {
            if (error.id !== "errorImagenes") error.remove();
        });

        formularioCrearCurso.querySelectorAll(".campo-invalido").forEach(function (grupo) {
            grupo.classList.remove("campo-invalido");
        });


        // MOSTRAR ERROR JUNTO AL CAMPO

        function mostrarError(idCampo, mensaje) {

            let campo = document.getElementById(idCampo);

            let grupo = campo.closest(".configuracion-opcion")
                || campo.closest(".mb-4")
                || campo.closest(".mt-4")
                || campo.parentElement;

            grupo.classList.add("campo-invalido");

            let error = document.createElement("p");

            error.className = "error-campo";
            error.textContent = mensaje;

            grupo.appendChild(error);

            if (primerCampoInvalido == null) {
                primerCampoInvalido = campo;
            }
        }


        // TITULO Y DESCRIPCION

        if (document.getElementById("Titulo").value.trim() === "") {
            mostrarError("Titulo", "Ingresá un título para el curso.");
        }

        if (document.getElementById("Descripcion").value.trim() === "") {
            mostrarError("Descripcion", "Ingresá una descripción para el curso.");
        }


        // ACCESO

        if (document.getElementById("TipoAcceso").value === "") {
            mostrarError("TipoAcceso", "Seleccioná si el curso es público o privado.");
        }


        // CUPO

        let opcionCupo = document.getElementById("TieneCupo").value;
        let cantidadCupo = document.getElementById("Cupo").value;

        if (opcionCupo === "") {
            mostrarError("TieneCupo", "Seleccioná una opción de cupo.");
        } else if (opcionCupo === "si" &&
            (!/^[1-9]\d*$/.test(cantidadCupo) || Number(cantidadCupo) > 2147483647)) {

            mostrarError("Cupo", "Ingresá un cupo entero mayor que cero.");
        }


        // COSTO

        let tipoCursoSeleccionado = document.getElementById("TipoCurso").value;
        let importe = document.getElementById("Costo").value;

        if (tipoCursoSeleccionado === "") {
            mostrarError("TipoCurso", "Seleccioná si el curso es gratuito o pago.");
        } else if (tipoCursoSeleccionado === "pago" &&
            (!/^\d{1,8}(\.\d{1,2})?$/.test(importe) || Number(importe) <= 0)) {

            mostrarError("Costo", "Ingresá un importe válido mayor que cero.");
        }


        // FECHAS

        let inicio = document.getElementById("FechaInicio");
        let fin = document.getElementById("FechaFin");

        if (!inicio.value || inicio.value < inicio.min) {
            mostrarError("FechaInicio", "La fecha de inicio no puede ser anterior a hoy.");
        }

        if (!fin.value || (inicio.value && fin.value <= inicio.value)) {
            mostrarError("FechaFin", "La fecha de fin debe ser posterior al inicio.");
        }


        // COMPROBAR IMAGEN PRINCIPAL

        let imagenPrincipal = document.getElementById("ImagenPrincipal");
        let imagenesAdicionales = document.querySelectorAll(".curso-miniatura .imagen-input");

        let hayAdicionales = Array.from(imagenesAdicionales).some(function (campo) {
            return campo.files.length > 0;
        });

        if (hayAdicionales && imagenPrincipal.files.length === 0) {

            errorImagenes.textContent = "Agregá primero una imagen principal.";
            errorImagenes.hidden = false;

            if (primerCampoInvalido === null) primerCampoInvalido = imagenPrincipal;
        }


        // EVITAR EL ENVIO SI HAY ERRORES

        if (primerCampoInvalido !== null) {
            evento.preventDefault();
            primerCampoInvalido.focus();
        }

    });


    // QUITAR EL ERROR CUANDO SE MODIFICA UN CAMPO

    function limpiarError(evento) {

        let campo = evento.target;

        let grupo = campo.closest(".configuracion-opcion")
            || campo.closest(".mb-4")
            || campo.closest(".mt-4");

        if (grupo !== null) {

            grupo.classList.remove("campo-invalido");

            grupo.querySelectorAll(".error-campo").forEach(function (error) {
                error.remove();
            });
        }
    }

    formularioCrearCurso.addEventListener("input", limpiarError);
    formularioCrearCurso.addEventListener("change", limpiarError);

}


// CAMBIAR CANDADO SEGUN EL TIPO DE ACCESO

let selectorAcceso = document.getElementById("TipoAcceso");
let iconoAcceso = document.getElementById("iconoAcceso");


function actualizarIconoAcceso() {

    if (selectorAcceso.value === "privado") {
        iconoAcceso.src = "imagenes/curso/candado_cerrado.svg";
    } else {
        iconoAcceso.src = "imagenes/curso/candado_abierto.svg";
    }

}


if (selectorAcceso != null && iconoAcceso != null) {

    selectorAcceso.addEventListener("change", actualizarIconoAcceso);

    actualizarIconoAcceso();

}