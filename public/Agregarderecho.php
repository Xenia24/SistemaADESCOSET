<?php
session_start();
include('../includes/db.php'); // Incluye la conexión a la base de datos

// Verificar si el formulario de login ha sido enviado
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

// Verificar si el formulario para agregar derechohabiente ha sido enviado
if (isset($_POST['guardar'])) {
    $codigo = $_POST['codigo'];
    $nombre_completo = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $identificacion = $_POST['identificacion'];
    $estado = $_POST['estado'];
    $tipo_derechohabiente = $_POST['tipo_derecho'];

    try {
        $stmt = $pdo->prepare("INSERT INTO agregarderechohabiente (codigo, nombre_completo, identificacion, direccion, estado, telefono, tipo_derechohabiente) 
                               VALUES (:codigo, :nombre_completo, :identificacion, :direccion, :estado, :telefono, :tipo_derechohabiente)");
        
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':identificacion', $identificacion);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':tipo_derechohabiente', $tipo_derechohabiente);

        if ($stmt->execute()) {
            $success = "¡Registro guardado exitosamente!";
        } else {
            $error_agregar = "Error al guardar el registro.";
        }
    } catch (PDOException $e) {
        $error_agregar = "Error: " . $e->getMessage();
    }
}
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
            flex-direction: column;
            height: 100vh;
            background-color: #E0F7FA;
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

        .admin-container span {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
        }

        .icon {
            font-size: 18px;
        }

        .admin-container a {
            text-decoration: none;
            background-color: red;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
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
            width: 120px;
            margin: 0 auto 20px auto;
            display: block;
            border-radius: 10px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        .sidebar a img {
            width: 20px;
            height: 20px;
        }

        .submenu {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding-left: 20px;
        }

        .submenu a {
            font-size: 14px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .submenu a img {
            width: 16px;
            height: 16px;
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

        input, select {
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

        .btn-cancel:hover {
            background-color: darkred;
        }

        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .estado-container {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }

        .estado-container label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .estado-container input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkmark {
            height: 20px;
            width: 20px;
            border-radius: 5px;
            display: inline-block;
            border: 2px solid #ccc;
            background-color: #f4f4f4;
            transition: all 0.2s ease-in-out;
        }

        #activo:checked + .checkmark {
            background-color: green;
            border-color: green;
        }

        #inactivo:checked + .checkmark {
            background-color: red;
            border-color: red;
        }
    </style>
</head>

<body>
    <!-- Barra superior -->
    <div class="top-bar">
        <h2>Sistema de Cobro</h2>
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
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Cobro</h3>

            <a href="dashboard.php">
                <img src="../Image/hogarM.png" alt="Inicio"> Inicio
            </a>

            <a href="derechohabiente.php">
                <img src="../Image/avatar1.png" alt="Tipo de derechohabiente"> Tipo de derechohabiente ⏷
            </a>

            <div class="submenu">
                <a href="Agregarderecho.php">
                    <img src="../Image/nuevo-usuario.png" alt="Agregar derechohabiente"> Agregar derechohabiente
                </a>
                <a href="Natural.php">
                    <img src="../Image/usuario1.png" alt="Natural"> Natural
                </a>
                <a href="juridica.php">
                    <img src="../Image/grandes-almacenes.png" alt="Jurídica"> Jurídica
                </a>
            </div>

            <a href="recibo.php">
                <img src="../Image/factura.png" alt="Recibo"> Recibo
            </a>

            <a href="listado.php">
                <img src="../Image/lista.png" alt="Listado"> Listado
            </a>

            <a href="reporte.php">
                <img src="../Image/reporte.png" alt="Reporte"> Reporte
            </a>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <h1>Agregar Derechohabiente</h1>

            <?php if (isset($success)) : ?>
                <p class="success"><?= $success ?></p>
            <?php elseif (isset($error_agregar)) : ?>
                <p class="error"><?= $error_agregar ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="number" id="codigo" name="codigo" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" required>
                    </div>

                    <div class="form-group">
                        <label for="identificacion">Identificación</label>
                        <input type="text" id="identificacion" name="identificacion" required>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <div class="estado-container">
                            <label>
                                <input type="checkbox" id="activo" name="estado" value="activo" onclick="seleccionarUnico(this)">
                                <span class="checkmark"></span> Activo
                            </label>
                            <label>
                                <input type="checkbox" id="inactivo" name="estado" value="inactivo" onclick="seleccionarUnico(this)">
                                <span class="checkmark"></span> Inactivo
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tipo_derecho">Tipo de derechohabiente</label>
                        <select id="tipo_derecho" name="tipo_derecho" required>
                            <option value="natural">Natural</option>
                            <option value="juridica">Jurídica</option>
                        </select>
                    </div>

                    <div class="buttons">
                        <button type="reset" class="btn btn-cancel">Cancelar</button>
                        <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        function seleccionarUnico(elemento) {
            var checkboxes = document.querySelectorAll('input[name="estado"]');
            checkboxes.forEach(function (cb) {
                if (cb !== elemento) {
                    cb.checked = false;
                }
            });
        }
    </script>
</body>
</html>
