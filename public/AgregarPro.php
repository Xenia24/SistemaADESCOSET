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

if (isset($_POST['guardar'])) {
    $nombre_producto = $_POST['nombre_producto'];
    $cantidad = $_POST['cantidad'];
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];

    try {
        // Preparar sentencia SQL para insertar producto
        $stmt = $pdo->prepare("INSERT INTO productos (nombre_producto, cantidad, precio, categoria) 
                               VALUES (:nombre_producto, :cantidad, :precio, :categoria)");

        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':cantidad', $cantidad);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            height: 60px;
            background-color: #0097A7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            /* ← CAMBIO AQUÍ */
            top: 0;
            left: 0;
            z-index: 1000;
            /* Asegura que esté sobre otros elementos */
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
            width: 230px;
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
            margin-left: 270px;
            /* espacio para el sidebar */
            margin-top: 80px;
            /* espacio para la top-bar */
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

        .sidebar {
            width: 250px;
            transition: all 0.3s ease;
        }

        .sidebar.hidden {
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        .content {
            transition: margin-left 0.3s ease;
        }

        .content.sidebar-hidden {
            margin-left: 0;
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

            <a href="#" class="toggle-submenu">
                <img src="../Image/avatar1.png" alt="usuarios"> Usuarios ⏷
            </a>

            <div class="submenu" id="submenu-usuarios" style="display: none;">
                <a href="AgregarUsuario.php">
                    <img src="../Image/nuevo-usuario.png" alt="Agregar Usuario"> Agregar Usuario
                </a>
                <a href="ListAdministrador.php">
                    <img src="../Image/usuario1.png" alt="Administradores"> Administradores
                </a>
                <a href="ListGeneral.php">
                    <img src="../Image/grandes-almacenes.png" alt="Usuarios"> Usuarios
                </a>
            </div>


            <a href="AgregarCat.php">
                <img src="../Image/factura.png" alt="Categorias"> Categorias
            </a>

            <a href="#" class="toggle-submenu2">
                <img src="../Image/lista.png" alt="Listado"> Productos ⏷
            </a>


            <div class="submenu" id="submenu-productos" style="display: none;">
                <a href="ListProductos.php">
                    <img src="../Image/lista.png" alt="Listado"> Lista de Productos
                </a>
                <a href="AgregarPro.php">
                    <img src="../Image/lista.png" alt="Agregar Producto"> Agregar Producto
                </a>
                <a href="">
                    <img src="../Image/lista.png" alt="Listado"> Retirar Productos
                </a>

            </div>


            <a href="">
                <img src="../Image/reporte.png" alt="Reporte"> Reportes
            </a>
        </div>

        <!-- Contenido principal -->

        <div class="content">
            <h2>Agregar Producto</h2>

            <?php if (isset($success)) : ?>
                <p class="success"><?= $success ?></p>
            <?php elseif (isset($error_agregar)) : ?>
                <p class="error"><?= $error_agregar ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <h3 style="text-decoration: underline;">Datos</h3>

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
                                        echo "<option value=\"" . htmlspecialchars($row['nombre_categoria']) . "\">" . htmlspecialchars($row['nombre_categoria']) . "</option>";
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
                            <label for="cantidad">Cantidad</label>
                            <input type="number" id="cantidad" name="cantidad" min="1" step="1" required>
                        </div>
                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" id="precio" name="precio" min="0.01" step="0.01" required>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="reset" class="btn btn-cancel">Cancelar</button>
                        <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    </div>


    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleLink = document.querySelector(".toggle-submenu");
            const submenu = document.getElementById("submenu-usuarios");

            toggleLink.addEventListener("click", function(e) {
                e.preventDefault();
                submenu.style.display = submenu.style.display === "none" ? "flex" : "none";
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
        const toggles = document.querySelectorAll(".toggle-submenu2");

        toggles.forEach(function (toggle) {
            toggle.addEventListener("click", function (e) {
                e.preventDefault();
                const nextSubmenu = toggle.nextElementSibling;
                if (nextSubmenu && nextSubmenu.classList.contains("submenu")) {
                    nextSubmenu.style.display = nextSubmenu.style.display === "none" ? "flex" : "none";
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
                const toggleBtn = document.getElementById("toggleSidebarBtn");
                const sidebar = document.querySelector(".sidebar");
                const content = document.querySelector(".content");

                toggleBtn.addEventListener("click", () => {
                    sidebar.classList.toggle("hidden");
                    content.classList.toggle("sidebar-hidden");
                });
            });
    </script>
</body>

</html>