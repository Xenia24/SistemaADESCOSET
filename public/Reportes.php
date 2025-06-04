<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];


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
            width: 230px;
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

        .card {
            background-color: #e0f7fa;
            border: 1px solid #ccc;
            padding: 20px;
            width: 220px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.3s;
            text-decoration: none;
            color: #333;
            margin-top: 140px;
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

            <h2 style="margin-bottom: 20px;">Reportes</h2>
            <h4 style="margin-bottom: 20px;">Bienvenido al área de reportes, aquí puedes ver los datos, generar y descargar la informacion en formato PDF.</h4>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <a href="ReporteGM.php" class="card">
                    <i class="fa-regular fa-file-pdf"></i>
                    <h3>Ver Informacion</h3>
                    <p>Productos gastados mensualmente por Usuario</p>
                </a>

                <a href="ReporteGA.php" class="card">
                    <i class="fa-regular fa-file-pdf"></i>
                    <h3>Ver Informacion</h3>
                    <p>Productos gastados anualmente por Usuario</p>
                </a>

                <a href="ReporteCM.php" class="card">
                    <i class="fa-regular fa-file-pdf"></i>
                    <h3>Ver Informacion</h3>
                    <p>Compras Mensuales</p>
                </a>
            </div>


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