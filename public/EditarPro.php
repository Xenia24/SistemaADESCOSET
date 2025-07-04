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
$Administrador = [
    'id'             => '',
    'nombre_producto'=> '',
    'precio'         => '',
    'categoria'      => '',
];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $Administrador = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($Administrador) {
        $modo_edicion = true;
    } else {
        echo "<script>alert('¡No se encontró el Producto!'); window.location.href='ListProductos.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id              = $_GET['id'];
    $nombre_producto = $_POST['nombre_producto'];
    $precio          = $_POST['precio'];
    $categoria       = $_POST['categoria'];

    // Comprobar si es edición
    $stmt_check = $pdo->prepare("SELECT id FROM productos WHERE id = :id");
    $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_check->execute();
    $modo_edicion = $stmt_check->fetch() ? true : false;

    try {
        if ($modo_edicion) {
            $stmt = $pdo->prepare("UPDATE productos SET 
                                    nombre_producto = :nombre_producto,
                                    precio          = :precio,
                                    categoria       = :categoria
                                   WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $mensaje_exito = "¡Registro actualizado exitosamente!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO productos 
                                   (nombre_producto, precio, categoria)
                                    VALUES (:nombre_producto, :precio, :categoria)");
            $mensaje_exito = "¡Registro guardado exitosamente!";
        }

        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':categoria', $categoria);

        if ($stmt->execute()) {
            echo "<script>alert('$mensaje_exito'); window.location.href='ListProductos.php';</script>";
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Producto</title>
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

    /* === Top Bar === */
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

    /* === Contenedor General === */
    .container {
      display: flex;
      flex: 1;
      padding-top: 60px;   /* espacio para top-bar */
      padding-bottom: 60px;/* espacio para bottom-bar */
    }

    /* === Sidebar (sin cambios) === */
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
      display: flex;
      flex-direction: column;
      gap: 5px;
      padding-left: 20px;
      margin-top: 8px;

    }
    .submenu a {
      font-size: 14px;
      padding: 8px;
      background-color: rgba(255,255,255,0.2);
      border-radius: 5px;
      display: flex;
      align-items: center;
      gap: 8px;
      color: #fff;
      text-decoration: none;
      transition: background 0.3s;
    }
    .submenu a:hover {
      background-color: rgba(255,255,255,0.4);
    }
    .submenu a img {
      width: 16px;
      height: 16px;
    }

    /* === Contenido Principal === */
    .content {
      flex: 1;
      background-color: white;
      margin: 0 20px;
      margin-left: 270px; /* espacio para el sidebar */
      margin-top: -5px;   /* espacio para la top-bar */
      border-radius: 10px;
      overflow-y: auto;

      /* Centrar la tarjeta vertical y horizontalmente */
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .content.sidebar-hidden {
      margin-left: 20px;
    }

    /* === Ajuste del formulario === */
    .form-container {
      background: #F1F1F1;
      padding: 20px;
      border-radius: 10px;
      width: 100%;
      max-width: 450px;   /* menor ancho para que no quede muy alargado */
    }
    .form-container h1 {
      font-size: 1.4rem;
      margin-bottom: 15px;
      color: #0097A7;
      text-align: center;
    }
    .form-row {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .form-group label {
      font-weight: bold;
      margin-bottom: 5px;
    }
    .form-group input,
    .form-group select {
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
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .btn-cancel:hover {
      background-color: darkred;
    }

    /* === Footer === */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 60px;
      background-color: #0097A7;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* === Responsive === */
    @media (max-width: 768px) {
      .content {
        padding: 10px;
      }
      .form-container {
        max-width: 100%;
      }
      .buttons {
        flex-direction: column;
      }
      .buttons .btn {
        width: 100%;
        margin-bottom: 10px;
      }
    }
  </style>
</head>

<body>
  <!-- Barra superior (sin cambios) -->
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
    <!-- Sidebar (sin cambios) -->
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
    <!-- Contenido principal (formulario centrado) -->
    <div class="content">
      <div class="form-container">
        <h1><?= $modo_edicion ? 'Editar Producto' : 'Agregar Producto' ?></h1>
        <form method="POST" action="">
          <div class="form-row">
            <div class="form-group">
              <label for="nombre_producto">Nombre Producto</label>
              <input type="text" id="nombre_producto" name="nombre_producto"
                     value="<?= htmlspecialchars($Administrador['nombre_producto']) ?>" required>
            </div>
            <div class="form-group">
              <label for="categoria">Categoría</label>
              <select id="categoria" name="categoria" required>
                <?php
                try {
                  $stmt = $pdo->query("SELECT nombre_categoria FROM categorias");
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $cat = $row['nombre_categoria'];
                    $selected = ($Administrador['categoria'] === $cat) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>"
                         . htmlspecialchars($cat) . "</option>";
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
              <input type="number" id="precio" name="precio" min="0.01" step="0.01"
                     value="<?= htmlspecialchars($Administrador['precio']) ?>" required>
            </div>
          </div>
          <div class="buttons">
            <a href="ListProductos.php" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-save"><?= $modo_edicion ? 'Actualizar' : 'Guardar' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Barra inferior fija (sin cambios) -->
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
    // Alternar sidebar completo
    document.addEventListener("DOMContentLoaded", function() {
                const toggleBtn = document.getElementById("toggleSidebarBtn");
                const sidebar = document.querySelector(".sidebar");
                const content = document.querySelector(".content");

                toggleBtn.addEventListener("click", () => {
                    sidebar.classList.toggle("hidden");
                    content.classList.toggle("sidebar-hidden");
                });
            });
    // Mostrar fecha actual en top-bar
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
