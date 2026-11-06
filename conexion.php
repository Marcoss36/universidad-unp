<?php
$host = "b6p6oz7mnrce350oem9f-mysql.services.clever-cloud.com";
$user = "uhavndk8sr1l2xci";
$password = "q5ipQtnEUt4YQFnlqSHn";
$dbname = "b6p6oz7mnrce350oem9f";
$port = 3306;

$conexion = mysqli_connect($host, $user, $password, $dbname, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
