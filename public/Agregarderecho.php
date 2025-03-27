<?php
session_start();
include('../includes/db.php');

if (isset($_POST['submit'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && md5($contrasena) == $usuario['contrasena']) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Llenar todos los campos";
    }
}
?><!DOCTYPE html>
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
            background-color: #E0F7FA;
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
            border-radius: 10px;
            margin: 20px;
        }
        .form-container {
            background: #F1F1F1;
            padding: 20px;
            border-radius: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .estado-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .estado-container label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .estado-container input {
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            cursor: pointer;
        }
        .estado-container input[value="activo"] {
            background-color: green;
        }
        .estado-container input[value="inactivo"] {
            background-color: red;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-save {
            background-color: #0097A7;
            color: white;
        }
        .btn-save:hover {
            background-color: #007c91;
        }
        .btn-cancel {
            background-color: red;
            color: white;
        }
        .btn-cancel:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Sistema de Cobro</h2>
        <img src="logo.png" alt="Logo">
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
        <h1>Agregar derecho  probando si se cambiaAmbiente</h1>
        <div class="form-container">
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo">
            </div>
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre">
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="identificacion">Identificación</label>
                <input type="text" id="identificacion" name="identificacion">
            </div>
            <div class="form-group estado-container">
                <label>Estado:</label>
                <label><input type="radio" name="estado" value="activo"> Activo</label>
                <label><input type="radio" name="estado" value="inactivo"> Inactivo</label>
            </div>
            <div class="form-group">
                <label for="tipo_derecho">Tipo de derecho Ambiente</label>
                <input type="text" id="tipo_derecho" name="tipo_derecho">
            </div>
            <div class="buttons">
                <button class="btn btn-cancel">Cancelar</button>
                <button class="btn btn-save">Guardar</button>
            </div>
        </div>
    </div>
</body>
</html>
