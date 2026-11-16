
// CAMBIAMOS LA IMAGEN PRINCIPAL DEL CURSO

let imagenDetalle = document.getElementById("imagenDetallePrincipal");
let miniaturasDetalle = document.querySelectorAll(".detalle-miniatura");

miniaturasDetalle.forEach(function (miniatura) {

    miniatura.addEventListener("click", function () {

        if (imagenDetalle === null) return;

        imagenDetalle.src = miniatura.dataset.imagen;

        miniaturasDetalle.forEach(function (otra) {
            otra.classList.remove("activa");
            otra.setAttribute("aria-pressed", "false");
        });

        miniatura.classList.add("activa");
        miniatura.setAttribute("aria-pressed", "true");

    });

});


// ANIMAMOS EL CONTENIDO AL INGRESAR

window.addEventListener("pageshow", function () {

    let contenido = document.querySelector(".detalle-curso-contenido");

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

    // MOSTRAMOS LA CONFIRMACION CUANDO PHP GUARDO LA INSCRIPCION

    let ventana = document.getElementById("modalInscripcion") || document.getElementById("modalInscripcionPendiente");

    if (ventana !== null) {
        bootstrap.Modal.getOrCreateInstance(ventana).show();
    }

});

