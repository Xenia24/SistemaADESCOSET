<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');

if (isset($_GET['fecha_inicio'], $_GET['fecha_fin']) && $_GET['fecha_inicio'] !== '' && $_GET['fecha_fin'] !== '') {
    // Filtro por rango de fechas específicas
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];

    $stmt = $pdo->prepare("
        SELECT 
            p.nombre_producto,
            SUM(c.cantidad_comprada) AS total_cantidad,
            SUM(c.precio * c.cantidad_comprada) AS total_compras,
            c.categoria
        FROM compras c
        INNER JOIN productos p ON c.producto_id = p.id
        WHERE c.fecha_compra BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY p.id
        ORDER BY total_compras DESC
    ");
    $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
    $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
    $stmt->execute();
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (isset($_GET['mes'], $_GET['anio']) && $_GET['mes'] !== '' && $_GET['anio'] !== '') {
    // Filtro por mes y año
    $mes = str_pad($_GET['mes'], 2, '0', STR_PAD_LEFT);
    $anio = $_GET['anio'];
    $anio_mes = "$anio-$mes";

    $stmt = $pdo->prepare("
        SELECT 
            p.nombre_producto,
            SUM(c.cantidad_comprada) AS total_cantidad,
            SUM(c.precio * c.cantidad_comprada) AS total_compras,
            c.categoria
        FROM compras c
        INNER JOIN productos p ON c.producto_id = p.id
        WHERE DATE_FORMAT(c.fecha_compra, '%Y-%m') = :anio_mes
        GROUP BY p.id
        ORDER BY total_compras DESC
    ");
    $stmt->bindParam(':anio_mes', $anio_mes, PDO::PARAM_STR);
    $stmt->execute();
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            margin-top: 50px;
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

        .card {
            width: 250px;
            background-color: #e0f7fa;
            border: 1px solid #b2ebf2;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-top: 140px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .card i {
            font-size: 40px;
            color: #007c91;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0;
            font-size: 18px;
            color: #007c91;
        }

        .card p {
            font-size: 14px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        thead {
            background-color: #0097A7;
            color: white;
        }

        thead th {
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border-right: 1px solid #007c91;
        }

        thead th:last-child {
            border-right: none;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: #f1fafa;
        }

        tbody td {
            padding: 12px 15px;
            text-align: center;
            font-size: 15px;
            color: #333;
        }

        .btn {
            background-color: #007c91;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #005f6b;
        }
    </style>
</head>

<>
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
            <h2>Reporte de Compras Mensuales por Usuario</h2>

            <form method="GET" style="margin-top: 10px;">
                <label for="fecha_inicio">Desde:</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">

                <label for="fecha_fin">Hasta:</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>">

                <label for="mes">Mes:</label>
                <select name="mes" id="mes">
                    <option value="">--</option>
                    <?php
                    $meses = [
                        1 => "Enero",
                        2 => "Febrero",
                        3 => "Marzo",
                        4 => "Abril",
                        5 => "Mayo",
                        6 => "Junio",
                        7 => "Julio",
                        8 => "Agosto",
                        9 => "Septiembre",
                        10 => "Octubre",
                        11 => "Noviembre",
                        12 => "Diciembre"
                    ];
                    for ($m = 1; $m <= 12; $m++) {
                        $selected = (isset($_GET['mes']) && $_GET['mes'] == $m) ? 'selected' : '';
                        echo "<option value='$m' $selected>" . $meses[$m] . "</option>";
                    }
                    ?>
                </select>

                <label for="anio">Año:</label>
                <input type="number" name="anio" id="anio" min="2000" max="2099" value="<?= htmlspecialchars($_GET['anio'] ?? date('Y')) ?>">

                <button type="submit" class="btn">Filtrar</button>
            </form>


            <?php if (isset($compras) && count($compras) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Total Cantidad</th>
                            <th>Total Compras</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td><?= htmlspecialchars($compra['nombre_producto']) ?></td>
                                <td><?= htmlspecialchars($compra['categoria']) ?></td>
                                <td><?= htmlspecialchars($compra['total_cantidad']) ?></td>
                                <td>$<?= number_format($compra['total_compras'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <div style="text-align: right; margin-top: 20px;">
                        <form method="GET" action="ReporteCMPDF.php" target="_blank" style="display:inline-block;">
                            <input type="hidden" name="fecha_inicio" value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">
                            <input type="hidden" name="fecha_fin" value="<?= htmlspecialchars($_GET['fecha_fin']    ?? '') ?>">
                            <input type="hidden" name="mes" value="<?= htmlspecialchars($_GET['mes']         ?? '') ?>">
                            <input type="hidden" name="anio" value="<?= htmlspecialchars($_GET['anio']        ?? '') ?>">
                            <button type="submit" class="btn">Exportar a PDF</button>
                        </form>
                    </div>
                </table>
            <?php elseif (isset($compras)): ?>
                <p>No hay resultados para este mes y año seleccionados.</p>
            <?php endif; ?>


        </div>
    </div>

    <!-- Barra inferior -->
    <div class="bottom-bar">
        Desarrolladores ©️ 2025 Xenia, Ivania, Erick
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

        function accionTarjeta(accion) {
            alert('Seleccionaste: ' + accion);
            // Puedes cambiar esto por una redirección:
            // if (accion === 'Agregar Producto') {
            //     window.location.href = 'AgregarPro.php';
            // }
        }
    </script>
    </body>

</html>