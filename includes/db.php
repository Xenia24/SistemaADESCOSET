<?php
$host = 'localhost';
$usuario = 'root';  // Usuario por defecto de XAMPP
$contrasena = '';   // Contraseña por defecto de XAMPP
$baseDeDatos = 'ADESCOSET_bd';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$baseDeDatos", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
