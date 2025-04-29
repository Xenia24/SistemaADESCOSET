<?php
session_start();
include('../includes/db.php'); // Incluye la conexión a la base de datos

//Verificar si el formulario de login ha sido enviado
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
        header('Location: dashboard2.php');
        exit();
    } else {
        $error = "Llenar todos los campos";
    }
    
}

// Verificar si el formulario para agregar derechohabiente ha sido enviado
if (isset($_POST['guardar'])) {
    $nombre_completo = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $numero_dui = $_POST['numero_dui'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $contraseña = $_POST['contraseña'];
    // $repetir_contraseña = $_POST['repetir_contraseña']; // Ya no es necesario aquí
    $estado = $_POST['estado'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Verificar que las contraseñas coincidan antes de procesar
    if ($contraseña != $_POST['repetir_contraseña']) {
        $error_agregar = "Las contraseñas no coinciden.";
    } else {
        try {

            $contraseña_cifrada = password_hash($contraseña, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO usuariosag (nombre_completo, correo, telefono, numero_dui, nombre_usuario, contraseña, estado, tipo_usuario) 
            VALUES (:nombre_completo, :correo, :telefono, :numero_dui, :nombre_usuario, :password, :estado, :tipo_usuario)");

            $stmt->bindParam(':nombre_completo', $nombre_completo);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':numero_dui', $numero_dui);
            $stmt->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt->bindParam(':password', $contraseña_cifrada);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);

            if ($stmt->execute()) {
                $success = "¡Registro guardado exitosamente!";
            } else {
                $error_agregar = "Error al guardar el registro.";
            }
        } catch (PDOException $e) {
            $error_agregar = "Error: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario</title>
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
            height: 80px;
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

        #activo:checked+.checkmark {
            background-color: green;
            border-color: green;
        }

        #inactivo:checked+.checkmark {
            background-color: red;
            border-color: red;
        }




        /* Estilo para el formulario */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Dos columnas iguales */
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .estado-container {
            display: flex;
            gap: 10px;
        }

        .estado-container label {
            display: flex;
            align-items: center;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
        }

        .full-width {
            grid-column: span 2;
            /* Toma ambas columnas en la fila */
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                /* Una sola columna en pantallas pequeñas */
            }

            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Barra superior -->
    <div class="top-bar">
        <h2>Sistema de Inventario</h2>
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
            <h3>Sistema de Inventario</h3>

            <a href="dashboard2.php">
                <img src="../Image/hogarM.png" alt="Inicio"> Inicio
            </a>

            <a href="">
                <img src="../Image/avatar1.png" alt="usuarios"> Usuarios ⏷
            </a>

            <div class="submenu">
                <a href="AgregarUsuario.php">
                    <img src="../Image/nuevo-usuario.png" alt="Agregar Usuario"> Agregar Usuario
                </a>
                <a href="">
                    <img src="../Image/usuario1.png" alt="Administradores"> Administradores
                </a>
                <a href="ListGeneral.php">
                    <img src="../Image/grandes-almacenes.png" alt="Usuarios"> Usuarios
                </a>
            </div>

            <a href="">
                <img src="../Image/factura.png" alt="Recibo"> Categorias
            </a>

            <a href="">
                <img src="../Image/lista.png" alt="Listado"> Productos
            </a>

            <a href="">
                <img src="../Image/reporte.png" alt="Reporte"> Reportes
            </a>
        </div>

         <!-- Contenido principal -->

         <div class="content">
            <h2>Agregar Usuario</h2>

            <?php if (isset($success)) : ?>
                <p class="success"><?= $success ?></p>
            <?php elseif (isset($error_agregar)) : ?>
                <p class="error"><?= $error_agregar ?></p>
            <?php endif; ?>

            <div class="form-container">
                <h3 style="text-decoration: underline;">Datos Basicos</h3>
                <form method="POST" action="" onsubmit="return validarContrasenas();">
                    <div class="form-row">

                        <div class="form-group">
                            <label for="nombre_completo">Nombre Completo</label>
                            <input type="text" id="nombre_completo" name="nombre_completo" required>
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo</label>
                            <input type="text" id="correo" name="correo" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" required>
                        </div>

                        <div class="form-group">
                            <label for="numero_dui">Numero de DUI</label>
                            <input type="text" id="numero_dui" name="numero_dui" required>
                        </div>
                    </div>
                    <h3 style="text-decoration: underline;">Datos de la Cuenta</h3>
                    <div class="form-row" style="margin-top: 10px;">
                        <div class="form-group">
                            <label for="nombre_usuario">Nombre de Usuario</label>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                        </div>

                        <div class="form-group">
                            <label for="contraseña">Contraseña</label>
                            <input type="password" id="contraseña" name="contraseña" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="repetir_contraseña">Repetir Contraseña</label>
                            <input type="password" id="repetir_contraseña" name="repetir_contraseña" required>
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
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="tipo_usuario">Tipo de Usuario</label>
                            <select id="tipo_usuario" name="tipo_usuario" required>
                                <option value="Administrador">Administrador</option>
                                <option value="General">Usuario General</option>
                            </select>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="reset" class="btn btn-cancel">Cancelar</button>
                        <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
                    </div>
                </form>
            </div>
        </div>


        <script>
            // Función para validar que las contraseñas coincidan
            function validarContrasenas() {
                var password = document.getElementById('contraseña').value;
                var passwordRepeat = document.getElementById('repetir_contraseña').value;

                if (password !== passwordRepeat) {
                    alert('Las contraseñas no coinciden. Por favor, intente nuevamente.');
                    return false;
                }
                return true;
            }
        </script>

    </div>

    </div>

       
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        function seleccionarUnico(elemento) {
            var checkboxes = document.querySelectorAll('input[name="estado"]');
            checkboxes.forEach(function(cb) {
                if (cb !== elemento) {
                    cb.checked = false;
                }
            });
        }
    </script>
</body>

</html>