// NOTIFICACIONES RAPIDAS

let botonCampana = document.getElementById("botonCampana");
let panelNotificaciones = document.getElementById("panelNotificaciones");
let iconoCampana = document.getElementById("iconoCampana");



if (botonCampana != null) {


    // ABRIR O CERRAR PANEL

    botonCampana.addEventListener("click", function () {

        panelNotificaciones.classList.toggle("abierto");


        // SI SE ABRIO EL PANEL

        if (panelNotificaciones.classList.contains("abierto")) {

            let elementos = document.querySelectorAll(".notificacion-rapida");

            let ids = [];


            elementos.forEach(function (elemento) {

                ids.push(elemento.dataset.id);

            });


            // MARCAR COMO LEIDAS LAS NOTIFICACIONES MOSTRADAS

            if (ids.length > 0) {

                let datos = new FormData();

                datos.append("ids", JSON.stringify(ids));


                fetch("marcar_notificaciones_leidas.php", {
                    method: "POST",
                    body: datos
                })

                .then(function (respuesta) {
                    return respuesta.json();
                })

                .then(function (resultado) {


                    // SI TODAVIA QUEDAN NOTIFICACIONES NO LEIDAS

                    if (resultado.noLeidas > 0) {

                        iconoCampana.src = "imagenes/notificaciones/campana_notificacion.svg";

                    }


                    // SI YA NO QUEDAN

                    else {

                        iconoCampana.src = "imagenes/notificaciones/campana.svg";

                    }

                });

            }

        }

    });

}