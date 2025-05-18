<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
include('../includes/db.php');

// Cálculos para las tarjetas
$totalDH   = $pdo->query("SELECT COUNT(*) FROM agregarderechohabiente")->fetchColumn();
$naturales = $pdo->query("SELECT COUNT(*) FROM agregarderechohabiente WHERE tipo_derechohabiente='natural'")->fetchColumn();
$juridicos = $pdo->query("SELECT COUNT(*) FROM agregarderechohabiente WHERE tipo_derechohabiente='juridica'")->fetchColumn();

// Total recaudado mes actual
$mes  = date('m');
$anio = date('Y');
$stmt = $pdo->prepare("
    SELECT SUM(total) 
    FROM recibos 
    WHERE MONTH(fecha_emision)=:mes 
      AND YEAR(fecha_emision)=:anio
");
$stmt->execute([':mes'=>$mes,':anio'=>$anio]);
$totalRecaudado = $stmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard – Sistema de Cobro</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
    body { display:flex; height:100vh; background:#f4f4f4; }
    .top-bar {
      position:fixed; top:0; left:0; right:0; height:60px; background:#0097A7;
      display:flex; align-items:center; justify-content:space-between;
      padding:0 20px; color:#fff; z-index:100;
    }
    .top-bar a { color:#fff; text-decoration:underline; }
    .container { display:flex; flex:1; padding-top:60px; }
    .sidebar {
      width:250px; background:#0097A7; color:#fff; padding:20px;
      display:flex; flex-direction:column; gap:10px;
    }
    .sidebar img.logo { width:120px; margin:0 auto 20px; border-radius:10px; }
    .sidebar a, .sidebar .toggle {
      color:#fff; text-decoration:none; display:flex; align-items:center;
      gap:10px; padding:10px; border-radius:5px; cursor:pointer;
      transition:background .3s;
    }
    .sidebar a:hover, .sidebar .toggle:hover { background:#007c91; }
    .submenu {
      display:none; flex-direction:column; gap:5px; padding-left:20px;
    }
    .submenu.show { display:flex; }
    .submenu a {
      background:rgba(255,255,255,0.2); padding:8px; border-radius:5px;
    }
    .submenu a:hover { background:rgba(255,255,255,0.4); }

    .content {
      flex:1; overflow-y:auto; padding:20px;
    }

    /* tarjetas */
    .cards {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
      gap:20px;
    }
    .card {
      background:#fff; border:1px solid #ccc; border-radius:8px;
      padding:15px; box-shadow:0 2px 5px rgba(0,0,0,0.1);
      text-align:center;
    }
    .card h3 { margin-bottom:10px; font-size:16px; color:#333; }
    .card p { font-size:28px; font-weight:bold; color:#0097A7; margin:0; }

    .bottom-bar {
      position:fixed; bottom:0; left:0; right:0; height:40px;
      background:#0097A7; color:#fff; text-align:center; line-height:40px;
    }
  </style>
</head>
<body>
  <div class="top-bar">
    <h2>Bienvenido Sistema De Cobro</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario']) ?> 👤 |
      <a href="logout.php">Salir</a>
    </div>
  </div>

  <div class="container">
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo">
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>

      <div class="toggle">
        <img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷
      </div>
      <div class="submenu">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="Natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
        <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
      </div>

      <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
      <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
      <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
    </div>

    <div class="content">
      <div class="cards">
        <div class="card">
          <h3>Total Derechohabientes</h3>
          <p><?= $totalDH ?></p>
        </div>
        <div class="card">
          <h3>Naturales</h3>
          <p><?= $naturales ?></p>
        </div>
        <div class="card">
          <h3>Jurídicos</h3>
          <p><?= $juridicos ?></p>
        </div>
        <div class="card">
          <h3>Total Recaudado<br>(<?= DateTime::createFromFormat('!m',$mes)->format('F') ?>)</h3>
          <p>$<?= number_format($totalRecaudado,2) ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Toggle submenú
    document.querySelector('.toggle').addEventListener('click', ()=>{
      document.querySelector('.submenu').classList.toggle('show');
    });
  </script>
</body>
</html>
