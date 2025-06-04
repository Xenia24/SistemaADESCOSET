<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$producto = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "<script>alert('¡Producto no encontrado!'); window.location.href='RetirarPro.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ID de producto no proporcionado.'); window.location.href='RetirarPro.php';</script>";
    exit();
}

// Obtener usuarios disponibles
$usuarios_generales = [];
$usuarios_Empleados = [];

try {
    $stmt_users = $pdo->query("SELECT id, nombre_completo FROM usuariosag WHERE tipo_usuario = 'General Cobro'");
    $usuarios_generales = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

    $stmt_users = $pdo->query("SELECT id, nombre_completo FROM usuariosag WHERE tipo_usuario = 'General Inventario'");
    $usuarios_Empleados = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error al cargar usuarios.');</script>";
}

// Procesar el retiro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirar'])) {
    $cantidad_retirar = (int) $_POST['cantidad'];
    $usuario_id = (int) $_POST['usuario_id'];
    $fecha_venta = $_POST['fecha_venta'];  // Captura la fecha ingresada
    $descripcion = $_POST['descripcion'];

    if ($cantidad_retirar <= 0) {
        echo "<script>alert('La cantidad debe ser mayor a cero.');</script>";
        exit;
    }

    $cantidad_actual = (int) $producto['cantidad'];
    $precio_producto = (float) $producto['precio'];

    if ($cantidad_retirar > $cantidad_actual) {
        echo "<script>alert('No hay suficiente cantidad disponible.');window.location.href='RetirarPro.php';</script>";
        exit;
    }

    $total_venta = $cantidad_retirar * $precio_producto;

    try {
        $pdo->beginTransaction();

        // Restar la cantidad
        $stmt = $pdo->prepare("UPDATE productos SET cantidad = cantidad - :cantidad WHERE id = :id");
        $stmt->bindParam(':cantidad', $cantidad_retirar, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Registrar venta con la fecha proporcionada
        $stmt = $pdo->prepare("INSERT INTO ventas (producto_id, usuario_id, cantidad, precio_unitario, total, fecha, descripcion) 
            VALUES (:producto_id, :usuario_id, :cantidad, :precio_unitario, :total, :fecha, :descripcion)");

        $stmt->bindParam(':producto_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':cantidad', $cantidad_retirar, PDO::PARAM_INT);
        $stmt->bindParam(':precio_unitario', $precio_producto);
        $stmt->bindParam(':total', $total_venta);
        $stmt->bindParam(':fecha', $fecha_venta); // Fecha ingresada
        $stmt->bindParam(':descripcion', $descripcion);

        $stmt->execute();
        $pdo->commit();

        echo "<script>alert('Producto retirado exitosamente.'); window.location.href='RetirarPro.php';</script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error al retirar: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retirar Producto | Sistema de Inventario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            width: 250px;
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
        /* Submenús ocultos por defecto */
        .submenu {
            display: none;
            flex-direction: column;
            gap: 5px;
            padding-left: 20px;
            margin-top: 8px;

        }
        /* Cuando el submenu tenga la clase "show", se muestra */
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
            padding: 20px;
        }
        .content.sidebar-hidden {
            margin-left: 20px;
        }
        /* ==== TARJETA DE FORMULARIO ==== */
        .retiro-card {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
            border: 2px solid #0097A7;
            margin-top: 40px;   /* espacio para la top-bar */
        }
        .retiro-card h2 {
            font-size: 1.6rem;
            margin-bottom: 20px;
            color: #0097A7;
            text-align: center;
        }
        .retiro-card form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .retiro-card label {
            font-weight: bold;
            color: #37474F;
            margin-bottom: 6px;
        }
        .retiro-card input[type="text"],
        .retiro-card input[type="number"],
        .retiro-card input[type="date"],
        .retiro-card select,
        .retiro-card textarea {
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .retiro-card input:focus,
        .retiro-card select:focus,
        .retiro-card textarea:focus {
            outline: none;
            border-color: #0097A7;
            box-shadow: 0 0 6px rgba(0, 151, 167, 0.3);
        }
        .retiro-card textarea {
            resize: vertical;
            min-height: 60px;
            max-height: 150px;
        }
        .retiro-card .botones {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }
        .retiro-card button,
        .retiro-card a {
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, transform 0.1s;
        }
        .retiro-card button {
            background: #0097A7;
            color: #fff;
            border: none;
        }
        .retiro-card button:hover {
            background: #007c91;
            transform: translateY(-2px);
        }
        .retiro-card a {
            background: #f1f1f1;
            color: #0097A7;
            border: 2px solid #0097A7;
        }
        .retiro-card a:hover {
            background: #0097A7;
            color: #fff;
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
        }
        /* ==== RESPONSIVE ==== */
        @media (max-width: 600px) {
            .retiro-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Barra superior (sin modificar) -->
    <div class="top-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="margin: 0;">Sistema de Inventario</h2>
            <button id="toggleSidebarBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <span id="fecha-actual" style="margin-left: 20px; font-size: 16px;"></span>
        <div class="admin-container">
            <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>

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

            <div class="submenu" id="submenu-usuarios" style="display: none;">
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


            <div class="submenu" id="submenu-productos" style="display: none;">
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
            <div class="retiro-card">
                <h2>Retirar Producto</h2>

                <?php if (isset($success)) : ?>
                    <p class="success"><?= $success ?></p>
                <?php elseif (isset($error_agregar)) : ?>
                    <p class="error"><?= $error_agregar ?></p>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="nombre_producto">Nombre del Producto</label>
                        <input type="text" id="nombre_producto" value="<?= htmlspecialchars($producto['nombre_producto']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="cantidad_actual">Cantidad Disponible</label>
                        <input type="number" id="cantidad_actual" value="<?= htmlspecialchars($producto['cantidad']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="precio">Precio Unitario</label>
                        <input type="text" id="precio" value="$<?= number_format($producto['precio'], 2) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad a Retirar</label>
                        <input type="number" name="cantidad" id="cantidad" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_venta">Fecha de la Venta</label>
                        <input type="date" name="fecha_venta" id="fecha_venta" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario_id">Seleccionar Usuario</label>
                        <select name="usuario_id" id="usuario_id" required>
                            <option value="">-- Seleccione un usuario --</option>
                            <optgroup label="General Cobro">
                                <?php foreach ($usuarios_generales as $usuario): ?>
                                    <option value="<?= htmlspecialchars($usuario['id']) ?>"><?= htmlspecialchars($usuario['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="General Inventario">
                                <?php foreach ($usuarios_Empleados as $usuario): ?>
                                    <option value="<?= htmlspecialchars($usuario['id']) ?>"><?= htmlspecialchars($usuario['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" placeholder="Motivo o detalles del retiro..." required></textarea>
                    </div>

                    <div class="botones">
                        <a href="RetirarPro.php">Cancelar</a>
                        <button type="submit" name="retirar">Retirar Producto</button>
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
        document.addEventListener("DOMContentLoaded", function() {
            const toggleLink = document.querySelector(".toggle-submenu");
            const submenu = document.getElementById("submenu-usuarios");

            toggleLink.addEventListener("click", function(e) {
                e.preventDefault();
                submenu.style.display = submenu.style.display === "none" ? "flex" : "none";
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const toggles = document.querySelectorAll(".toggle-submenu2");

            toggles.forEach(function(toggle) {
                toggle.addEventListener("click", function(e) {
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

        document.addEventListener("DOMContentLoaded", function() {
            actualizarFecha(); // Mostrar la fecha al cargar la página

            // También puedes actualizar cada día a medianoche si mantienes la página abierta
            const ahora = new Date();
            const msHastaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1).getTime() - ahora.getTime();

            setTimeout(() => {
                actualizarFecha();
                setInterval(actualizarFecha, 24 * 60 * 60 * 1000); // Actualiza cada 24 horas
            }, msHastaMedianoche);
        });
    </script>
</body>
</html>
