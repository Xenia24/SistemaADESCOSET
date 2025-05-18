<?php 
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
include('../includes/db.php');
$stmt = $pdo->query("
    SELECT numero_recibo, propietario, fecha_emision, estado_pago 
    FROM recibos 
    ORDER BY numero_recibo DESC
");
$recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lista de Recibos</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { display:flex; flex-direction:column; height:100vh; background:#f0f8ff; }

    /* Top bar */
    .top-bar {
      position:fixed; top:0; left:0; right:0; height:60px;
      background:#0097A7; color:#fff;
      display:flex; justify-content:space-between; align-items:center;
      padding:0 20px; z-index:100;
    }
    .top-bar a { color:#fff; text-decoration:underline; }
    .admin-container { display:flex; align-items:center; gap:10px; }

    /* Layout */
    .container { display:flex; flex:1; padding-top:60px; }

    /* Sidebar */
    .sidebar {
      width:250px; background:#0097A7; color:#fff;
      padding:20px; display:flex; flex-direction:column; gap:10px;
    }
    .sidebar img.logo { width:120px; margin:0 auto 20px; border-radius:10px; }
    .sidebar h3 { text-align:center; margin-bottom:15px; }
    .sidebar a, .sidebar .toggle {
      color:#fff; text-decoration:none; display:flex; align-items:center;
      gap:10px; padding:10px; border-radius:5px; cursor:pointer;
      transition:background .3s;
    }
    /* hover idéntico para enlaces y toggle */
    .sidebar a:hover, .sidebar .toggle:hover { background:#007c91; }
    .sidebar a img, .sidebar .toggle img { width:20px; height:20px; }

    .submenu {
      display:none; flex-direction:column; gap:5px; padding-left:20px;
    }
    .submenu.show { display:flex; }
    .submenu a {
      display:flex; align-items:center; gap:8px;
      padding:8px; color:#fff; text-decoration:none;
      background:rgba(255,255,255,0.2); border-radius:5px;
      transition:background .3s;
    }
    .submenu a:hover { background:rgba(255,255,255,0.4); }
    .submenu a img { width:16px; height:16px; }

    /* Content */
    .content {
      flex:1; padding:30px; background:#fff;
      display:flex; flex-direction:column; align-items:center;
    }
    .recibo-header { display:flex; align-items:center; gap:10px; margin-bottom:20px; }
    .recibo-header img { width:24px; }
    .busqueda {
      margin-bottom:20px; width:100%; max-width:800px;
      display:flex; justify-content:flex-end;
    }
    .busqueda input {
      padding:8px 12px; width:250px;
      border:1px solid #ccc; border-radius:5px 0 0 5px;
    }
    .busqueda button {
      background:#0097A7; border:none;
      padding:8px 12px; border-radius:0 5px 5px 0;
      cursor:pointer;
    }
    .busqueda button img { width:16px; }

    .recibo-lista {
      width:100%; max-width:900px;
      display:grid; grid-template-columns:1fr; gap:20px;
    }
    .recibo-card {
      background:linear-gradient(145deg,#e0f7fa,#b2ebf2);
      padding:20px 25px; border-radius:15px;
      box-shadow:0 6px 12px rgba(0,0,0,0.1);
      display:flex; justify-content:space-between; align-items:center;
      transition:transform .2s;
    }
    .recibo-card:hover { transform:translateY(-5px); }
    .recibo-card .info { flex:1; display:flex; gap:15px; }
    .recibo-card .info div { flex:1; }
    .recibo-card strong { font-size:16px; color:#006064; }
    .acciones { display:flex; gap:12px; }
    .acciones a img {
      width:28px; height:28px; transition:transform .2s;
    }
    .acciones a img:hover { transform:scale(1.2); }

    /* Bottom bar */
    .bottom-bar {
      width:100%; text-align:center; padding:10px;
      background:#0097A7; color:#fff;
    }
  </style>
</head>
<body>
  <!-- Top bar -->
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div class="admin-container">
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <img src="logoadesco.jpg" alt="Logo" class="logo">
      <h3>Sistema de Cobro</h3>

      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>

      <!-- Toggle para submenu, igual a las anteriores -->
      <div class="toggle"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</div>
      <div class="submenu">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="Natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
        <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
      </div>
      <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
      <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
      <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
    </div>

    <!-- Content -->
    <div class="content">
      <div class="recibo-header">
        <img src="../Image/lista-de-verificacion.png" alt="">
        <h2>Lista de Recibos</h2>
      </div>

      <div class="busqueda">
        <input type="text" placeholder="Buscar Usuario">
        <button><img src="../Image/lupa1.png" alt=""></button>
      </div>

      <div class="recibo-lista">
        <?php if (count($recibos) > 0): ?>
          <?php foreach ($recibos as $recibo): ?>
            <div class="recibo-card">
              <div class="info">
                <div>
                  <strong>N° Recibo:</strong><br>
                  <?= htmlspecialchars($recibo['numero_recibo']) ?><br>
                  <?= htmlspecialchars($recibo['propietario']) ?>
                </div>
                <div>
                  <strong>Fecha emisión:</strong><br>
                  <?= htmlspecialchars($recibo['fecha_emision']) ?>
                </div>
                <div>
                  <strong>Estado:</strong><br>
                  <?= htmlspecialchars($recibo['estado_pago']) ?>
                </div>
              </div>
              <div class="acciones">
                <a href="actualizarRecibo.php?numero=<?= urlencode($recibo['numero_recibo']) ?>" title="Ver / Editar">
                  <img src="../Image/ojo.png" alt="Ver">
                </a>
                <a href="reciboPDF.php?numero_recibo=<?= urlencode($recibo['numero_recibo']) ?>"
                   target="_blank"
                   download="Recibo_<?= htmlspecialchars($recibo['numero_recibo']) ?>.pdf"
                   title="Descargar PDF">
                  <img src="../Image/pdf-icon.png" alt="PDF">
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay recibos registrados aún.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Toggle del submenú (igual a las otras páginas)
    document.querySelector('.toggle').onclick = () => {
      document.querySelector('.submenu').classList.toggle('show');
    };
  </script>
</body>
</html>
