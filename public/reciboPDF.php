<?php
// reciboPDF.php — imprime 2 recibos idénticos, uno debajo del otro, centrados en A4 portrait

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;

include(__DIR__ . '/../includes/db.php');

if (empty($_GET['numero_recibo'])) {
    die('Falta número de recibo');
}
$numero = $_GET['numero_recibo'];

$stmt = $pdo->prepare("SELECT * FROM recibos WHERE numero_recibo = :num");
$stmt->execute([':num' => $numero]);
$recibo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recibo) {
    die('Recibo no encontrado');
}

function imgBase64($path) {
    if (!file_exists($path)) return '';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
}
$logo = imgBase64(__DIR__ . '/../Image/logoadesco.jpg');
$icon = imgBase64(__DIR__ . '/../Image/grifo-de-agua.png');

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    @page { margin:0; size:210mm 297mm; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      font-size: 7pt;
    }
    .page {
      display: flex;
      flex-direction: column;
      align-items: center;      /* centra los wrappers horizontalmente */
      width: 210mm;
      height: 297mm;
      padding: 5mm;
      box-sizing: border-box;
    }
    .wrapper {
      width: 80%;                 
      border: 1px solid #ccc;
      padding: 3mm;
      margin-bottom: 5mm;    
      margin: 0 auto;     
      display: flex;
      flex-direction: column;
      page-break-inside: avoid;
    }
    .header {
      position: relative;
      background: #008CBA;
      height: 20mm;
      margin-bottom: 3px;
    }
    .header img.logo {
      position: absolute;
      top: 2mm;
      left: 2mm;
      width: 10mm;
      height: 10mm;
    }
    .header img.icon {
      position: absolute;
      top: 3mm;
      right: 2mm;
      width: 8mm;
    }
    .titles {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #fff;
      text-align: center;
      line-height: 1;
    }
    .titles h1 {
      margin: 0;
      font-size: 9pt;
      font-weight: bold;
    }
    .titles h2 {
      margin: 1pt 0 0;
      font-size: 7pt;
    }
    .titles h4 {
      margin: 2pt 0 0;
      font-size: 9pt;
      font-weight: bold;
    }
    .info {
      display: flex;
      justify-content: space-between;
      margin: 4px 0;
    }
    .block {
      width: 48%;
    }
    .label {
      display: inline-block;
      width: 80px;
      font-weight: bold;
    }
    .value {
      display: inline-block;
    }
    .subtitle {
      text-align: center;
      font-weight: bold;
      font-size: 8pt;
      border-top: 1px solid #000;
      border-bottom: 1px solid #000;
      margin: 4px 0;
      padding: 2px 0;
    }
    .summary {
      display: flex;
      justify-content: space-between;
      margin-top: 4px;
    }
    table {
      border-collapse: collapse;
      width: 48%;
      font-size: 7pt;
    }
    th, td {
      border: 1px solid #000;
      padding: 2px 4px;
    }
    th {
      background: #f0f0f0;
      text-align: left;
    }
    .right th, .right td {
      text-align: right;
    }
    .footer {
      margin-top: auto;
      text-align: center;
      font-size: 6.5pt;
      color: #666;
    }
  </style>
</head>
<body>
  <div class="page">
    <?php for ($i = 0; $i < 2; $i++): ?>
      <div class="wrapper">
        <!-- HEADER -->
        <div class="header">
          <?php if ($logo): ?>
            <img src="<?= $logo ?>" class="logo">
          <?php endif; ?>
          <?php if ($icon): ?>
            <img src="<?= $icon ?>" class="icon">
          <?php endif; ?>
          <div class="titles">
            <h1>ASOCIACIÓN DE DESARROLLO COMUNAL</h1>
            <h2>SEVERO TEPEYAC – Col. Severo López</h2>
            <h4>RECIBO</h4>
          </div>
        </div>

        <!-- DATOS PRINCIPALES -->
        <div class="info">
          <div class="block">
            <div>
              <span class="label">N° Recibo:</span>
              <span class="value"><?= htmlspecialchars($recibo['numero_recibo']) ?></span>
            </div>
            <div>
              <span class="label">Propietario:</span>
              <span class="value"><?= htmlspecialchars($recibo['propietario']) ?></span>
            </div>
            <div>
              <span class="label">Dirección:</span>
              <span class="value"><?= htmlspecialchars($recibo['direccion']) ?></span>
            </div>
          </div>
          <div class="block">
            <div>
              <span class="label">Emisión:</span>
              <span class="value"><?= htmlspecialchars($recibo['fecha_emision']) ?></span>
            </div>
            <div>
              <span class="label">Vencimiento:</span>
              <span class="value"><?= htmlspecialchars($recibo['fecha_vencimiento']) ?></span>
            </div>
          </div>
        </div>

        <div class="subtitle">Resumen de Consumo y Estado</div>

        <!-- TABLAS LADO A LADO -->
        <div class="summary">
          <table>
            <thead>
              <tr>
                <th colspan="2">Informe de Consumo</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Fecha Lectura</td>
                <td><?= htmlspecialchars($recibo['fecha_lectura']) ?></td>
              </tr>
              <tr>
                <td>m³ Consumidos</td>
                <td><?= htmlspecialchars($recibo['metros_cubicos']) ?></td>
              </tr>
              <tr>
                <td>N° Suministro</td>
                <td><?= htmlspecialchars($recibo['numero_suministro']) ?></td>
              </tr>
              <tr>
                <td>L. Anterior</td>
                <td><?= htmlspecialchars($recibo['lectura_anterior']) ?></td>
              </tr>
              <tr>
                <td>L. Actual</td>
                <td><?= htmlspecialchars($recibo['lectura_actual']) ?></td>
              </tr>
              <tr>
                <td>N° Medidor</td>
                <td><?= htmlspecialchars($recibo['numero_medidor']) ?></td>
              </tr>
            </tbody>
          </table>
          <table class="right">
            <thead>
              <tr>
                <th colspan="2">Estado de Cuenta</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Meses Pendientes</td>
                <td><?= htmlspecialchars($recibo['meses_pendiente']) ?></td>
              </tr>
              <tr>
                <td>Multas</td>
                <td><?= htmlspecialchars($recibo['multas']) ?></td>
              </tr>
              <tr>
                <td>Total</td>
                <td><?= htmlspecialchars($recibo['total']) ?></td>
              </tr>
            </tbody>
          </table>
        </div>

     
      </div>
    <?php endfor; ?>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("Recibos_{$numero}.pdf", ['Attachment' => false]);
exit();
?>
