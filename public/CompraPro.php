<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener detalles del producto
$producto = [
    'id' => '',
    'nombre_producto' => '',
    'categoria' => '',
    'precio' => ''
];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "<script>alert('¡Producto no encontrado!'); window.location.href='ListProductos.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ID de producto no proporcionado.'); window.location.href='ListProductos.php';</script>";
    exit();
}

// Procesar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad     = $_POST['cantidad'];
    $fecha_compra = $_POST['fecha_compra'];

    try {
        // Registrar la compra
        $stmt = $pdo->prepare("INSERT INTO compras 
            (producto_id, cantidad_comprada, precio, categoria, fecha_compra)
            VALUES (:producto_id, :cantidad_comprada, :precio, :categoria, :fecha_compra)");

        $stmt->bindParam(':producto_id', $producto['id'], PDO::PARAM_INT);
        $stmt->bindParam(':cantidad_comprada', $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $producto['precio']);
        $stmt->bindParam(':categoria', $producto['categoria']);
        $stmt->bindParam(':fecha_compra', $fecha_compra);
        $stmt->execute();

        // Actualizar la cantidad sumando la nueva compra
        $updateStmt = $pdo->prepare("UPDATE productos SET cantidad = cantidad + :cantidad WHERE id = :id");
        $updateStmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $producto['id'], PDO::PARAM_INT);
        $updateStmt->execute();

        echo "<script>alert('¡Compra registrada y cantidad actualizada exitosamente!'); window.location.href='ListProductos.php';</script>";
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
  <title>Comprar Producto – Sistema de Inventario</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* === Reset y base === */
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
      background-color: #f4f4f4;
    }

    /* === Barra superior fija === */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background-color: #0097A7;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      color: white;
      z-index: 1000;
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

    /* === Contenedor general === */
    .container {
      display: flex;
      flex: 1;
      padding-top: 60px;   /* espacio para barra superior */
      padding-bottom: 60px; /* espacio para barra inferior */
    }

    /* === Sidebar (sin modificar funcionalidad) === */
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
      transition: width 0.3s ease;
    }
    .sidebar img.logo {
      width: 120px;
      margin: 0 auto 20px auto;
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
      display: none;
      flex-direction: column;
      gap: 5px;
      padding-left: 20px;
      margin-top: 8px;
    }
    .submenu.show {
      display: flex;
    }
    .submenu a {
      font-size: 14px;
      padding: 8px;
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.3s;
    }
    .submenu a:hover {
      background-color: rgba(255, 255, 255, 0.4);
    }
    .submenu a img {
      width: 16px;
      height: 16px;
    }

    /* === Contenido principal === */
    .content {
      flex: 1;
      background-color: white;
      margin-left: 270px; /* espacio para sidebar */
      margin-right: 20px;
      margin-top: -5px;   /* espacio para barra superior */
      border-radius: 10px;
      overflow-y: auto;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      text-align: center;
      transition: margin-left 0.3s ease;
    }
    .content.sidebar-hidden {
      margin-left: 20px;
    }

    /* === “Tarjeta” de compra === */
    .purchase-card {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 450px;
      border: 2px solid #0097A7;
    }
    .purchase-card h2 {
      font-size: 1.6rem;
      margin-bottom: 20px;
      color: #0097A7;
    }
    .purchase-card form {
      display: flex;
      flex-direction: column;
      gap: 15px;
      text-align: left;
    }
    .purchase-card label {
      font-weight: bold;
      color: #37474F;
      margin-bottom: 6px;
    }
    .purchase-card input[type="text"],
    .purchase-card input[type="number"],
    .purchase-card input[type="datetime-local"] {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .purchase-card input:focus {
      outline: none;
      border-color: #0097A7;
      box-shadow: 0 0 6px rgba(0, 151, 167, 0.3);
    }
    .purchase-card input[disabled] {
      background-color: #e9ecef;
      color: #6c757d;
      cursor: not-allowed;
    }
    .purchase-card .btn-group {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 20px;
    }
    .purchase-card button,
    .purchase-card a {
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
    .purchase-card button {
      background-color: #0097A7;
      color: #fff;
      border: none;
    }
    .purchase-card button:hover {
      background-color: #007c91;
      transform: translateY(-2px);
    }
    .purchase-card a {
      background: #f1f1f1;
      color: #0097A7;
      border: 2px solid #0097A7;
    }
    .purchase-card a:hover {
      background-color: #0097A7;
      color: #fff;
    }

    /* === Barra inferior fija === */
    .bottom-bar {
      
      bottom: 0;
      left: 0;
      right: 0;
      padding: 10px;
      background-color: #0097A7;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* === Responsive === */
    @media (max-width: 600px) {
      .content {
        padding: 10px;
      }
      .purchase-card {
        max-width: 100%;
      }
    }
  </style>
</head>

<body>
  <!-- Barra superior (sin modificar funcionalidad) -->
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

  <div class="container">
    <!-- Sidebar (sin modificar funcionalidad) -->
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

    <!-- Contenido principal (diseño actualizado, funcionalidad de fecha y menú intacta) -->
    <div class="content">
      <div class="purchase-card">
        <h2>Comprar Producto</h2>
        <form method="POST">
          <label>Producto:</label>
          <input type="text" value="<?= htmlspecialchars($producto['nombre_producto']) ?>" disabled>

          <label>Categoría:</label>
          <input type="text" value="<?= htmlspecialchars($producto['categoria']) ?>" disabled>

          <label>Precio:</label>
          <input type="text" value="<?= htmlspecialchars($producto['precio']) ?>" disabled>

          <label for="cantidad">Cantidad a comprar:</label>
          <input type="number" name="cantidad" id="cantidad" min="1" required>

          <label for="fecha_compra">Fecha de compra:</label>
          <input type="datetime-local" name="fecha_compra" id="fecha_compra" required>

          <div class="btn-group">
            <a href="ListProductos.php">Cancelar</a>
            <button type="submit">Realizar Compra</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Barra inferior (sin modificar funcionalidad) -->
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
