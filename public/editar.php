<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se está editando un registro
$modo_edicion = false;
$derechohabiente = [
    'codigo' => '',
    'nombre_completo' => '',
    'direccion' => '',
    'telefono' => '',
    'identificacion' => '',
    'estado' => '',
    'tipo_derechohabiente' => 'natural'
];

// Obtener datos del derechohabiente para edición
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];

    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
    $stmt->execute();
    $derechohabiente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($derechohabiente) {
        $modo_edicion = true;
    } else {
        echo "<script>alert('¡No se encontró el derechohabiente!'); window.location.href='natural.php';</script>";
        exit();
    }
}

// Procesar el formulario para guardar o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $nombre_completo = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $identificacion = $_POST['identificacion'];
    $estado = $_POST['estado'];
    $tipo_derechohabiente = $_POST['tipo_derecho'];

    try {
        if ($modo_edicion) {
            // Actualizar derechohabiente existente
            $stmt = $pdo->prepare("UPDATE agregarderechohabiente SET 
                                    nombre_completo = :nombre_completo,
                                    direccion = :direccion,
                                    telefono = :telefono,
                                    identificacion = :identificacion,
                                    estado = :estado,
                                    tipo_derechohabiente = :tipo_derechohabiente
                                    WHERE codigo = :codigo");

            $mensaje_exito = "¡Registro actualizado exitosamente!";
        } else {
            // Insertar nuevo derechohabiente
            $stmt = $pdo->prepare("INSERT INTO agregarderechohabiente 
                                    (codigo, nombre_completo, identificacion, direccion, estado, telefono, tipo_derechohabiente)
                                    VALUES (:codigo, :nombre_completo, :identificacion, :direccion, :estado, :telefono, :tipo_derechohabiente)");
            
            $mensaje_exito = "¡Registro guardado exitosamente!";
        }

        // Vincular valores
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':identificacion', $identificacion);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':tipo_derechohabiente', $tipo_derechohabiente);

        // Ejecutar consulta
        if ($stmt->execute()) {
            echo "<script>alert('$mensaje_exito'); window.location.href='natural.php';</script>";
        } else {
            echo "<script>alert('Error al guardar los cambios.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente</title>
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
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        /* Contenido principal */
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

        input,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
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

        .btn-cancel {
            background-color: red;
            color: white;
        }

        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }
    </style>
</head>

<body>

    <!-- Barra superior -->
    <div class="top-bar">
        <h2><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente</h2>
        <div class="admin-container">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Sidebar (Menú) -->
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
            <h1><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente</h1>

            <!-- Formulario para editar/agregar derechohabiente -->
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="number" id="codigo" name="codigo" value="<?= htmlspecialchars($derechohabiente['codigo']) ?>" required <?= $modo_edicion ? 'readonly' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($derechohabiente['nombre_completo']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($derechohabiente['direccion']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($derechohabiente['telefono']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="identificacion">Identificación</label>
                        <input type="text" id="identificacion" name="identificacion" value="<?= htmlspecialchars($derechohabiente['identificacion']) ?>" required>
                    </div>

                    <!-- Estado -->
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="activo" <?= $derechohabiente['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $derechohabiente['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <!-- Tipo de derechohabiente -->
                    <div class="form-group">
                        <label for="tipo_derecho">Tipo de derechohabiente</label>
                        <select id="tipo_derecho" name="tipo_derecho" required>
                            <option value="natural" <?= $derechohabiente['tipo_derechohabiente'] == 'natural' ? 'selected' : '' ?>>Natural</option>
                            <option value="juridica" <?= $derechohabiente['tipo_derechohabiente'] == 'juridica' ? 'selected' : '' ?>>Jurídica</option>
                        </select>
                    </div>

                    <div class="buttons">
                        <a href="natural.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" class="btn btn-save"><?= $modo_edicion ? 'Actualizar' : 'Guardar' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
</body>

</html>
