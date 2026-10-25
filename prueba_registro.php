<?php

require "clases/usuario.php";

$resultado = usuario::registrar(
    "Juan",
    "Perez",
    "juan@gmail.com",
    "juan123",
    "Juan1234"
);

var_dump($resultado);

?>