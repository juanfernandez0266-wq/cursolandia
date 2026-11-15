
// CONTROLAMOS LAS PESTAÑAS DE MIS CURSOS

let pestanasCursos = document.querySelectorAll(".mis-cursos-pestana");
let seccionesCursos = document.querySelectorAll(".mis-cursos-seccion");


// MOSTRAMOS LA SECCION SELECCIONADA

function mostrarSeccionCursos(nombreSeccion) {

    pestanasCursos.forEach(function (pestana) {

        let seleccionada = pestana.dataset.pestana === nombreSeccion;

        pestana.classList.toggle("activa", seleccionada);
        pestana.setAttribute("aria-selected", seleccionada ? "true" : "false");

    });


    // OCULTAMOS LAS DEMAS SECCIONES

    seccionesCursos.forEach(function (seccion) {

        seccion.hidden = seccion.id !== "seccion-" + nombreSeccion;

    });

}


// ACTIVAMOS LAS PESTAÑAS

pestanasCursos.forEach(function (pestana) {

    pestana.addEventListener("click", function () {

        mostrarSeccionCursos(pestana.dataset.pestana);

    });

});


// ANIMAMOS EL CONTENIDO AL INGRESAR A MIS CURSOS

window.addEventListener("pageshow", function () {

    let contenido = document.querySelector(".mis-cursos-contenido");

    if (contenido == null) {
        return;
    }

    contenido.animate([

        {
            opacity: 0,
            transform: "translateY(-28px)",
            offset: 0
        },

        {
            opacity: 0.45,
            offset: 0.45
        },

        {
            opacity: 1,
            transform: "translateY(0)",
            offset: 1
        }

    ], {
        duration: 550,
        easing: "cubic-bezier(0.22, 0.61, 0.36, 1)",
        iterations: 1
    });

});
