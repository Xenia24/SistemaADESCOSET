<?php
// reciboPDF.php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;

include(__DIR__ . '/../includes/db.php');

if (!isset($_GET['numero_recibo'])) {
    die('Falta número de recibo');
}
$numero = $_GET['numero_recibo'];

$stmt = $pdo->prepare("SELECT * FROM recibos WHERE numero_recibo = :num");
$stmt->execute([':num' => $numero]);
$recibo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recibo) {
    die('Recibo no encontrado');
}

// Convierte imagen a Base64
function imgBase64($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/'.$type.';base64,'.base64_encode($data);
}
$logoData   = imgBase64(__DIR__.'/../Image/logoadesco.jpg');
$faucetData = imgBase64(__DIR__.'/../Image/grifo-de-agua.png');

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    @page { margin:0; }
    body { margin:0; font-family:Arial,sans-serif; }

    .page {
      width:210mm; height:297mm;
      position:relative;
    }

    /* ===== HEADER ===== */
    .header {
      background: #008CBA;
      width:100%;
      height:35mm;
      position: relative;
      overflow:hidden;
    }
    .header img.logo {
      position:absolute;
      top:7mm; left:10mm;
      width:20mm; height:20mm;
      border-radius:50%;
      z-index:2;
    }
    .header img.faucet {
      position:absolute;
      top:9mm; right:10mm;
      width:10mm;
      z-index:2;
    }
    .header .titles {
      position:absolute;
      top:50%; left:50%;
      transform:translate(-50%,-50%);
      text-align:center;
      color:#fff;
      line-height:1.2;
      white-space:nowrap;
    }
    .header .titles h1 {
      margin:0; font-size:14pt; font-weight:bold; text-transform:uppercase;
    }
    .header .titles h2 {
      margin:2pt 0 0; font-size:11pt;
    }
    .header .titles h3 {
      margin:2pt 0 0; font-size:12pt; font-weight:bold;
    }

    /* ===== WRAPPER (superpone al header) ===== */
    .wrapper {
      position: relative;
      background:#fff;
      width:190mm;
      margin:-8mm auto 0;
      padding:10mm;
      box-sizing:border-box;
      border:1px solid #ccc;
      z-index:1;
    }

    .box {
      border:1px solid #000;
      margin-bottom:6mm;
      padding:4mm;
      page-break-inside:avoid;
    }
    .box-title {
      font-size:12pt; font-weight:bold;
      margin-bottom:3mm;
      border-bottom:2px solid #008CBA;
      padding-bottom:1mm;
    }
    .flex-row {
      display:flex; justify-content:space-between;
      margin-bottom:3mm;
    }
    .flex-col { width:48%; }
    .label {
      font-size:10pt; font-weight:bold;
      margin-bottom:1mm;
    }
    .value {
      background:#eee; border:1px solid #000;
      padding:1mm; font-size:10pt;
      height:5mm; line-height:5mm;
    }

    /* ===== FOOTER ===== */
    .footer {
      position:absolute; bottom:0; left:0;
      width:100%; height:12mm;
      background:#008CBA; color:#fff;
      text-align:center; line-height:12mm;
      font-size:9pt;
    }
  </style>
</head>
<body>
  <div class="page">
    <!-- HEADER -->
    <div class="header">
      <img src="<?= $logoData ?>" class="logo" alt="Logo">
      <img src="<?= $faucetData ?>" class="faucet" alt="Grifo">
      <div class="titles">
        <h1>ASOCIACIÓN DE DESARROLLO COMUNAL, SEVERO TEPEYAC</h1>
        <h2>(ADESCOSET)</h2>
        <h3>RECIBO</h3>
      </div>
    </div>

    <!-- CONTENIDO -->
    <div class="wrapper">
      <!-- Datos Recibo -->
      <div class="box">
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">N° Recibo:</div>
            <div class="value"><?=htmlspecialchars($recibo['numero_recibo'])?></div>
          </div>
          <div class="flex-col">
            <div class="label">Fecha Emisión:</div>
            <div class="value"><?=htmlspecialchars($recibo['fecha_emision'])?></div>
          </div>
        </div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">Fecha Vencimiento:</div>
            <div class="value"><?=htmlspecialchars($recibo['fecha_vencimiento'])?></div>
          </div>
          <div class="flex-col">&nbsp;</div>
        </div>
      </div>

      <!-- Informe Propietario -->
      <div class="box">
        <div class="box-title">Informe Propietario</div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">Propietario:</div>
            <div class="value"><?=htmlspecialchars($recibo['propietario'])?></div>
          </div>
          <div class="flex-col">
            <div class="label">Dirección:</div>
            <div class="value"><?=htmlspecialchars($recibo['direccion'])?></div>
          </div>
        </div>
      </div>

      <!-- Informe Consumo -->
      <div class="box">
        <div class="box-title">Informe de Consumo</div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">Fecha Lectura:</div>
            <div class="value"><?=htmlspecialchars($recibo['fecha_lectura'])?></div>
          </div>
          <div class="flex-col">
            <div class="label">Metros Cúbicos:</div>
            <div class="value"><?=htmlspecialchars($recibo['metros_cubicos'])?></div>
          </div>
        </div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">N° Suministro:</div>
            <div class="value"><?=htmlspecialchars($recibo['numero_suministro'])?></div>
          </div>
          <div class="flex-col">
            <div class="label">L. Anterior:</div>
            <div class="value"><?=htmlspecialchars($recibo['lectura_anterior'])?></div>
          </div>
        </div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">N° Medidor:</div>
            <div class="value"><?=htmlspecialchars($recibo['numero_medidor'])?></div>
          </div>
          <div class="flex-col">
            <div class="label">L. Actual:</div>
            <div class="value"><?=htmlspecialchars($recibo['lectura_actual'])?></div>
          </div>
        </div>
      </div>

      <!-- Estado Cuenta -->
      <div class="box">
        <div class="box-title">Estado de Cuenta</div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">Meses Pendientes:</div>
            <div class="value"><?=htmlspecialchars($recibo['meses_pendiente'])?></div>
          </div>
          <div class="flex-col">
            <div class="label">Multas:</div>
            <div class="value"><?=htmlspecialchars($recibo['multas'])?></div>
          </div>
        </div>
        <div class="flex-row">
          <div class="flex-col">
            <div class="label">Total:</div>
            <div class="value"><?=htmlspecialchars($recibo['total'])?></div>
          </div>
          <div class="flex-col">&nbsp;</div>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
  </div>
</body>
</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("Recibo_{$numero}.pdf", ['Attachment'=>true]);
