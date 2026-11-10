
// ELEMENTOS DEL FORMULARIO

let formularioSolicitud = document.getElementById("formularioSolicitud");
let pasoSolicitud = document.getElementById("pasoSolicitud");
let pasoDetalle = document.getElementById("pasoDetalle");

let botonContinuar = document.getElementById("continuarSolicitud");
let botonVolver = document.getElementById("volverSolicitud");

let campoMensaje = document.getElementById("Mensaje");
let campoComprobante = document.getElementById("Comprobante");

let errorComprobante = document.getElementById("errorComprobante");

// CAMBIAR ENTRE PANTALLAS

function cambiarPaso(pasoActual, pasoSiguiente) {

    if (pasoActual.classList.contains("paso-saliendo")) {
        return;
    }

    pasoActual.classList.add("paso-saliendo");

    setTimeout(function () {

        pasoActual.hidden = true;
        pasoActual.classList.remove("paso-saliendo");

        pasoSiguiente.hidden = false;

    }, 240);

}

// VALIDAR COMPROBANTE

function validarComprobante() {

    let archivo = campoComprobante.files[0];

    if (!archivo) {
        errorComprobante.textContent = "Adjuntá el comprobante para continuar.";
        errorComprobante.hidden = false;
        return false;
    }

    let extensionValida = /\.(jpg|jpeg|png|pdf)$/i.test(archivo.name);

    if (!extensionValida) {
        errorComprobante.textContent = "Solo se permiten archivos PNG, JPG o PDF.";
        errorComprobante.hidden = false;
        return false;
    }

    if (archivo.size == 0 || archivo.size > 10 * 1024 * 1024) {
        errorComprobante.textContent = "El archivo debe tener un tamaño máximo de 10 MB.";
        errorComprobante.hidden = false;
        return false;
    }

    errorComprobante.hidden = true;

    return true;
}


// CONTINUAR AL DETALLE

if (botonContinuar != null) {

    botonContinuar.addEventListener("click", function () {

        if (!validarComprobante()) {
            return;
        }

        let mensaje = campoMensaje.value.trim();
        let archivo = campoComprobante.files[0];

        document.getElementById("detalleMensaje").textContent = mensaje || "Sin mensaje";
        document.getElementById("detalleComprobante").textContent = archivo.name;

        cambiarPaso(pasoSolicitud, pasoDetalle);

    });

}


// VOLVER AL FORMULARIO

if (botonVolver != null) {

    botonVolver.addEventListener("click", function () {

        cambiarPaso(pasoDetalle, pasoSolicitud);

    });

}


// MOSTRAR COMPROBANTE SELECCIONADO

if (campoComprobante != null) {

    campoComprobante.addEventListener("change", function () {

        let archivo = campoComprobante.files[0];

        let nombreComprobante = document.getElementById("nombreComprobante");


        if (archivo) {

            nombreComprobante.textContent = archivo.name;

        } else {

            nombreComprobante.textContent = "Subir comprobante";

        }


        validarComprobante();

    });

}


// COMPROBAR ARCHIVO Y ENVIAR SOLICITUD

let envioConfirmado = false;
let envioProcesando = false;

if (formularioSolicitud != null) {

    formularioSolicitud.addEventListener("submit", function (evento) {

        if (envioConfirmado) {
            return;
        }

        evento.preventDefault();

        if (envioProcesando) {
            return;
        }

        if (!validarComprobante()) {

            cambiarPaso(pasoDetalle, pasoSolicitud);
            return;
        }

        envioProcesando = true;

        pasoDetalle.classList.add("paso-saliendo");

        setTimeout(function () {

            envioConfirmado = true;

            let botonEnviar = formularioSolicitud.querySelector('[name="confirmar_solicitud"]');

            formularioSolicitud.requestSubmit(botonEnviar);

        }, 240);

    });

}

