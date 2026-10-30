<?php

session_start();

require "clases/notificacion.php";



// COMPROBAR SESION

if (!isset($_SESSION["idUsuario"])) {

    echo json_encode(array(
        "noLeidas" => 0
    ));

    exit;
}



// RECUPERAR IDS

if (!isset($_POST["ids"])) {

    echo json_encode(array(
        "noLeidas" => Notificacion::contarNoLeidas($_SESSION["idUsuario"])
    ));

    exit;
}


$ids = json_decode($_POST["ids"], true);


if (!is_array($ids)) {

    echo json_encode(array(
        "noLeidas" => Notificacion::contarNoLeidas($_SESSION["idUsuario"])
    ));

    exit;
}



// MARCAR COMO LEIDAS

Notificacion::marcarComoLeidas(
    $_SESSION["idUsuario"],
    $ids
);



// CONSULTAR CUANTAS QUEDAN

$cantidadNoLeidas = Notificacion::contarNoLeidas(
    $_SESSION["idUsuario"]
);


echo json_encode(array(
    "noLeidas" => $cantidadNoLeidas
));

?>