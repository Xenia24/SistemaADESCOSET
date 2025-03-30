<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se envió el parámetro 'codigo' desde el icono de ver
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];

    // Obtener datos del derechohabiente JURÍDICO desde la base de datos
    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE codigo = :codigo AND tipo_derechohabiente = 'juridica'");
    $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
    $stmt->execute();

    $derechohabiente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no encuentra el derechohabiente jurídico, mostrar mensaje de error
    if (!$derechohabiente) {
        echo "<script>alert('¡No se encontró información para el derechohabiente seleccionado!'); window.location.href='juridica.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Código no recibido.'); window.location.href='juridica.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Derechohabiente Jurídico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f4f4f4;
        }

        /* Barra superior */
        .top-bar {
            width: 100%;
            height: 60px;
            background-color: #0097A7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            color: white;
        }

        .top-bar h2 {
            font-size: 18px;
        }

        .admin-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-container a {
            text-decoration: none;
            background-color: red;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .admin-container a:hover {
            background-color: darkred;
        }

        /* Contenedor principal */
        .container {
            display: flex;
            flex: 1;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar img {
            width: 100px;
            margin: 0 auto 15px auto;
            display: block;
            border-radius: 10px;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 15px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        /* Contenido principal */
        .content {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
            overflow-x: auto;
        }

        /* Estilo del detalle */
        .detalle-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
        }

        .detalle-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .detalle-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .detalle-col {
            width: 48%;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        .detalle-info {
            padding: 8px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        /* Estilo para el checkbox */
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        /* Botón volver */
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #5bc0de;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn-back:hover {
            background-color: #46b8da;
        }

        /* Barra inferior */
        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }

        /* Diseño responsivo */
        @media (max-width: 768px) {
            .detalle-col {
                width: 100%;
                margin-bottom: 10px;
            }

            .detalle-container {
                padding: 15px;
            }

            .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- Barra superior -->
    <div class="top-bar">
        <h2><i class="fas fa-info-circle"></i> Detalle Derechohabiente Jurídico</h2>
        <div class="admin-container">
            <span class="icon">🔄</span>
            <span>Admin name 👤</span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET">
            <h3>Sistema de Cobro</h3>
            <a href="dashboard.php">🏠 Inicio</a>
            <a href="derechohabiente.php">👤 Tipo de derechohabiente ⏷</a>
            <a href="Agregarderecho.php">➕ Agregar derechohabiente</a>
            <a href="natural.php">📌 Natural</a>
            <a href="juridica.php">📌 Jurídica</a>
            <a href="recibo.php">🧾 Recibo</a>
            <a href="listado.php">📋 Listado</a>
            <a href="reporte.php">📊 Reporte</a>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <div class="detalle-container">
                <div class="detalle-header">Información de Derechohabiente Jurídico</div>

                <!-- Primera fila -->
                <div class="detalle-row">
                    <div class="detalle-col">
                        <label>Código</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['codigo']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Identificación:</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['identificacion']) ?></div>
                    </div>
                </div>

                <!-- Segunda fila -->
                <div class="detalle-row">
                    <div class="detalle-col">
                        <label>Nombre Completo</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['nombre_completo']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Estado</label>
                        <div class="checkbox-container">
                            <input type="checkbox" <?= $derechohabiente['estado'] == 'activo' ? 'checked' : '' ?> disabled>
                            <span><?= htmlspecialchars(ucfirst($derechohabiente['estado'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Tercera fila -->
                <div class="detalle-row">
                    <div class="detalle-col">
                        <label>Dirección</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['direccion']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Tipo de derechohabiente</label>
                        <div class="detalle-info"><?= htmlspecialchars(ucfirst($derechohabiente['tipo_derechohabiente'])) ?></div>
                    </div>
                </div>

                <!-- Cuarta fila -->
                <div class="detalle-row">
                    <div class="detalle-col">
                        <label>Teléfono</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['telefono']) ?></div>
                    </div>
                </div>

                <!-- Botón para volver -->
                <a href="juridica.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>
        </div>
    </div>

    <!-- Barra inferior -->
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

</body>

</html>
