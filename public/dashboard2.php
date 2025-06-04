<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$registros = obtenerProductosConPocaCantidad($pdo);

function obtenerProductosConPocaCantidad($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE cantidad < 10");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Consultar el balance mensual de gastos
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(c.fecha_compra, '%Y-%m') AS mes,
        p.nombre_producto,
        SUM(c.cantidad_comprada) AS total_cantidad,
        SUM(c.cantidad_comprada * c.precio) AS total_gasto
    FROM compras c
    JOIN productos p ON c.producto_id = p.id
    GROUP BY mes, p.nombre_producto
    ORDER BY mes DESC, p.nombre_producto
");

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular el total general
$total_general = 0;
foreach ($resultados as $fila) {
    $total_general += $fila['total_gasto'];
}

$query = "
    SELECT 
        usuario_id,
        MONTH(fecha) AS mes,
        SUM(cantidad) AS total_retirado
    FROM ventas
    WHERE YEAR(fecha) = YEAR(CURDATE())
    GROUP BY usuario_id, MONTH(fecha)
    ORDER BY usuario_id, mes
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
];

$usuarios_data = [];

// Agrupa los datos por usuario
foreach ($datos as $fila) {
    $uid = $fila['usuario_id'];
    $mes = (int)$fila['mes'] - 1;

    if (!isset($usuarios_data[$uid])) {
        $usuarios_data[$uid] = array_fill(0, 12, 0);
    }

    $usuarios_data[$uid][$mes] = (int)$fila['total_retirado'];
}

// Prepara los datasets para Chart.js
$datasets = [];
$colores = ['#2196F3', '#4CAF50', '#FF9800', '#9C27B0', '#F44336', '#00BCD4']; // Puedes agregar más colores
$i = 0;

foreach ($usuarios_data as $uid => $totales) {
    $datasets[] = [
        'label' => "Usuario $uid",
        'data' => $totales,
        'backgroundColor' => $colores[$i % count($colores)],
        'borderColor' => $colores[$i % count($colores)],
        'borderWidth' => 1
    ];
    $i++;
}
// Contar usuarios por tipo
$stmtCobro = $pdo->prepare("SELECT COUNT(*) FROM usuariosag WHERE tipo_usuario = 'General Cobro'");
$stmtCobro->execute();
$totalCobro = $stmtCobro->fetchColumn();

$stmtInventario = $pdo->prepare("SELECT COUNT(*) FROM usuariosag WHERE tipo_usuario = 'General Inventario'");
$stmtInventario->execute();
$totalInventario = $stmtInventario->fetchColumn();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <title>Sistema de Inventario</title>
    <style>
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
            background-color: #f4f4f4;
        }

        .top-bar {
            width: 100%;
            height: 60px;
            background-color: #0097A7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            /* ← CAMBIO AQUÍ */
            top: 0;
            left: 0;
            z-index: 1000;
            /* Asegura que esté sobre otros elementos */
            color: white;
        }

        .top-bar h2 {
            font-size: 18px;
        }

        .admin-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-container a {
            text-decoration: none;
            background-color: red;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .admin-container a:hover {
            background-color: darkred;
        }

        .admin-container span {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
        }

        .icon {
            font-size: 18px;
        }

        .container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background-color: #0097A7;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            overflow-y: auto;
        }

        .sidebar img.logo {
            width: 120px;
            margin: 0 auto 20px auto;
            display: block;
            border-radius: 10px;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 15px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #007c91;
        }

        .sidebar a img {
            width: 20px;
            height: 20px;
        }

        .submenu {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding-left: 20px;
            margin-top: 8px;
        }

        .submenu a {
            font-size: 14px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .submenu a img {
            width: 16px;
            height: 16px;
        }

        .content {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-left: 270px;
            /* espacio para el sidebar */
            margin-top: 80px;
            text-align: center;
            /* espacio para la top-bar */
        }


        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }

        .sidebar {
            width: 250px;
            transition: all 0.3s ease;
        }

        .sidebar.hidden {
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        .content {
            transition: margin-left 0.3s ease;
        }

        .content.sidebar-hidden {
            margin-left: 0;
        }

        .container2 {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .container2 h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        table.custom-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table.custom-table th,
        table.custom-table td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }

        table.custom-table th {
            background-color: #333;
            color: white;
        }

        table.custom-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.custom-table tr:hover {
            background-color: #f1f1f1;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f7f7f7;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px 16px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #0097A7;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f0f0f0;
        }

        tfoot td {
            font-weight: bold;
            background-color: #e0f7fa;
        }

        .volver {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 16px;
            background-color: #0097A7;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .volver:hover {
            background-color: #007c91;
        }
    </style>
</head>

<body>
    <!-- Barra superior -->
    <div class="top-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="margin: 0;">Sistema de Inventario</h2>
            <button id="toggleSidebarBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <span id="fecha-actual" style="margin-left: 20px; font-size: 16px;"></span>
        <div class="admin-container">
            <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
    <!-- Contenedor principal -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Inventario</h3>

            <a href="dashboard2.php">
                <img src="../Image/hogarM.png" alt="Inicio"> Inicio
            </a>

            <a href="#" class="toggle-submenu">
                <i class="fa-solid fa-users"></i> Usuarios ⏷
            </a>

            <div class="submenu" id="submenu-usuarios" style="display: none;">
                <a href="AgregarUsuario.php">
                    <i class="fa-solid fa-user-plus"></i> Agregar Usuario
                </a>
                <a href="ListAdministrador.php">
                    <i class="fa-solid fa-user-tie"></i> Administradores
                </a>
                <a href="ListGeneral.php">
                    <i class="fa-solid fa-user-group"></i> Generales
                </a>

            </div>


            <a href="AgregarCat.php">
                <img src="../Image/factura.png" alt="Categorias"> Categorias
            </a>

            <a href="#" class="toggle-submenu2">
                <i class="fa-solid fa-truck"></i> Productos ⏷
            </a>


            <div class="submenu" id="submenu-productos" style="display: none;">
                <a href="ListProductos.php">
                    <i class="fa-solid fa-clipboard-list"></i> Lista de Productos
                </a>
                <a href="AgregarPro.php">
                    <i class="fa-solid fa-circle-plus"></i> Agregar Producto
                </a>
                <a href="RetirarPro.php">
                    <i class="fa-solid fa-cart-plus"></i> Retirar Productos
                </a>

            </div>


            <a href="Reportes.php">
                <img src="../Image/reporte.png" alt="Reporte"> Reportes
            </a>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <h1>Bienvenido al Sistema de Inventario</h1>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 30px; margin-top: 20px;">
                <div style="background-color: #4CAF50; color: white; padding: 20px; border-radius: 10px; width: 250px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center;">
                    <h3>General Cobro</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?= $totalCobro ?></p>
                    <i class="fas fa-users fa-2x"></i>
                </div>

                <div style="background-color: #FF9800; color: white; padding: 20px; border-radius: 10px; width: 250px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center;">
                    <h3>General Inventario</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?= $totalInventario ?></p>
                    <i class="fas fa-warehouse fa-2x"></i>
                </div>
            </div>

            <div class="container2">
                <h3>Productos con Bajo Stock</h3>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Producto</th>
                            <th>Stock</th>
                            <th>Categoría</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $registros = obtenerProductosConPocaCantidad($pdo);
                        foreach ($registros as $row) {
                            echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['nombre_producto']}</td>
                        <td>{$row['cantidad']}</td>
                        <td>{$row['categoria']}</td>
                    </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            </head>

            <body>
                <h3>Balance Mensual de Compras</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Producto</th>
                            <th>Cantidad Comprada</th>
                            <th>Gasto Total ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resultados) > 0): ?>
                            <?php foreach ($resultados as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['mes']) ?></td>
                                    <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                                    <td><?= htmlspecialchars($fila['total_cantidad']) ?></td>
                                    <td>$<?= number_format($fila['total_gasto'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No se encontraron compras registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Total General Gastado</td>
                            <td>$<?= number_format($total_general, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <canvas id="graficoRetiros" width="600" height="300"></canvas>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctx = document.getElementById('graficoRetiros').getContext('2d');
                    const grafico = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode($meses) ?>,
                            datasets: <?= json_encode($datasets) ?>
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Cantidad'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Mes'
                                    },
                                    stacked: true
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Productos Retirados por Usuario (Mes a Mes)'
                                }
                            }
                        }
                    });
                </script>


        </div>

    </div>


    <!-- Barra inferior -->
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleLink = document.querySelector(".toggle-submenu");
            const submenu = document.getElementById("submenu-usuarios");

            toggleLink.addEventListener("click", function(e) {
                e.preventDefault();
                submenu.style.display = submenu.style.display === "none" ? "flex" : "none";
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const toggles = document.querySelectorAll(".toggle-submenu2");

            toggles.forEach(function(toggle) {
                toggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    const nextSubmenu = toggle.nextElementSibling;
                    if (nextSubmenu && nextSubmenu.classList.contains("submenu")) {
                        nextSubmenu.style.display = nextSubmenu.style.display === "none" ? "flex" : "none";
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById("toggleSidebarBtn");
            const sidebar = document.querySelector(".sidebar");
            const content = document.querySelector(".content");

            toggleBtn.addEventListener("click", () => {
                sidebar.classList.toggle("hidden");
                content.classList.toggle("sidebar-hidden");
            });
        });

        function actualizarFecha() {
            const fechaElemento = document.getElementById("fecha-actual");
            const fecha = new Date();

            const opciones = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            fechaElemento.textContent = fecha.toLocaleDateString('es-ES', opciones);
        }

        document.addEventListener("DOMContentLoaded", function() {
            actualizarFecha(); // Mostrar la fecha al cargar la página

            // También puedes actualizar cada día a medianoche si mantienes la página abierta
            const ahora = new Date();
            const msHastaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1).getTime() - ahora.getTime();

            setTimeout(() => {
                actualizarFecha();
                setInterval(actualizarFecha, 24 * 60 * 60 * 1000); // Actualiza cada 24 horas
            }, msHastaMedianoche);
        });
    </script>
</body>

</html>