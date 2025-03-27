<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cobro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sidebar img {
            width: 100%;
            max-width: 150px;
            margin: 0 auto;
            display: block;
        }
        .sidebar a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background-color: #007c91;
        }
        .logout {
            margin-top: auto;
            background-color: red;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .logout a {
            color: white;
            text-decoration: none;
        }
        .logout a:hover {
            background-color: darkred;
        }
        .content {
            flex: 1;
            background-color: white;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Sistema de Cobro</h2>
        <img src="logoadesco.jpg" alt="Imagen de código" width="200">
        <a href="#">🏠 Inicio</a>
        <a href="Agregarderecho.php">👤 Tipo de derechohabiente</a>
        <a href="#">➕ Agregar derechohabiente</a>
        <a href="#">📌 Natural</a>
        <a href="#">📌 Jurídica</a>
        <a href="#">🧾 Recibo</a>
        <a href="#">📋 Listado</a>
        <a href="#">📊 Reporte</a>
        <div class="logout">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
    <div class="content">
        <h1>Bienvenido al Sistema de Cobro</h1>
    </div>
</body>
</html>

