<?php
    //Librerías
    include 'sunra/php-simple-html-dom-parser/Src/Sunra/PhpSimple/HtmlDomParser.php';
    include 'nesbot/carbon/src/Carbon/Carbon.php';
    include 'extras/Extras.php';
    include 'conexion/conectarSQL.php';
    include 'conexion/conexion.php';

    $conection = conectar(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE)or die('No pudo conectarse : '. mysqli_error());

    $object = new Extras($conection);
    $object->sendNumber();



 
 ?>