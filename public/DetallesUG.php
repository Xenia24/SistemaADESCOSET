<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se envió el parámetro 'codigo' desde el icono de ver
if (isset($_GET['id'])) {
    $codigo = $_GET['id'];

    // Obtener datos del derechohabiente JURÍDICO desde la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuariosag WHERE id = :id AND tipo_usuario = 'General'");
    $stmt->bindParam(':id', $codigo, PDO::PARAM_INT);
    $stmt->execute();

    $derechohabiente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no encuentra el derechohabiente jurídico, mostrar mensaje de error
    if (!$derechohabiente) {
        echo "<script>alert('¡No se encontró información para el Usuario seleccionado!'); window.location.href='ListAdministrador.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Código no recibido.'); window.location.href='ListAdministrador.php';</script>";
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

        .container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar img.logo {
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        .sidebar a img {
            width: 20px;
            height: 20px;
        }

        .content {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
            overflow-x: auto;
        }

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

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

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

        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }

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

    <div class="top-bar">
        <h2><i class="fas fa-info-circle"></i> Detalles Usuario General</h2>
        <div class="admin-container">
            <span class="icon">🔄</span>
            <span>Admin name 👤</span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <div class="container">
    <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Inventario</h3>
            <a href="dashboard2.php"><img src="../Image/hogarM.png" alt="Inicio"> Inicio</a>
            <a href=""><img src="../Image/avatar1.png" alt="Tipo"> Usuarios ⏷</a>
            <a href="AgregarUsuario.php"><img src="../Image/nuevo-usuario.png" alt="Agregar"> Agregar Usuario</a>
            <a href="ListAdministrador.php"><img src="../Image/usuario1.png" alt="Natural"> Administrador</a>
            <a href=""><img src="../Image/grandes-almacenes.png" alt="Jurídica"> Usuario General</a>
            <a href=""><img src="../Image/factura.png" alt="Recibo"> Categorias</a>
            <a href=""><img src="../Image/lista.png" alt="Listado"> Productos</a>
            <a href=""><img src="../Image/reporte.png" alt="Reporte"> Reportes</a>
        </div>

        


        <div class="content">
            <div class="detalle-container">
                <div class="detalle-header">Información de Usuario General</div>
                <div class="detalle-row">
                    <!-- <div class="detalle-col">
                        <label>Código</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['id']) ?></div>
                    </div> -->
                    <div class="detalle-col">
                        <label>Nombre Completo</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['nombre_completo']) ?></div>
                    </div>
                    
                </div>
                <div class="detalle-row">
                <div class="detalle-col">
                        <label>Correo:</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['correo']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Estado</label>
                        <div class="checkbox-container">
                            <input type="checkbox" <?= $derechohabiente['estado'] == 'activo' ? 'checked' : '' ?> disabled>
                            <span><?= htmlspecialchars(ucfirst($derechohabiente['estado'])) ?></span>
                        </div>
                    </div>
                </div>
                <div class="detalle-row">
                    <div class="detalle-col">
                        <label>Telefono</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['telefono']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Numero de DUI</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['numero_dui']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Nombre Usuario</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['nombre_usuario']) ?></div>
                    </div>
                    <div class="detalle-col">
                        <label>Tipo de Usuario</label>
                        <div class="detalle-info"><?= htmlspecialchars(ucfirst($derechohabiente['tipo_usuario'])) ?></div>
                    </div>
                </div>
                <!-- <div class="detalle-row">
                    <div class="detalle-col">
                        <label>Teléfono</label>
                        <div class="detalle-info"><?= htmlspecialchars($derechohabiente['telefono']) ?></div>
                    </div>
                </div> -->
                <a href="ListGeneral.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
</body>

</html>
