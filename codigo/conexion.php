<?php
$host = "localhost";
$user = "root";
$password = ""; // Cambia esto si tu MySQL tiene contraseña
$dbname = "barberia";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
// Establecer el conjunto de caracteres para evitar problemas con tildes y ñ
$conn->set_charset("utf8");
?>