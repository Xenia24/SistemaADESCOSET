<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
include('../includes/db.php');

// Recoger búsqueda
$search = trim($_GET['q'] ?? '');

// Construir consulta con filtro opcional
$sql = "
    SELECT numero_recibo, propietario, fecha_emision, estado_pago 
    FROM recibos
";
if ($search !== '') {
    $sql .= " WHERE numero_recibo LIKE :s OR propietario LIKE :s";
}
$sql .= " ORDER BY numero_recibo DESC";

$stmt = $pdo->prepare($sql);
if ($search !== '') {
    $stmt->bindValue(':s', "%{$search}%");
}
$stmt->execute();
$recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lista de Recibos</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family:Arial, sans-serif;
    }
    body {
      height: 100vh;
      background: #f0f8ff;
    }

    /* --- Top bar: fija en la parte superior --- */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
    }
    .top-bar a {
      color: #fff;
      text-decoration: none;
    }
    .admin-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* --- Sidebar: fija a la izquierda, debajo de la top-bar y encima de la bottom-bar --- */
    .sidebar {
      position: fixed;
      top: 60px;           /* justo debajo de la top-bar */
      left: 0;
      bottom: 40px;        /* dejar espacio para la bottom-bar */
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      overflow-y: auto;
      z-index: 900;
    }
    .sidebar img.logo {
      width: 120px;
      margin: 0 auto 20px;
      border-radius: 10px;
    }
    .sidebar h3 {
      text-align: center;
      margin-bottom: 15px;
    }
    .sidebar a,
    .sidebar .toggle {
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .sidebar a:hover,
    .sidebar .toggle:hover {
      background: #007c91;
    }
    .sidebar a img,
    .sidebar .toggle img {
      width: 20px;
      height: 20px;
    }
    .submenu {
      display: none;
      flex-direction: column;
      gap: 5px;
      padding-left: 20px;
    }
    .submenu.show {
      display: flex;
    }
    .submenu a {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      color: #fff;
      text-decoration: none;
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
      transition: background 0.3s;
    }
    .submenu a:hover {
      background: rgba(255,255,255,0.4);
    }
    .submenu a img {
      width: 16px;
      height: 16px;
    }

    /* --- Bottom bar: fija en la parte inferior --- */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 40px;
      background: #0097A7;
      color: #fff;
      text-align: center;
      line-height: 40px;
      z-index: 1000;
    }

    /* --- Contenido intermedio: ocupa el espacio entre top-bar y bottom-bar, desplazable por sí mismo --- */
    .main-content {
      position: absolute;
      top: 60px;           /* justo debajo de la top-bar */
      left: 250px;         /* a la derecha de la sidebar */
      right: 0;
      bottom: 40px;        /* por encima de la bottom-bar */
      overflow-y: auto;    /* solo este contenedor se desplazará */
      padding: 30px;
      background: #fff;
    }

    /* --- Encabezado de la sección de recibos dentro del contenido --- */
    .recibo-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
      margin-top: 20px;
    }
    .recibo-header img {
      width: 24px;
    }

    /* --- Buscador alineado a la derecha dentro de max-width:900px --- */
    .busqueda {
      margin-bottom: 20px;       /* espacio igual al gap entre tarjetas */
      max-width: 900px;          /* mismo ancho que .recibo-lista */
      margin: 0 auto;            /* centrado horizontal */
      display: flex;
      justify-content: flex-end;
      margin-top: 20px; /* pegado al borde derecho de ese ancho */
    }
    .busqueda input {
      padding: 8px 12px;
      width: 250px;
      border: 1px solid #ccc;
      border-radius: 5px 0 0 5px;
    }
    .busqueda button {
      background: #0097A7;
      border: none;
      padding: 8px 12px;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
    }
    .busqueda button img {
      width: 16px;
    }

    /* --- Descarga por mes alineada a la derecha dentro de max-width:900px --- */
    .busqueda.month {
      margin-top: 20px;          /* mismo espacio que margin-bottom de .busqueda */
      margin-bottom: 20px;       /* mismo espacio que gap entre tarjetas */
      max-width: 900px;          /* mismo ancho que .recibo-lista */
      display: flex;
      justify-content: flex-end; /* pegado al borde derecho de ese ancho */
    }
    .busqueda.month input[type="month"] {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-right: 8px;
    }
    .busqueda.month button {
      background: #0097A7;
      border: none;
      padding: 8px 12px;
      border-radius: 5px;
      color: #fff;
      cursor: pointer;
    }

    /* --- Lista de recibos: tarjetas centradas en su contenedor --- */
    .recibo-lista {
      width: 100%;
      max-width: 900px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;    /* espacio entre tarjetas */
      margin: 0 auto;  /* centrado horizontal */
    }
    .recibo-card {
      background: linear-gradient(145deg, #e0f7fa, #b2ebf2);
      padding: 20px 25px;
      border-radius: 15px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: transform 0.2s;
    }
    .recibo-card:hover {
      transform: translateY(-5px);
    }
    .recibo-card .info {
      flex: 1;
      display: flex;
      gap: 15px;
    }
    .recibo-card .info div {
      flex: 1;
    }
    .recibo-card strong {
      font-size: 16px;
      color: #006064;
    }

    .acciones {
      display: flex;
      gap: 12px;
    }
    .acciones a img {
      width: 28px;
      height: 28px;
      transition: transform 0.2s;
    }
    .acciones a img:hover {
      transform: scale(1.2);
    }
  </style>
</head>
<body>

  <!-- Top bar (fija) -->
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div class="admin-container">
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <!-- Sidebar (fija) -->
  <div class="sidebar">
    <img src="logoadesco.jpg" alt="Logo" class="logo">
    <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>
    <div class="toggle"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</div>
    <div class="submenu">
      <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
      <a href="Natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
      <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
    </div>
    <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
    <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
    <div class="toggle" id="toggle-reporte">
      <img src="../Image/reporte.png" alt=""> Reporte ⏷
    </div>
    <div class="submenu" id="submenu-reporte">
      <a href="reporte.php?tipo=pagados">Recibos pagados</a>
      <a href="reporte.php?tipo=nopagados">No pagados</a>
      <a href="reporte.php?tipo=despues_vencimiento">Pagados tras venc.</a>
      <a href="reporte.php?tipo=mora">En mora</a>
      <a href="reporte.php?tipo=total">Total recaudado</a>
    </div>
  </div>

  <!-- Contenido principal (desplazable) -->
  <div class="main-content">
    <div class="recibo-header">
      <img src="../Image/lista-de-verificacion.png" alt="">
      <h2>Lista de Recibos</h2>
    </div>

    <!-- Buscador alineado a la derecha dentro de max-width:900px -->
    <div class="busqueda">
      <form id="formBusqueda" method="get" action="listado.php">
        <input
          id="inputBusqueda"
          type="text"
          name="q"
          placeholder="Buscar Nº recibo o Usuario"
          value="<?= htmlspecialchars($search) ?>"
        >
        <button type="submit">
          <img src="../Image/lupa1.png" alt="">
        </button>
      </form>
    </div>

    <!-- Descargar por mes alineado a la derecha dentro de max-width:900px -->
    <div class="busqueda month">
      <form method="get" action="descargarRecibosMes.php" target="_blank">
        <input type="month" name="mes" required>
        <button type="submit">Descargar por mes</button>
      </form>
    </div>

    <!-- Tarjetas de recibos centradas dentro de main-content -->
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

  <!-- Bottom bar (fija) -->
  <div class="bottom-bar">
    © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Si el campo de búsqueda está vacío, recarga sin parámetros
    document.getElementById('formBusqueda').addEventListener('submit', function(e) {
      var campo = document.getElementById('inputBusqueda');
      if (campo.value.trim() === '') {
        e.preventDefault();
        window.location.href = 'listado.php';
      }
    });

    // Toggle del submenú “Tipo de derechohabiente”
    document.querySelector('.toggle').onclick = () => {
      document.querySelector('.submenu').classList.toggle('show');
    };
    // Toggle del submenú “Reporte”
    document.getElementById('toggle-reporte').addEventListener('click', () => {
      document.getElementById('submenu-reporte').classList.toggle('show');
    });
  </script>
</body>
</html>
