// COMPLETAR REGISTRO


// FOTO DE PERFIL

let FotoPerfil = document.getElementById("FotoPerfil");
let vistaFotoPerfil = document.getElementById("vistaFotoPerfil");


if (FotoPerfil != null) {

    FotoPerfil.addEventListener("change", function () {

        let archivo = FotoPerfil.files[0];


        if (archivo != null) {

            let lector = new FileReader();


            lector.onload = function (evento) {

                vistaFotoPerfil.src = evento.target.result;

            };


            lector.readAsDataURL(archivo);
        }

    });

}



// VALIDAR FECHA ANTES DE AVANZAR

let FechaNacimiento = document.getElementById("FechaNacimiento");
let siguienteFecha = document.getElementById("siguienteFecha");
let errorFecha = document.getElementById("errorFecha");


if (siguienteFecha != null) {

    siguienteFecha.addEventListener("click", function () {


        // NO INGRESO FECHA

        if (FechaNacimiento.value == "") {

            errorFecha.textContent = "Ingresá tu fecha de nacimiento.";
            errorFecha.classList.add("visible");

            FechaNacimiento.classList.remove("campo-error-animacion");

            void FechaNacimiento.offsetWidth;

            FechaNacimiento.classList.add("campo-error-animacion");

            return;
        }


        // MENOR DE 18 AÑOS

        if (FechaNacimiento.value > FechaNacimiento.max) {

            errorFecha.textContent = "Debés tener al menos 18 años.";
            errorFecha.classList.add("visible");

            FechaNacimiento.classList.remove("campo-error-animacion");

            void FechaNacimiento.offsetWidth;

            FechaNacimiento.classList.add("campo-error-animacion");

            return;
        }


        // FECHA CORRECTA

        errorFecha.classList.remove("visible");


        let pasosRegistro = document.getElementById("pasosRegistro");

        let carousel = bootstrap.Carousel.getOrCreateInstance(pasosRegistro);

        carousel.next();

    });

}



// QUITAR ERROR AL CAMBIAR LA FECHA

if (FechaNacimiento != null) {

    FechaNacimiento.addEventListener("change", function () {

        if (FechaNacimiento.value != "" && FechaNacimiento.value <= FechaNacimiento.max) {
            errorFecha.classList.remove("visible");
        }

    });

}





// VALIDAR INTERESES ANTES DE AVANZAR

let siguienteIntereses = document.getElementById("siguienteIntereses");
let grupoIntereses = document.getElementById("grupoIntereses");
let errorIntereses = document.getElementById("errorIntereses");
let intereses = document.querySelectorAll(".interes-check");


if (siguienteIntereses != null) {

    siguienteIntereses.addEventListener("click", function () {

        let interesesSeleccionados = document.querySelectorAll(".interes-check:checked");


        // SI NO SELECCIONO NINGUN INTERES

        if (interesesSeleccionados.length == 0) {

            errorIntereses.classList.add("visible");


            // REINICIAR ANIMACION

            grupoIntereses.classList.remove("intereses-error-animacion");

            void grupoIntereses.offsetWidth;

            grupoIntereses.classList.add("intereses-error-animacion");


            return;
        }


        // SI SELECCIONO AL MENOS UNO

        errorIntereses.classList.remove("visible");


        let pasosRegistro = document.getElementById("pasosRegistro");

        let carousel = bootstrap.Carousel.getOrCreateInstance(pasosRegistro);

        carousel.next();

    });

}



// QUITAR ERROR AL SELECCIONAR UN INTERES

intereses.forEach(function (interes) {

    interes.addEventListener("change", function () {

        let interesesSeleccionados = document.querySelectorAll(".interes-check:checked");


        if (interesesSeleccionados.length > 0) {

            errorIntereses.classList.remove("visible");

        }

    });

});



// QUITAR ERROR AL SELECCIONAR UN INTERES

intereses.forEach(function (interes) {

    interes.addEventListener("change", function () {

        let interesesSeleccionados = document.querySelectorAll(".interes-check:checked");


        if (interesesSeleccionados.length > 0) {

            grupoIntereses.classList.remove("intereses-con-error");
            errorIntereses.classList.remove("visible");

        }

    });

});

// VALIDAR ANTECEDENTES ANTES DE AVANZAR

let Antecedentes = document.getElementById("Antecedentes");
let siguienteAntecedentes = document.getElementById("siguienteAntecedentes");
let errorAntecedentes = document.getElementById("errorAntecedentes");


if (siguienteAntecedentes != null) {

    siguienteAntecedentes.addEventListener("click", function () {


        // NO INGRESO ANTECEDENTES

        if (Antecedentes.value.trim() == "") {

            errorAntecedentes.textContent = "Contanos un poco sobre vos para continuar.";
            errorAntecedentes.classList.add("visible");

            Antecedentes.classList.remove("campo-error-animacion");

            void Antecedentes.offsetWidth;

            Antecedentes.classList.add("campo-error-animacion");

            return;
        }


        // ANTECEDENTES CORRECTOS

        errorAntecedentes.classList.remove("visible");


        let pasosRegistro = document.getElementById("pasosRegistro");

        let carousel = bootstrap.Carousel.getOrCreateInstance(pasosRegistro);

        carousel.next();

    });

}



// QUITAR ERROR AL ESCRIBIR

if (Antecedentes != null) {

    Antecedentes.addEventListener("input", function () {

        if (Antecedentes.value.trim() != "") {
            errorAntecedentes.classList.remove("visible");
        }

    });

}


