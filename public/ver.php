<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se envió el parámetro 'codigo' desde el icono de ver
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];

    // Obtener datos del derechohabiente NATURAL desde la base de datos
    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE codigo = :codigo AND tipo_derechohabiente = 'natural'");
    $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
    $stmt->execute();

    $derechohabiente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no encuentra el derechohabiente natural, mostrar mensaje de error
    if (!$derechohabiente) {
        echo "<script>alert('¡No se encontró información para el derechohabiente seleccionado!'); window.location.href='natural.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Código no recibido.'); window.location.href='natural.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle Derechohabiente – Sistema de Cobro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      margin:0; padding:0; box-sizing:border-box;
      font-family: Arial, sans-serif;
    }
    body {
      display:flex; flex-direction:column;
      height:100vh; background:#f4f4f4;
    }
    /* Top bar */
    .top-bar {
      position:fixed; top:0; left:0; right:0;
      height:60px; background:#0097A7;
      display:flex; align-items:center;
      justify-content:space-between;
      padding:0 20px; color:#fff; z-index:100;
    }
    .top-bar a {
      color:#fff; text-decoration:underline;
    }
    /* Container */
    .container {
      display:flex; flex:1;
      padding-top:60px; /* espacio para top-bar */
      padding-bottom:60px; /* espacio para bottom-bar */
    }
    /* Sidebar */
    .sidebar {
      width:250px; background:#0097A7; color:#fff;
      padding:20px;
      display:flex; flex-direction:column; gap:10px;
    }
    .sidebar img.logo {
      width:120px; margin:0 auto 20px;
      border-radius:10px;
    }
    .sidebar a, .sidebar .toggle {
      display:flex; align-items:center; gap:10px;
      padding:10px; color:#fff; text-decoration:none;
      border-radius:5px; cursor:pointer;
      transition:background .3s;
    }
    .sidebar a:hover, .sidebar .toggle:hover {
      background:#007c91;
    }
    .sidebar a img, .toggle img {
      width:20px; height:20px;
    }
    .submenu {
      display:none; flex-direction:column;
      gap:5px; padding-left:20px;
    }
    .submenu.show {
      display:flex;
    }
    .submenu a {
      display:flex; align-items:center; gap:8px;
      padding:8px;
      background:rgba(255,255,255,0.2);
      border-radius:5px;
      color:#fff; text-decoration:none;
      transition:background .3s;
    }
    .submenu a:hover {
      background:rgba(255,255,255,0.4);
    }
    .submenu a img {
      width:16px; height:16px;
    }
    /* Content */
    .content {
      flex:1;
      background:#fff;
      margin:0 20px;
      padding:20px;
      border-radius:10px;
      overflow-y:auto;
    }
    .detalle-container {
      max-width:800px; margin:0 auto;
      background:#f9f9f9; padding:20px;
      border:1px solid #ddd; border-radius:10px;
    }
    .detalle-header {
      font-size:18px; font-weight:bold;
      margin-bottom:20px; color:#333;
    }
    .detalle-row {
      display:flex; flex-wrap:wrap;
      justify-content:space-between; margin-bottom:15px;
    }
    .detalle-col {
      width:48%;
    }
    label {
      font-weight:bold; color:#333;
      display:block; margin-bottom:5px;
    }
    .detalle-info {
      background:#fff; border:1px solid #ccc;
      padding:8px; border-radius:5px;
    }
    .checkbox-container {
      display:flex; align-items:center; gap:5px;
      margin-top:5px;
    }
    .btn-back {
      display:inline-block; margin-top:20px;
      padding:10px 15px;
      background:#5bc0de; color:#fff;
      text-decoration:none; border-radius:5px;
    }
    .btn-back:hover {
      background:#46b8da;
    }
    /* Bottom bar */
    .bottom-bar {
      position:fixed; bottom:0; left:0; right:0;
      height:60px; background:#0097A7;
      color:#fff; display:flex;
      align-items:center; justify-content:center;
    }
    /* Responsive */
    @media (max-width:768px) {
      .detalle-col { width:100%; margin-bottom:10px; }
      .detalle-container { padding:15px; }
      .btn-back { width:100%; text-align:center; }
    }
  </style>
</head>
<body>

  <!-- Top bar -->
  <div class="top-bar">
    <h2><i class="fas fa-info-circle"></i> Detalle Derechohabiente</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo ADESCOSET">
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>
      <div class="toggle"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</div>
      <div class="submenu">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
        <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
      </div>
      <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
      <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
      <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
    </div>

    <!-- Content -->
    <div class="content">
      <div class="detalle-container">
        <div class="detalle-header">Información de Propietario</div>
        <div class="detalle-row">
          <div class="detalle-col">
            <label>Código</label>
            <div class="detalle-info"><?= htmlspecialchars($derechohabiente['codigo']) ?></div>
          </div>
          <div class="detalle-col">
            <label>Identificación</label>
            <div class="detalle-info"><?= htmlspecialchars($derechohabiente['identificacion']) ?></div>
          </div>
        </div>
        <div class="detalle-row">
          <div class="detalle-col">
            <label>Nombre Completo</label>
            <div class="detalle-info"><?= htmlspecialchars($derechohabiente['nombre_completo']) ?></div>
          </div>
          <div class="detalle-col">
            <label>Estado</label>
            <div class="checkbox-container">
              <input type="checkbox" <?= $derechohabiente['estado']=='activo'?'checked':'' ?> disabled>
              <span><?= htmlspecialchars(ucfirst($derechohabiente['estado'])) ?></span>
            </div>
          </div>
        </div>
        <div class="detalle-row">
          <div class="detalle-col">
            <label>Dirección</label>
            <div class="detalle-info"><?= htmlspecialchars($derechohabiente['direccion']) ?></div>
          </div>
          <div class="detalle-col">
            <label>Tipo de Derechohabiente</label>
            <div class="detalle-info"><?= htmlspecialchars(ucfirst($derechohabiente['tipo_derechohabiente'])) ?></div>
          </div>
        </div>
        <div class="detalle-row">
          <div class="detalle-col">
            <label>Teléfono</label>
            <div class="detalle-info"><?= htmlspecialchars($derechohabiente['telefono']) ?></div>
          </div>
        </div>
        <a href="natural.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Toggle submenú
    document.querySelector('.toggle').addEventListener('click',()=>{
      document.querySelector('.submenu').classList.toggle('show');
    });
  </script>

</body>
</html>
