<?php
// reporte.php — Dashboard mejorado con panel de notificaciones (sin “Vence Hoy”), gráfico principal y tabla con filtrado avanzado

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
include('../includes/db.php');

// Parámetro para determinar qué reporte mostrar
$tipo = $_GET['tipo'] ?? '';

// 1) CÁLCULOS PARA TARJETAS
$totalDH    = $pdo->query("SELECT COUNT(*) FROM agregarderechohabiente")->fetchColumn();
$naturales  = $pdo->query("SELECT COUNT(*) FROM agregarderechohabiente WHERE tipo_derechohabiente='natural'")->fetchColumn();
$juridicos  = $pdo->query("SELECT COUNT(*) FROM agregarderechohabiente WHERE tipo_derechohabiente='juridica'")->fetchColumn();
$mes        = date('m');
$anio       = date('Y');
$stmt       = $pdo->prepare("
    SELECT SUM(total) 
      FROM recibos 
     WHERE MONTH(fecha_emision)=:mes 
       AND YEAR(fecha_emision)=:anio
");
$stmt->execute([':mes' => $mes, ':anio' => $anio]);
$totalRecaudado = $stmt->fetchColumn() ?: 0;

// Nombre del mes en español
$mesesEsp   = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$mesNombre  = $mesesEsp[(int)$mes - 1];

// 2) PANEL DE NOTIFICACIONES
// 2.1) Recibos en mora
$moraCount = $pdo->query("SELECT COUNT(*) FROM recibos WHERE estado_pago='En mora'")->fetchColumn();
// 2.2) Recibos no pagados
$pendientesCount = $pdo->query("SELECT COUNT(*) FROM recibos WHERE estado_pago='No pagado'")->fetchColumn();
// 2.3) Recibos pagados (nueva tarjeta en lugar de “Vence Hoy”)
$pagadosCount = $pdo->query("SELECT COUNT(*) FROM recibos WHERE estado_pago='Pagado'")->fetchColumn();

// 3) DATOS PARA GRÁFICO PRINCIPAL (Recaudación mes a mes en el año actual)
$monthlyData = [];
for ($m = 1; $m <= 12; $m++) {
    $stmtM = $pdo->prepare("
        SELECT SUM(total) 
          FROM recibos 
         WHERE MONTH(fecha_emision)=:m 
           AND YEAR(fecha_emision)=:y
    ");
    $stmtM->execute([':m' => $m, ':y' => $anio]);
    $monthlyData[] = $stmtM->fetchColumn() ?: 0;
}

// 4) FILTRADO AVANZADO para “Recibos Pagados”
$recibosPagados = [];
$f_propietario   = trim($_GET['f_propietario'] ?? '');
$f_inicio        = $_GET['f_fecha_inicio'] ?? '';
$f_fin           = $_GET['f_fecha_fin'] ?? '';

if ($tipo === 'pagados') {
    $queryBase = "
        SELECT numero_recibo, propietario, fecha_emision, total 
          FROM recibos 
         WHERE estado_pago = 'Pagado'
    ";
    $params = [];

    if ($f_propietario !== '') {
        $queryBase .= " AND propietario LIKE :prop ";
        $params[':prop'] = "%{$f_propietario}%";
    }
    if ($f_inicio !== '' && $f_fin !== '') {
        $queryBase .= " AND fecha_emision BETWEEN :fini AND :ffin ";
        $params[':fini'] = $f_inicio;
        $params[':ffin'] = $f_fin;
    }

    $queryBase .= " ORDER BY fecha_emision DESC ";
    $stmt2 = $pdo->prepare($queryBase);
    $stmt2->execute($params);
    $recibosPagados = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard – Sistema de Cobro</title>
  <!-- Chart.js para el gráfico principal -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* ***********************
       RESET BÁSICO 
    *************************/
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    html, body {
      height: 100%;
      background: #f4f4f4;
      color: #333;
    }
    a {
      text-decoration: none;
      color: inherit;
    }
    ul {
      list-style: none;
    }

    /* ***********************
       BARRA SUPERIOR 
    *************************/
    .top-bar {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 60px;
      background: #0097A7;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      color: #fff;
      z-index: 100;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .top-bar a {
      color: #fff;
      font-size: 0.9rem;
    }

    /* ***********************
       CONTENEDOR PRINCIPAL 
    *************************/
    .container {
      display: flex;
      height: calc(100vh - 60px - 40px); /* 60px de la top-bar + 40px de la bottom-bar */
      padding-top: 60px; /* espacio para la barra superior */
    }

    /* ***********************
       SIDEBAR 
    *************************/
    .sidebar {
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      height: 110%; /* llena toda la altura disponible */
    }
    .sidebar img.logo {
      width: 120px;
      margin: 0 auto 20px;
      border-radius: 10px;
      object-fit: cover;
    }
    .sidebar a, .sidebar .toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      border-radius: 5px;
      transition: background 0.3s;
      cursor: pointer;
      
    }
    .sidebar a:hover, .sidebar .toggle:hover {
      background: #007c91;
    }
    .sidebar a img, .sidebar .toggle img {
      width: 18px;
      height: 18px;
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
      background: rgba(255,255,255,0.15);
      padding: 8px;
      border-radius: 5px;
      color: #fff;
      font-size: 0.9rem;
      transition: background 0.3s;
    }
    .submenu a:hover {
      background: rgba(255,255,255,0.3);
    }

    /* ***********************
       ÁREA DE CONTENIDO 
    *************************/
    .content {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }

    /* ***********************
       DASHBOARD – TARJETAS 
    *************************/
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-left: 5px solid #0097A7;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
      position: relative;
    }
    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .card h3 {
      font-size: 1rem;
      margin-bottom: 12px;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .card p {
      font-size: 2rem;
      font-weight: bold;
      color: #0097A7;
      margin: 0;
    }
    .card .icon {
      position: absolute;
      top: 20px;
      right: 20px;
      font-size: 1.5rem;
      color: rgba(0, 151, 167, 0.5);
    }

    /* ***********************
       PANEL DE NOTIFICACIONES 
    *************************/
    .notifications {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .notif-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-left: 5px solid #f39c12;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      transition: transform 0.2s, box-shadow 0.2s;
      font-size: 0.95rem;
    }
    .notif-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .notif-card h4 {
      font-size: 0.9rem;
      margin-bottom: 6px;
      color: #333;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .notif-card h4 .fas {
      color: #f39c12;
    }
    .notif-card span {
      font-size: 1.3rem;
      font-weight: bold;
      color: #f39c12;
    }

    /* ***********************
       GRÁFICO PRINCIPAL 
    *************************/
    .chart-container {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }
    .chart-container h3 {
      font-size: 1.1rem;
      margin-bottom: 12px;
      color: #333;
    }
    .chart-container canvas {
      width: 100% !important;
      max-height: 300px;
    }

    /* ***********************
       TABLA – AVANZADA 
    *************************/
    .table-header {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      gap: 10px;
    }
    .table-header h2 {
      font-size: 1.2rem;
      color: #333;
    }
    .filter-group {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }
    .filter-group input,
    .filter-group select {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 0.9rem;
      outline: none;
      transition: border-color 0.2s;
    }
    .filter-group input:focus,
    .filter-group select:focus {
      border-color: #0097A7;
      box-shadow: 0 0 0 3px rgba(0,151,167,0.2);
    }
    .filter-group button {
      padding: 6px 12px;
      background: #0097A7;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: background 0.2s;
    }
    .filter-group button:hover {
      background: #00838f;
    }
    .table-container {
      overflow-x: auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }
    table.modern {
      width: 100%;
      border-collapse: collapse;
      min-width: 700px; /* para scroll si es necesario */
    }
    table.modern thead {
      background: #0097A7;
      color: #fff;
    }
    table.modern th, table.modern td {
      padding: 12px 10px;
      border-bottom: 1px solid #e0e0e0;
      text-align: left;
      font-size: 0.95rem;
    }
    table.modern th {
      font-weight: 500;
      font-size: 0.95rem;
    }
    table.modern tbody tr:nth-child(even) {
      background: #fafafa;
    }
    table.modern tbody tr:hover {
      background: #f1f1f1;
    }
    table.modern td {
      vertical-align: middle;
    }
    table.modern td a {
      color: #0097A7;
      font-weight: 500;
      font-size: 0.9rem;
    }
    @media (max-width: 600px) {
      table.modern th, table.modern td {
        padding: 8px 6px;
        font-size: 0.85rem;
      }
    }

    /* ***********************
       BARRA INFERIOR 
    *************************/
    .bottom-bar {
      position: fixed;
      bottom: 0; left: 0; right: 0;
      height: 40px;
      background: #0097A7;
      color: #fff;
      text-align: center;
      line-height: 40px;
      font-size: 0.85rem;
      box-shadow: 0 -2px 4px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>
  <!-- BARRA SUPERIOR -->
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario']) ?> 👤 |
      <a href="logout.php">Cerrar Sesión</a>
    </div>
  </div>

  <div class="container">
    <!-- SIDEBAR (INALTERADO) -->
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo">
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>

      <div class="toggle" id="toggle-dh">
        <img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷
      </div>
      <div class="submenu" id="submenu-dh">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
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

    <!-- ÁREA DE CONTENIDO -->
    <div class="content">
      <?php if ($tipo === 'pagados'): ?>
        <!-- ======= REPORTE “RECIBOS PAGADOS” CON FILTRADO AVANZADO ======= -->
        <div class="table-header">
          <h2>Reporte: Recibos Pagados</h2>
          <!-- FORMULARIO DE FILTRADO AVANZADO -->
          <form class="filter-group" method="get" action="reporte.php">
            <input type="hidden" name="tipo" value="pagados">
            <input
              type="text"
              name="f_propietario"
              placeholder="Propietario…"
              value="<?= htmlspecialchars($f_propietario) ?>"
            >
            <input
              type="date"
              name="f_fecha_inicio"
              value="<?= htmlspecialchars($f_inicio) ?>"
            >
            <input
              type="date"
              name="f_fecha_fin"
              value="<?= htmlspecialchars($f_fin) ?>"
            >
            <button type="submit">Filtrar</button>
          </form>
        </div>
        <div class="table-container">
          <table class="modern" id="tablaRecibos">
            <thead>
              <tr>
                <th>N° Recibo</th>
                <th>Propietario</th>
                <th>Fecha Emisión</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($recibosPagados) > 0): ?>
                <?php foreach ($recibosPagados as $r): ?>
                  <tr>
                    <td><?= htmlspecialchars($r['numero_recibo']) ?></td>
                    <td><?= htmlspecialchars($r['propietario']) ?></td>
                    <td><?= htmlspecialchars($r['fecha_emision']) ?></td>
                    <td>$<?= number_format($r['total'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align:center; padding: 20px;">
                    No se encontraron recibos pagados con esos criterios.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      <?php else: ?>
        <!-- ======= DASHBOARD PRINCIPAL ======= -->
        <h2>Sistema De Cobro ADESCOSET</h2>

        <!-- TARJETAS INFORMATIVAS -->
        <div class="cards">
          <div class="card">
            <i class="fas fa-users icon"></i>
            <h3>Total Derechohabientes</h3>
            <p><?= $totalDH ?></p>
          </div>
          <div class="card">
            <i class="fas fa-user icon"></i>
            <h3>Naturales</h3>
            <p><?= $naturales ?></p>
          </div>
          <div class="card">
            <i class="fas fa-building icon"></i>
            <h3>Jurídicos</h3>
            <p><?= $juridicos ?></p>
          </div>
          <div class="card">
            <i class="fas fa-dollar-sign icon"></i>
            <h3>Total Recaudado<br>(<?= $mesNombre ?> <?= $anio ?>)</h3>
            <p>$<?= number_format($totalRecaudado, 2) ?></p>
          </div>
        </div>

        <!-- PANEL DE NOTIFICACIONES -->
        <div class="notifications">
          <div class="notif-card">
            <h4><i class="fas fa-exclamation-triangle"></i> En Mora</h4>
            <span><?= $moraCount ?></span>
          </div>
          <div class="notif-card">
            <h4><i class="fas fa-check-circle"></i> Pagados</h4>
            <span><?= $pagadosCount ?></span>
          </div>
          <div class="notif-card">
            <h4><i class="fas fa-hourglass-half"></i> Pendientes</h4>
            <span><?= $pendientesCount ?></span>
          </div>
        </div>

        <!-- GRÁFICO PRINCIPAL: RECAUDACIÓN MES A MES -->
        <div class="chart-container">
          <h3>Recaudación Mensual (<?= $anio ?>)</h3>
          <canvas id="chartRecaudacion"></canvas>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- BARRA INFERIOR -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <!-- ***********************
       SCRIPTS 
  ************************-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
  <script>
    // Toggle submenús
    document.getElementById('toggle-dh').addEventListener('click', () => {
      document.getElementById('submenu-dh').classList.toggle('show');
    });
    document.getElementById('toggle-reporte').addEventListener('click', () => {
      document.getElementById('submenu-reporte').classList.toggle('show');
    });

    <?php if ($tipo !== 'pagados'): ?>
    // Configurar Chart.js para "Recaudación Mensual"
    const ctx = document.getElementById('chartRecaudacion').getContext('2d');
    const data = {
      labels: <?= json_encode($mesesEsp) ?>,
      datasets: [{
        label: 'Recaudación',
        data: <?= json_encode($monthlyData) ?>,
        fill: true,
        backgroundColor: 'rgba(0,151,167,0.2)',
        borderColor: 'rgba(0,151,167,0.8)',
        tension: 0.3,
        pointBackgroundColor: 'rgba(0,151,167,1)',
        pointBorderColor: '#fff',
        pointRadius: 4
      }]
    };
    const config = {
      type: 'line',
      data: data,
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: '#e0e0e0' },
            ticks: { color: '#555' }
          },
          x: {
            grid: { display: false },
            ticks: { color: '#555' }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#fff',
            titleColor: '#0097A7',
            bodyColor: '#333',
            borderColor: '#0097A7',
            borderWidth: 1
          }
        }
      }
    };
    new Chart(ctx, config);
    <?php endif; ?>
  </script>
</body>
</html>
