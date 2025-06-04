<?php
// ReporteCMPDF.php

require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include(__DIR__ . '/../includes/db.php');

// Función para nombres de mes en español
function nombreMesEsp($mesNum) {
    $meses = [
        1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
        5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto',
        9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
    ];
    return $meses[(int)$mesNum] ?? '';
}

// 1) Leer filtro: rango de fechas o mes/año
$filtro_desc = '';
$where = '';
$params = [];

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $fi = $_GET['fecha_inicio'];
    $ff = $_GET['fecha_fin'];
    $filtro_desc = "Compras desde <strong>".date('d/m/Y',strtotime($fi))."</strong> hasta <strong>".date('d/m/Y',strtotime($ff))."</strong>";
    $where = "c.fecha_compra BETWEEN :fi AND :ff";
    $params = [':fi'=>$fi,':ff'=>$ff];

} elseif (!empty($_GET['mes']) && !empty($_GET['anio'])) {
    $mes = str_pad($_GET['mes'],2,'0',STR_PAD_LEFT);
    $anio= $_GET['anio'];
    $nombreMes = nombreMesEsp($mes);
    $filtro_desc = "Compras del mes <strong>{$nombreMes} {$anio}</strong>";
    $where = "DATE_FORMAT(c.fecha_compra,'%Y-%m') = :am";
    $params = [':am'=>"$anio-$mes"];

} else {
    die('Seleccione rango de fechas o mes/año.');
}

// 2) Consulta de datos
$sql = "
  SELECT p.nombre_producto,
         c.categoria,
         SUM(c.cantidad_comprada) AS total_cantidad,
         SUM(c.precio*c.cantidad_comprada) AS total_compras
    FROM compras c
    JOIN productos p ON c.producto_id=p.id
   WHERE $where
   GROUP BY p.id
   ORDER BY total_compras DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular suma total de todas las compras
$sumaTotal = array_reduce($rows, function($carry, $item) {
    return $carry + $item['total_compras'];
}, 0);

// 3) Rutas imágenes
$logo  = str_replace('\\','/',realpath(__DIR__.'/../Image/logoadesco.jpg'));

// 4) mPDF
$mpdf = new Mpdf([
  'default_font'=>'Roboto',
  'format'=>'A4','orientation'=>'P',
  'margin_top'=>45,'margin_bot'=>15,'margin_left'=>10,'margin_right'=>10
]);

// 5) Header
$mpdf->SetHTMLHeader('
<table width="100%" style="background:#008CBA;color:#fff;font-family:Roboto;">
  <tr>
    <td width="25mm" style="padding:4px;">
      <img src="file:///'.$logo.'" style="width:20mm;height:20mm;border-radius:50%;" alt="Logo">
    </td>
    <td style="text-align:center;vertical-align:middle;">
      <div style="font-size:16pt;">ASOCIACIÓN DE DESARROLLO COMUNAL, SEVERO TEPEYAC (ADESCOSET)</div>
      <div style="font-size:12pt;margin-bottom:2mm;">Colonia Severo López</div>
      <div style="font-size:12pt;">'.$filtro_desc.'</div>
    </td>
    <td width="15mm" style="padding:4px;text-align:right;vertical-align:middle;">
    </td>
  </tr>
</table>
');

// 6) Footer
$mpdf->SetHTMLFooter('
<div style="background:#008CBA;color:#fff;text-align:center;font-size:9pt;padding:4px;font-family:Roboto;">
  Desarrolladores © 2025 Xenia, Ivania, Erick
</div>
');

// 7) Cuerpo: tabla con fila de totales
$html = '
<div style="padding:0 5mm;margin-top:12mm;font-family:Roboto;font-size:10pt;">
  <table width="100%" border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">
    <thead style="background:#f0f0f0;">
      <tr>
        <th>Producto</th><th>Categoría</th><th>Total Cantidad</th><th>Total Compras ($)</th>
      </tr>
    </thead>
    <tbody>';
if ($rows) {
  foreach ($rows as $r) {
    $html .= '
      <tr>
        <td>'.htmlspecialchars($r['nombre_producto']).'</td>
        <td>'.htmlspecialchars($r['categoria']).'</td>
        <td style="text-align:right;">'.number_format($r['total_cantidad'],0).'</td>
        <td style="text-align:right;">$'.number_format($r['total_compras'],2).'</td>
      </tr>';
  }
  // Fila de total general
  $html .= '
    <tr style="font-weight:bold; background:#e0e0e0;">
      <td colspan="3" style="text-align:center;">TOTAL GENERAL</td>
      <td style="text-align:right;">$'.number_format($sumaTotal,2).'</td>
    </tr>';
} else {
  $html .= '<tr><td colspan="4" style="text-align:center;">No hay registros.</td></tr>';
}
$html .= '
    </tbody>
  </table>
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('reporte_compras.pdf','I');
exit;