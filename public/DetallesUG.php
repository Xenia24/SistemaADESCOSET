<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se envió el parámetro 'id' desde el icono de ver
if (isset($_GET['id'])) {
    $codigo = $_GET['id'];

    // Obtener datos del usuario general (cobro o inventario) desde la base de datos
    $stmt = $pdo->prepare("
        SELECT * 
        FROM usuariosag 
        WHERE id = :id 
          AND tipo_usuario IN ('General Cobro', 'General Inventario')
    ");
    $stmt->bindParam(':id', $codigo, PDO::PARAM_INT);
    $stmt->execute();

    $derechohabiente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no encuentra el usuario, mostrar mensaje de error
    if (!$derechohabiente) {
        echo "<script>
                alert('¡No se encontró información para el Usuario seleccionado!');
                window.location.href='ListAdministrador.php';
              </script>";
        exit();
    }
} else {
    echo "<script>
            alert('Código no recibido.');
            window.location.href='ListAdministrador.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle Usuario General – Sistema de Cobro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* ----------------------------------------
       RESET Y ESTILOS GENERALES
    ---------------------------------------- */
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

    /* ----------------------------------------
       BARRA SUPERIOR FIJA
    ---------------------------------------- */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      color: #fff;
      z-index: 100;
    }
    .top-bar h2 {
      font-size: 18px;
    }
    .top-bar a {
      color: #fff;
      text-decoration: underline;
    }

    /* ----------------------------------------
       CONTENEDOR PRINCIPAL
    ---------------------------------------- */
    .container {
      display: flex;
      flex: 1;
      margin-top: 60px; /* espacio para la top-bar */
      margin-bottom: 60px; /* espacio para la bottom-bar */
    }

    /* ----------------------------------------
       SIDEBAR (sin cambios de funcionalidad)
    ---------------------------------------- */
    .sidebar {
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 60px;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      transition: width .3s ease;
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
    .sidebar a, .sidebar .toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      transition: background .3s;
      cursor: pointer;
    }
    .sidebar a:hover, .sidebar .toggle:hover {
      background-color: #007c91;
    }
    .sidebar a img, .toggle img {
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
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      transition: background .3s;
    }
    .submenu a:hover {
      background-color: rgba(255,255,255,0.4);
    }
    .submenu a img {
      width: 16px;
      height: 16px;
    }

    /* ----------------------------------------
       CONTENIDO
    ---------------------------------------- */
    .content {
      flex: 1;
      background: #fff;
      margin-left: 270px; /* espacio para el sidebar */
      margin-right: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0 20px; /* ya no hay padding-top para centrar verticalmente */
      border-radius: 10px;
      overflow-y: auto;
      transition: margin-left .3s ease;
    }
    .content.sidebar-hidden {
      margin-left: 20px;
    }

    /* ----------------------------------------
       ESTILO DE LA TARJETA DE DETALLE
    ---------------------------------------- */
    .detalle-container {
      background: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      max-width: 800px;
      width: 100%;
      border: 2px solid #0097A7;
    }
    .detalle-header {
      font-size: 1.6rem;
      font-weight: bold;
      margin-bottom: 20px;
      color: #0097A7;
      text-align: center;
    }
    .detalle-row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 15px;
    }
    .detalle-col {
      width: calc(50% - 10px);
    }
    @media (max-width: 768px) {
      .detalle-col {
        width: 100%;
      }
    }
    label {
      display: block;
      font-weight: bold;
      margin-bottom: 6px;
      color: #37474F;
    }
    .detalle-info {
      padding: 10px;
      background-color: #F1F1F1;
      border: 1px solid #ccc;
      border-radius: 6px;
      color: #333;
    }
    .checkbox-container {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 5px;
    }
    .btn-back {
      display: inline-block;
      margin-top: 25px;
      padding: 10px 20px;
      background: #0097A7;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background .2s, transform .1s;
    }
    .btn-back:hover {
      background: #007c91;
      transform: translateY(-2px);
    }

    /* ----------------------------------------
       BARRA INFERIOR FIJA
    ---------------------------------------- */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
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
    <span id="fecha-actual" style="font-size: 16px;"></span>
    <div class="admin-container">
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <!-- Contenedor principal -->
  <div class="container">
    <!-- Sidebar (sin cambios de funcionalidad) -->
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo ADESCOSET">
      <h3>Sistema de Inventario</h3>

      <a href="dashboard2.php"><img src="../Image/hogarM.png" alt="Inicio"> Inicio</a>

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

      <a href="AgregarCat.php"><img src="../Image/factura.png" alt="Categorías"> Categorias</a>

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

      <a href="Reportes.php"><img src="../Image/reporte.png" alt="Reporte"> Reportes</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
      <div class="detalle-container">
        <div class="detalle-header">Información de Usuario General</div>

        <div class="detalle-row">
          <div class="detalle-col">
            <label>Nombre Completo</label>
            <div class="detalle-info">
              <?= htmlspecialchars($derechohabiente['nombre_completo']) ?>
            </div>
          </div>
          <div class="detalle-col">
            <label>Correo</label>
            <div class="detalle-info">
              <?= htmlspecialchars($derechohabiente['correo']) ?>
            </div>
          </div>
        </div>

        <div class="detalle-row">
          <div class="detalle-col">
            <label>Estado</label>
            <div class="checkbox-container">
              <input type="checkbox" <?= $derechohabiente['estado'] === 'activo' ? 'checked' : '' ?> disabled>
              <span><?= htmlspecialchars(ucfirst($derechohabiente['estado'])) ?></span>
            </div>
          </div>
          <div class="detalle-col">
            <label>Teléfono</label>
            <div class="detalle-info">
              <?= htmlspecialchars($derechohabiente['telefono']) ?>
            </div>
          </div>
        </div>

        <div class="detalle-row">
          <div class="detalle-col">
            <label>Número de DUI</label>
            <div class="detalle-info">
              <?= htmlspecialchars($derechohabiente['numero_dui']) ?>
            </div>
          </div>
          <div class="detalle-col">
            <label>Nombre de Usuario</label>
            <div class="detalle-info">
              <?= htmlspecialchars($derechohabiente['nombre_usuario']) ?>
            </div>
          </div>
        </div>

        <div class="detalle-row">
          <div class="detalle-col">
            <label>Tipo de Usuario</label>
            <div class="detalle-info">
              <?= htmlspecialchars(ucfirst($derechohabiente['tipo_usuario'])) ?>
            </div>
          </div>
        </div>

        <a href="ListGeneral.php" class="btn-back">
          <i class="fas fa-arrow-left"></i> Volver
        </a>
      </div>
    </div>
  </div>

  <!-- Barra inferior fija -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Alternar visibilidad del submenú Usuarios
    document.querySelector('.toggle-submenu').addEventListener('click', () => {
      document.getElementById('submenu-usuarios').classList.toggle('show');
    });
    // Alternar visibilidad del submenú Productos
    document.querySelector('.toggle-submenu2').addEventListener('click', () => {
      document.getElementById('submenu-productos').classList.toggle('show');
    });
    // Alternar visibilidad del sidebar completo
    document.getElementById('toggleSidebarBtn').addEventListener('click', () => {
      document.querySelector('.sidebar').classList.toggle('hidden');
      document.querySelector('.content').classList.toggle('sidebar-hidden');
    });
    // Mostrar fecha actual en la top-bar
    function actualizarFecha() {
      const fechaElemento = document.getElementById('fecha-actual');
      const fecha = new Date();
      const opciones = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };
      fechaElemento.textContent = fecha.toLocaleDateString('es-ES', opciones);
    }
    actualizarFecha();
    const ahora = new Date();
    const msHastaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1).getTime() - ahora.getTime();
    setTimeout(() => {
      actualizarFecha();
      setInterval(actualizarFecha, 24 * 60 * 60 * 1000);
    }, msHastaMedianoche);
  </script>
</body>
</html>
