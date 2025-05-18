<?php
// reporte.php

require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');

$tipo = $_GET['tipo']  ?? 'pagados';
$mes  = $_GET['mes']   ?? date('m');
$anio = $_GET['anio']  ?? date('Y');
$pdf  = isset($_GET['pdf']) && $_GET['pdf']==='1';

switch ($tipo) {
    case 'pagados':
        $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago='Pagado'
                   AND MONTH(fecha_emision)=:mes
                   AND YEAR(fecha_emision)=:anio";
        break;
    case 'nopagados':
        $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago='No pagado'
                   AND MONTH(fecha_emision)=:mes
                   AND YEAR(fecha_emision)=:anio";
        break;
    case 'despues_vencimiento':
        $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago='Pagado'
                   AND fecha_emision>fecha_vencimiento
                   AND MONTH(fecha_emision)=:mes
                   AND YEAR(fecha_emision)=:anio";
        break;
    case 'mora':
        $sql = "SELECT numero_recibo, propietario, fecha_emision, estado_pago, total
                  FROM recibos
                 WHERE estado_pago='En mora'
                   AND MONTH(fecha_emision)=:mes
                   AND YEAR(fecha_emision)=:anio";
        break;
    case 'total':
        $sql = "SELECT SUM(total) AS total_recaudado
                  FROM recibos
                 WHERE estado_pago='Pagado'
                   AND MONTH(fecha_emision)=:mes
                   AND YEAR(fecha_emision)=:anio";
        break;
    default:
        die('Tipo de reporte inválido');
}

$stmt = $pdo->prepare($sql);
$stmt->execute([':mes'=>$mes,':anio'=>$anio]);

if ($tipo === 'total') {
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRecaudado = $res['total_recaudado'] ?? 0;
} else {
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($pdf) {
    $logoPath   = str_replace('\\','/', realpath(__DIR__.'/../Image/logoadesco.jpg'));
    $faucetPath = str_replace('\\','/', realpath(__DIR__.'/../Image/grifo-de-agua.png'));

    $mpdf = new Mpdf([
        'default_font'  => 'Roboto',
        'margin_top'    => 45,
        'margin_bottom' => 15,
        'margin_left'   => 10,
        'margin_right'  => 10,
    ]);

    $header = '
    <table width="100%" style="background:#008CBA;color:#fff;font-family:Roboto,sans-serif;">
      <tr>
        <td width="25mm" style="vertical-align:middle;padding:4px;">
          <img src="file:///'.$logoPath.'" style="width:20mm;height:20mm;border-radius:50%;" alt="Logo">
        </td>
        <td style="text-align:center;vertical-align:middle;">
          <div style="font-size:16pt;margin-bottom:2mm;">ASOCIACIÓN DE DESARROLLO COMUNAL, SEVERO TEPEYAC (ADESCOSET)</div>
          <div style="font-size:12pt;margin-bottom:1mm;">Colonia Severo López</div>
          <div style="font-size:12pt;">Reporte '.htmlspecialchars(strtoupper($tipo)).' — '.DateTime::createFromFormat('!m',$mes)->format('F').' '.$anio.'</div>
        </td>
        <td width="15mm" style="vertical-align:middle;padding:4px;text-align:right;">
          <img src="file:///'.$faucetPath.'" style="width:10mm;" alt="Icono Grifo">
        </td>
      </tr>
    </table>';
    $mpdf->SetHTMLHeader($header);

    $footer = '
    <div style="background:#008CBA;color:#fff;text-align:center;
                font-size:9pt;padding:4px 0;font-family:Roboto,sans-serif;">
      Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>';
    $mpdf->SetHTMLFooter($footer);

    $html = '<div style="font-family:Roboto,sans-serif;padding:0 5mm;margin-top:12mm;">';
    if ($tipo === 'total') {
        $html .= '<p style="text-align:center;font-size:14pt;"><strong>Total recaudado:</strong> $'.number_format($totalRecaudado,2).'</p>';
    } else {
        $html .= '
        <table width="100%" border="1" cellspacing="0" cellpadding="4"
               style="border-collapse:collapse;font-size:10pt;font-family:Roboto,sans-serif;">
          <thead>
            <tr style="background:#f0f0f0;">
              <th>N° Recibo</th>
              <th>Propietario</th>
              <th>Fecha Emisión</th>
              <th>Estado</th>
              <th style="width:25mm;">Monto</th>
            </tr>
          </thead>
          <tbody>';
        foreach ($datos as $r) {
            $html .= '
            <tr>
              <td>'.htmlspecialchars($r['numero_recibo']).'</td>
              <td>'.htmlspecialchars($r['propietario']).'</td>
              <td>'.htmlspecialchars($r['fecha_emision']).'</td>
              <td>'.htmlspecialchars($r['estado_pago']).'</td>
              <td style="text-align:right;">'.number_format($r['total'],2).'</td>
            </tr>';
        }
        $html .= '
          </tbody>
        </table>';
    }
    $html .= '</div>';

    $mpdf->WriteHTML($html);
    $mpdf->Output("reporte_{$tipo}_{$mes}_{$anio}.pdf",'D');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Reporte – <?= ucfirst(str_replace('_',' ',$tipo)) ?></title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;}
    body{display:flex;flex-direction:column;height:100vh;background:#f4f4f4;}
    .top-bar{position:fixed;top:0;left:0;right:0;height:60px;background:#0097A7;color:#fff;
             display:flex;justify-content:space-between;align-items:center;padding:0 20px;}
    .admin-container{display:flex;align-items:center;gap:10px;}
    .admin-container a{color:#fff;text-decoration:underline;}
    .container{display:flex;flex:1;padding-top:60px;}
    .sidebar{width:250px;background:#0097A7;color:#fff;padding:20px;display:flex;flex-direction:column;gap:10px;}
    .sidebar img.logo{width:120px;margin:0 auto 20px;border-radius:10px;}
    .sidebar a, .sidebar .toggle{color:#fff;text-decoration:none;display:flex;align-items:center;gap:10px;
                                 padding:10px;border-radius:5px;transition:background .3s;cursor:pointer;}
    .sidebar a:hover, .sidebar .toggle:hover{background:#007c91;}
    .sidebar a img, .sidebar .toggle img{width:20px;height:20px;}
    .submenu{display:none;flex-direction:column;gap:5px;padding-left:20px;}
    .submenu.show{display:flex;}
    .submenu a{display:flex;align-items:center;gap:8px;padding:8px;color:#fff;
               background:rgba(255,255,255,0.2);border-radius:5px;transition:background .3s;}
    .submenu a:hover{background:rgba(255,255,255,0.4);}
    .content{flex:1;background:#fff;padding:20px;overflow:auto;font-family:Roboto,sans-serif;}
    .report-filters{display:flex;gap:12px;margin-bottom:20px;align-items:center;}
    .report-filters select{padding:6px 12px;font-size:14px;}
    .report-filters button{border:none;background:none;cursor:pointer;font-size:14px;padding:6px 12px;
                           display:flex;align-items:center;gap:6px;}
    .report-filters button.pdf-btn img{width:20px;height:20px;}
    table{width:100%;border-collapse:collapse;margin-top:10px;}
    th,td{border:1px solid #ccc;padding:6px;font-size:12px;}
    th{background:#0097A7;color:#fff;}
    .bottom-bar{width:100%;text-align:center;padding:10px;background:#0097A7;color:#fff;}
  </style>
</head>
<body>
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div class="admin-container">
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="container">
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

    <div class="content">
      <h2>Reporte – <?= ucfirst(str_replace('_',' ',$tipo)) ?></h2>
      <div class="report-filters">
        <label>Mes:
          <select id="mes">
            <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?=sprintf('%02d',$m)?>" <?=$m==$mes?'selected':''?>>
              <?=DateTime::createFromFormat('!m',$m)->format('F')?>
            </option>
            <?php endfor; ?>
          </select>
        </label>
        <label>Año:
          <select id="anio">
            <?php for($y=date('Y')-3;$y<=date('Y');$y++): ?>
            <option value="<?=$y?>" <?=$y==$anio?'selected':''?>><?=$y?></option>
            <?php endfor; ?>
          </select>
        </label>
        <button id="filtrar">Filtrar</button>
        <button id="descargar" class="pdf-btn">
          <img src="../Image/pdf-icon.png" alt="PDF" title="Descargar PDF">
        </button>
      </div>

      <?php if(isset($datos) && $datos): ?>
      <table>
        <thead>
          <tr>
            <th>N° Recibo</th>
            <th>Propietario</th>
            <th>Fecha Emisión</th>
            <th>Estado</th>
            <th>Monto</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($datos as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['numero_recibo'])?></td>
            <td><?=htmlspecialchars($r['propietario'])?></td>
            <td><?=htmlspecialchars($r['fecha_emision'])?></td>
            <td><?=htmlspecialchars($r['estado_pago'])?></td>
            <td style="text-align:right;"><?=number_format($r['total'],2)?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php elseif($tipo==='total'): ?>
      <p><strong>Total recaudado:</strong> $<?=number_format($totalRecaudado,2)?></p>
      <?php else: ?>
      <p>No hay registros para este periodo.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    document.querySelectorAll('.toggle').forEach(btn =>
      btn.onclick = () => btn.nextElementSibling.classList.toggle('show')
    );
    document.getElementById('filtrar').onclick = () => {
      const t = '<?=$tipo?>', m = document.getElementById('mes').value,
            a = document.getElementById('anio').value;
      location.href = `reporte.php?tipo=${t}&mes=${m}&anio=${a}`;
    };
    document.getElementById('descargar').onclick = () => {
      const t = '<?=$tipo?>', m = document.getElementById('mes').value,
            a = document.getElementById('anio').value;
      location.href = `reporte.php?tipo=${t}&mes=${m}&anio=${a}&pdf=1`;
    };
  </script>
</body>
</html>
