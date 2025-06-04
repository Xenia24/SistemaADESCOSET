<?php
// ReporteCMPDF.php

require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include(__DIR__ . '/../includes/db.php');

// Validar año desde GET
if (!isset($_GET['anio'])) {
    echo "Año no especificado.";
    exit();
}

$anio = $_GET['anio'];

// Consultar ventas anuales agrupadas por usuario y producto
$stmt = $pdo->prepare("
    SELECT 
        u.nombre_completo,
        p.nombre_producto,
        SUM(v.cantidad) AS total_cantidad,
        SUM(v.total) AS total_ventas
    FROM ventas v
    INNER JOIN usuariosag u ON v.usuario_id = u.id
    INNER JOIN productos p ON v.producto_id = p.id
    WHERE YEAR(v.fecha) = :anio
    GROUP BY u.id, p.id
    ORDER BY total_ventas DESC
");
$stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total general de ventas
$sumaTotalVentas = 0;
foreach ($ventas as $v) {
    $sumaTotalVentas += $v['total_ventas'];
}

// Ruta del logo
$logoPath = str_replace('\\', '/', realpath(__DIR__ . '/../Image/logoadesco.jpg'));

// Inicializar mPDF con fuente y márgenes personalizados
$mpdf = new Mpdf([
    'default_font'  => 'Roboto',
    'margin_top'    => 45,
    'margin_bottom' => 15,
    'margin_left'   => 10,
    'margin_right'  => 10,
]);

// Cabecera del PDF
$header = '
<table width="100%" style="background:#008CBA;color:#fff;font-family:Roboto,sans-serif;">
  <tr>
    <td width="25mm" style="vertical-align:middle;padding:4px;">
      <img src="file:///' . $logoPath . '" style="width:20mm;height:20mm;border-radius:50%;" alt="Logo">
    </td>
    <td style="text-align:center;vertical-align:middle;">
      <div style="font-size:16pt;margin-bottom:2mm;">ASOCIACIÓN DE DESARROLLO COMUNAL, SEVERO TEPEYAC (ADESCOSET)</div>
      <div style="font-size:12pt;margin-bottom:1mm;">Colonia Severo López</div>
      <div style="font-size:12pt;">Reporte Anual de Ventas — Año ' . htmlspecialchars($anio) . '</div>
    </td>
  </tr>
</table>';
$mpdf->SetHTMLHeader($header);

// Pie de página
$footer = '
<div style="
     background:#008CBA;
     color:#fff;
     text-align:center;
     font-size:9pt;
     padding:4px 0;
     font-family:Roboto,sans-serif;
">
  Desarrolladores © 2025 Xenia, Ivania, Erick
</div>';
$mpdf->SetHTMLFooter($footer);

// Contenido del PDF
$html = '<div style="font-family:Roboto,sans-serif;padding:0 5mm;margin-top:12mm;">';

if (count($ventas) > 0) {
    $html .= '
    <table width="100%" border="1" cellspacing="0" cellpadding="4" style="
           border-collapse:collapse;
           font-size:10pt;
           font-family:Roboto,sans-serif;
         ">
      <thead>
        <tr style="background:#f0f0f0;color:white;">
          <th>Usuario</th>
          <th>Producto</th>
          <th>Total Productos</th>
          <th>Total Ventas ($)</th>
        </tr>
      </thead>
      <tbody>';
    foreach ($ventas as $venta) {
        $html .= '
        <tr>
          <td>' . htmlspecialchars($venta['nombre_completo']) . '</td>
          <td>' . htmlspecialchars($venta['nombre_producto']) . '</td>
          <td style="text-align:right;">' . number_format($venta['total_cantidad'], 0) . '</td>
          <td style="text-align:right;">$' . number_format($venta['total_ventas'], 2) . '</td>
        </tr>';
    }
    // Fila del total general
    $html .= '
        <tr style="font-weight:bold; background:#e0e0e0;">
          <td colspan="3" style="text-align:center;">TOTAL GENERAL</td>
          <td style="text-align:right;">$' . number_format($sumaTotalVentas, 2) . '</td>
        </tr>';
    $html .= '
      </tbody>
    </table>';
} else {
    $html .= '<p style="text-align:center;font-size:12pt;">No hay datos para el año seleccionado.</p>';
}

$html .= '</div>';

// Generar PDF
$mpdf->WriteHTML($html);
$mpdf->Output("Reporte_Anual_Ventas_{$anio}.pdf", 'I');