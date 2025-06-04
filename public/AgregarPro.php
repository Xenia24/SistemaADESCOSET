<?php
session_start();
include('../includes/db.php'); // Incluye la conexión a la base de datos

// Verificar si el formulario de login ha sido enviado (aunque aquí en AgregarPro.php realmente estamos guardando producto)
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

if (isset($_POST['guardar'])) {
    $nombre_producto = $_POST['nombre_producto'];
    $precio           = $_POST['precio'];
    $categoria        = $_POST['categoria'];

    try {
        // Preparar sentencia SQL para insertar producto
        $stmt = $pdo->prepare("INSERT INTO productos (nombre_producto, precio, categoria) 
                               VALUES (:nombre_producto, :precio, :categoria)");

        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':categoria', $categoria);

        if ($stmt->execute()) {
            $success = "¡Producto guardado exitosamente!";
        } else {
            $error_agregar = "Error al guardar el producto.";
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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto | Sistema de Inventario</title>
    <style>
        /* ==== RESET GENERAL ==== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #E0F7FA;
        }

        /* ==== TOP BAR ==== */
        .top-bar {
            width: 100%;
            height: 60px;
            background-color: #0097A7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
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

        /* ==== LAYOUT PRINCIPAL ==== */
        .container {
            display: flex;
            flex: 1;
        }

        /* ==== SIDEBAR ==== */
        .sidebar {
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            width: 250px;
        }

        .sidebar.hidden {
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        .sidebar img.logo {
            width: 120px;
            margin: 0 auto 20px auto;
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

        /* Submenú oculto por defecto */
        .submenu {
            display: none;
            flex-direction: column;
            gap: 5px;
            padding-left: 20px;
            margin-top: 8px;
        }

        /* Submenú cuando tenga la clase show */
        .submenu.show {
            display: flex;
        }

        .submenu a {
            font-size: 14px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .submenu a img {
            width: 16px;
            height: 16px;
        }

        /* ==== CONTENIDO PRINCIPAL ==== */
        .content {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            margin-left: 230px; /* espacio para el sidebar */
            margin-top: 60px;   /* espacio para la top-bar */
            transition: margin-left 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 200px;
        }

        /* Cuando el sidebar esté oculto */
        .content.sidebar-hidden {
            margin-left: 20px;
        }

        /* ==== FORMULARIO DISEÑO FIJO ==== */
        .form-container {
            background: #F1F1F1;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 30px;
            width: 100%;
            max-width: 500px;
        }

        .form-container h3 {
            text-decoration: underline;
            margin-bottom: 10px;
            color: #0097A7;
            text-align: center;
            font-size: 1.4rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Dos columnas iguales */
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #37474F;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0097A7;
            box-shadow: 0 0 6px rgba(0, 151, 167, 0.3);
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, transform 0.1s;
             margin-top: 20px;
        }

        .btn-save {
            background-color: #0097A7;
        }

        .btn-save:hover {
            background-color: #007c91;
            transform: translateY(-2px);
            
        }

        .btn-cancel {
          background-color: #0097A7;
           
        }

        .btn-cancel:hover {
            background-color: darkred;
            transform: translateY(-2px);
        }

        /* ==== MENSAJES ==== */
        .success {
            color: green;
            margin-bottom: 10px;
            text-align: center;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }

        /* ==== BOTTOM BAR ==== */
        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
            margin-top: auto;
        }

        /* ==== RESPONSIVE ==== */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Barra superior -->
    <div class="top-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="margin: 0;">Sistema de Inventario</h2>
            <button id="toggleSidebarBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <span id="fecha-actual" style="margin-left: 20px; font-size: 16px;"></span>
        <div class="admin-container">
            <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤
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

            <a href="#" class="toggle-submenu">
                <i class="fa-solid fa-users"></i> Usuarios ⏷
            </a>
            <div class="submenu" id="submenu-usuarios">
                <a href="AgregarUsuario.php">
                   <i class="fa-solid fa-user-plus"></i> Agregar Usuario
                </a>
                <a href="ListAdministrador.php">
                    <i class="fa-solid fa-user-tie"></i> Administradores
                </a>
                <a href="ListGeneral.php">
                     <i class="fa-solid fa-user-group"></i> Generales
                </a>
            </div>

            <a href="AgregarCat.php">
                <img src="../Image/factura.png" alt="Categorias"> Categorias
            </a>

            <a href="#" class="toggle-submenu2">
                <i class="fa-solid fa-truck"></i> Productos ⏷
            </a>
            <div class="submenu" id="submenu-productos">
                <a href="ListProductos.php">
                    <i class="fa-solid fa-clipboard-list"></i> Lista de Productos
                </a>
                <a href="AgregarPro.php">
                    <i class="fa-solid fa-circle-plus"></i> Agregar Producto
                </a>
                <a href="RetirarPro.php">
                     <i class="fa-solid fa-cart-plus"></i> Retirar Productos
                </a>
            </div>

            <a href="Reportes.php">
                <img src="../Image/reporte.png" alt="Reporte"> Reportes
            </a>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <div class="form-container">
                <h3>Datos del Producto</h3>

                <?php if (isset($success)) : ?>
                    <p class="success"><?= $success ?></p>
                <?php elseif (isset($error_agregar)) : ?>
                    <p class="error"><?= $error_agregar ?></p>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_producto">Nombre Producto</label>
                            <input type="text" id="nombre_producto" name="nombre_producto" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categorías</label>
                            <select id="categoria" name="categoria" required>
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT nombre_categoria FROM categorias");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value=\"" . htmlspecialchars($row['nombre_categoria']) . "\">"
                                            . htmlspecialchars($row['nombre_categoria']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option>Error al cargar categorías</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" id="precio" name="precio" min="0.01" step="0.01" required>
                        </div>
                        <!-- Si en el futuro deseas otro campo, aquí podrías agregarlo en segunda columna -->
                    </div>

                    <div class="buttons">
                        <a href="dashboard2.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Barra inferior -->
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        // Alternar submenú Usuarios
        document.querySelector('.toggle-submenu').addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = document.getElementById('submenu-usuarios');
            submenu.classList.toggle('show');
        });

        // Alternar submenú Productos
        document.querySelector('.toggle-submenu2').addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = document.getElementById('submenu-productos');
            submenu.classList.toggle('show');
        });

        // Alternar sidebar completo
        document.getElementById('toggleSidebarBtn').addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('hidden');
            document.querySelector('.content').classList.toggle('sidebar-hidden');
        });

        // Función para actualizar la fecha actual en la top-bar
        function actualizarFecha() {
            const fechaElemento = document.getElementById("fecha-actual");
            const fecha = new Date();
            const opciones = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            fechaElemento.textContent = fecha.toLocaleDateString('es-ES', opciones);
        }

        document.addEventListener("DOMContentLoaded", function () {
            actualizarFecha(); // Mostrar la fecha al cargar la página

            // Calcular milisegundos hasta medianoche para refrescar
            const ahora = new Date();
            const msHastaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1).getTime() - ahora.getTime();
            setTimeout(() => {
                actualizarFecha();
                setInterval(actualizarFecha, 24 * 60 * 60 * 1000);
            }, msHastaMedianoche);
        });
    </script>
</body>
</html>
