<?php
// reporte.php — reporte en español con PDF de diseño más atractivo, manteniendo la información

require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit();
}

include('../includes/db.php');

// Definimos un arreglo con los meses en español (1 => 'Enero', 2 => 'Febrero', ...)
$meses_es = [
  1 => 'Enero',
  2 => 'Febrero',
  3 => 'Marzo',
  4 => 'Abril',
  5 => 'Mayo',
  6 => 'Junio',
  7 => 'Julio',
  8 => 'Agosto',
  9 => 'Septiembre',
  10 => 'Octubre',
  11 => 'Noviembre',
  12 => 'Diciembre'
];

// Parámetros GET
$tipo = $_GET['tipo']  ?? 'pagados';
$mes  = intval($_GET['mes']   ?? date('m'));
$anio = intval($_GET['anio']  ?? date('Y'));
$pdf  = isset($_GET['pdf']) && $_GET['pdf'] === '1';

// Validar que $mes esté entre 1 y 12; de lo contrario, usar mes actual
if ($mes < 1 || $mes > 12) {
  $mes = intval(date('m'));
}

// Construir consulta según el tipo de reporte
switch ($tipo) {
  case 'pagados':
    $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago = 'Pagado'
                   AND MONTH(fecha_emision) = :mes
                   AND YEAR(fecha_emision) = :anio";
    break;
  case 'nopagados':
    $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago = 'No pagado'
                   AND MONTH(fecha_emision) = :mes
                   AND YEAR(fecha_emision) = :anio";
    break;
  case 'despues_vencimiento':
    $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago = 'Pagado'
                   AND fecha_emision > fecha_vencimiento
                   AND MONTH(fecha_emision) = :mes
                   AND YEAR(fecha_emision) = :anio";
    break;
  case 'mora':
    $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago = 'En mora'
                   AND MONTH(fecha_emision) = :mes
                   AND YEAR(fecha_emision) = :anio";
    break;
  case 'total':
    $sql = "SELECT SUM(total) AS total_recaudado
                  FROM recibos
                 WHERE estado_pago = 'Pagado'
                   AND MONTH(fecha_emision) = :mes
                   AND YEAR(fecha_emision) = :anio";
    break;
  default:
    die('Tipo de reporte inválido');
}

$stmt = $pdo->prepare($sql);
$stmt->execute([':mes' => $mes, ':anio' => $anio]);

if ($tipo === 'total') {
  $res = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalRecaudado = $res['total_recaudado'] ?? 0;
} else {
  $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Si pidieron PDF, generarlo
if ($pdf) {
  $logoPath   = str_replace('\\', '/', realpath(__DIR__ . '/../Image/logoadesco.jpg'));
  $faucetPath = str_replace('\\', '/', realpath(__DIR__ . '/../Image/grifo-de-agua.png'));
  $mpdf = new Mpdf([
    'default_font'  => 'Roboto',
    'margin_top'    => 50,
    'margin_bottom' => 20,
    'margin_left'   => 15,
    'margin_right'  => 15,
  ]);

  // Obtenemos el nombre del mes en español
  $mesNombreEs = $meses_es[$mes];

  // Cabecera
  $header = '
    <table width="100%" style="background: #0097A7; color: #fff; font-family: Roboto, sans-serif;">
      <tr>
        <td width="20mm" style="vertical-align: middle; padding: 5px;">
          <img src="file:///' . $logoPath . '" style="width: 18mm; height: 18mm; border-radius: 50%;" alt="Logo">
        </td>
        <td style="text-align: center; vertical-align: middle;">
          <div style="font-size: 16pt; font-weight: 700; text-transform: uppercase;">Asociación de Desarrollo Comunal, Severo Tepeyac</div>
          <div style="font-size: 11pt; margin-top: 2px;">Colonia Severo López</div>
          <div style="font-size: 11pt; margin-top: 4px;"><strong>Reporte ' . mb_strtoupper($tipo, 'UTF-8') . ' • ' . $mesNombreEs . ' ' . $anio . '</strong></div>
        </td>
        <td width="20mm" style="vertical-align: middle; padding: 5px; text-align: right;">
          <img src="file:///' . $faucetPath . '" style="width: 12mm;" alt="Ícono Grifo">
        </td>
      </tr>
    </table>';
  $mpdf->SetHTMLHeader($header);

  // Pie de página
  $footer = '
    <div style="background: #0097A7; color: #fff; text-align: center; font-size: 9pt; padding: 4px; font-family: Roboto, sans-serif;">
      © 2025 Xenia, Ivania, Erick
    </div>';
  $mpdf->SetHTMLFooter($footer);

  // Contenido
  $html = '<div style="font-family: Roboto, sans-serif; margin-top: 10px; padding: 0 10px;">';
  if ($tipo === 'total') {
    $html .= '
        <div style="text-align: center; margin-top: 20mm;">
          <span style="font-size: 14pt; font-weight: 700;">Total recaudado:</span>
          <span style="font-size: 14pt; margin-left: 5px;">$' . number_format($totalRecaudado, 2) . '</span>
        </div>';
  } else {
    $html .= '
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
            <thead>
              <tr style="background: #f0f0f0; color: #333;">
                <th style="padding: 8px; border: 1px solid #ddd;">Nº Recibo</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Propietario</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Fecha Emisión</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Estado</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Monto ($)</th>
              </tr>
            </thead>
            <tbody>';
    foreach ($datos as $r) {
      $html .= '
              <tr>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($r['numero_recibo']) . '</td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($r['propietario']) . '</td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($r['fecha_emision']) . '</td>
                <td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($r['estado_pago']) . '</td>
                <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">' . number_format($r['total'], 2) . '</td>
              </tr>';
    }
    $html .= '
            </tbody>
          </table>
        </div>';
  }
  $html .= '</div>';

  $mpdf->WriteHTML($html);
  $mpdf->Output("reporte_{$tipo}_{$mes}_{$anio}.pdf", 'I');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Reporte – <?= ucfirst(str_replace('_', ' ', $tipo)) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

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
      background: #f4f4f4;
      color: #333;
    }

    /* Top bar */
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
      z-index: 100;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    
    .admin-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .admin-container a {
      color: #fff;
      text-decoration: none;
      font-size: 16px;
    }

    /* Main layout */
    .container {
      display: flex;
      flex: 1;
      padding-top: 50px;
      /* espacio para la top-bar */
      padding-bottom: 30px;
      /* suficiente espacio para que no quede tapado por la bottom-bar */
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
      /* Restauramos Arial solo para el menú lateral */
      font-family: Arial, sans-serif;
    }

    .sidebar img.logo {
      width: 120px;
      margin: 0 auto 20px;
      border-radius: 8px;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .sidebar a,
    .sidebar .toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s, transform 0.1s;
      /* Aseguramos el Arial en cada enlace/toggle */
      font-weight: 500;
    }

    .sidebar a:hover,
    .sidebar .toggle:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateX(2px);
    }

    .sidebar a img,
    .sidebar .toggle img {
      width: 20px;
      height: 20px;
    }

    .submenu {
      display: none;
      flex-direction: column;
      gap: 8px;
      padding-left: 20px;
      margin-top: 5px;
      font-family: Arial, sans-serif;
      /* También Arial dentro del submenu */
    }

    .submenu.show {
      display: flex;
    }

    .submenu a {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 6px;
      color: #fff;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s, transform 0.1s;
      font-family: Arial, sans-serif;
      /* Arial para cada opción del submenu */
    }

    .submenu a:hover {
      background: rgba(255, 255, 255, 0.35);
      transform: translateX(2px);
    }

    /* Content */
    .content {
      flex: 1;
      margin: 10px;
      padding: 20px;
      background: #fafafa;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
      overflow-y: auto;
    }

    .content h2 {
      margin-bottom: 20px;
      font-size: 24px;
      color: #007c91;
      text-transform: capitalize;
      border-bottom: 2px solid #0097A7;
      padding-bottom: 6px;
      font-weight: 500;
    }

    /* Tarjeta de filtros */
    .filters-card {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      margin-bottom: 25px;
      transition: box-shadow 0.3s;
    }

    .filters-card:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .filters-card label {
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .filters-card select {
      padding: 8px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
      background: #fff;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .filters-card select:focus {
      outline: none;
      border-color: #0097A7;
      box-shadow: 0 0 0 3px rgba(0, 151, 167, 0.2);
    }

    .filters-card button {
      border: none;
      background: #0097A7;
      color: #fff;
      padding: 8px 14px;
      font-size: 14px;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background 0.3s, transform 0.1s;
    }

    .filters-card button:hover {
      background: #007c91;
      transform: translateY(-1px);
    }

    .filters-card button:active {
      transform: translateY(1px);
    }

    /* Tabla moderna */
    .table-container {
      overflow-x: auto;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 14px;
      min-width: 600px;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
    }

    thead {
      background: linear-gradient(90deg, #0097a7, #007c91);
      color: #fff;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    th,
    td {
      padding: 12px 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    tbody tr:nth-child(even) {
      background: #f9f9f9;
    }

    tbody tr:hover {
      background: #f1f1f1;
    }

    th:last-child,
    td:last-child {
      text-align: right;
    }

    .no-data {
      text-align: center;
      margin-top: 40px;
      font-size: 16px;
      color: #666;
    }

    .total-container {
      text-align: center;
      font-size: 18px;
      margin-top: 20px;
      font-weight: 500;
      color: #007c91;
    }

    /* Bottom bar */
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
      font-size: 15px;
      box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .filters-card {
        flex-direction: column;
        align-items: stretch;
      }

      .filters-card select,
      .filters-card button {
        width: 100%;
      }

      th,
      td {
        font-size: 12px;
        padding: 10px 8px;
      }

      .content {
        margin: 5px;
        padding: 15px;
      }
    }
  </style>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo">
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>

      <div class="toggle"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</div>
      <div class="submenu">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
        <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
      </div>

      <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
      <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>

      <div class="toggle"><img src="../Image/reporte.png" alt=""> Reporte ⏷</div>
      <div class="submenu">
        <a href="reporte.php?tipo=pagados">Recibos pagados</a>
        <a href="reporte.php?tipo=nopagados">No pagados</a>
        <a href="reporte.php?tipo=despues_vencimiento">Pagados tras venc.</a>
        <a href="reporte.php?tipo=mora">En mora</a>
        <a href="reporte.php?tipo=total">Total recaudado</a>
      </div>
    </div>

    <!-- Content -->
    <div class="content">
      <h2>Reporte – <?= ucfirst(str_replace('_', ' ', $tipo)) ?></h2>
      <div class="filters-card">
        <label for="mes">Mes:</label>
        <select id="mes">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= sprintf('%02d', $m) ?>" <?= $m === $mes ? 'selected' : '' ?>>
              <?= $meses_es[$m] ?>
            </option>
          <?php endfor; ?>
        </select>

        <label for="anio">Año:</label>
        <select id="anio">
          <?php for ($y = date('Y') - 3; $y <= date('Y'); $y++): ?>
            <option value="<?= $y ?>" <?= $y === $anio ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>

        <button id="filtrar"><i class="fas fa-filter"></i> Filtrar</button>
        <button id="descargar"><i class="fas fa-file-pdf"></i> Descargar PDF</button>
      </div>

      <?php if (isset($datos) && $datos): ?>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>N° Recibo</th>
                <th>Propietario</th>
                <th>Fecha Emisión</th>
                <th>Estado</th>
                <th>Monto ($)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($datos as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['numero_recibo']) ?></td>
                  <td><?= htmlspecialchars($r['propietario']) ?></td>
                  <td><?= htmlspecialchars($r['fecha_emision']) ?></td>
                  <td><?= htmlspecialchars($r['estado_pago']) ?></td>
                  <td><?= number_format($r['total'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php elseif ($tipo === 'total'): ?>
        <div class="total-container">
          <strong>Total recaudado:</strong> $<?= number_format($totalRecaudado, 2) ?>
        </div>
      <?php else: ?>
        <div class="no-data">No hay registros para este periodo.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Toggle submenús
    document.querySelectorAll('.toggle').forEach(btn =>
      btn.onclick = () => btn.nextElementSibling.classList.toggle('show')
    );
    // Filtrar sin recargar formulario
    document.getElementById('filtrar').onclick = () => {
      const t = '<?= $tipo ?>',
        m = document.getElementById('mes').value,
        a = document.getElementById('anio').value;
      location.href = `reporte.php?tipo=${t}&mes=${m}&anio=${a}`;
    };
    // Descargar PDF en nueva pestaña
    document.getElementById('descargar').onclick = () => {
      const t = '<?= $tipo ?>',
        m = document.getElementById('mes').value,
        a = document.getElementById('anio').value;
      window.open(`reporte.php?tipo=${t}&mes=${m}&anio=${a}&pdf=1`, '_blank');
    };
  </script>
</body>

</html>