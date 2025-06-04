<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];

    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    $stmt->bindParam(':id', $eliminar_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('¡Registro eliminado exitosamente!');</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro.');</script>";
    }
}

function obtenerProductos($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM productos");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cobro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* Reset general */
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

    /* Barra superior */
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
        transition: background-color 0.3s;
    }

    .admin-container a:hover {
        background-color: darkred;
    }

    /* Layout general */
    .container {
        display: flex;
        flex: 1;
    }

    /* Barra lateral */
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
        padding: 10px;
        border-radius: 5px;
        transition: background 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
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

    /* Contenido principal */
    .content {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-left: 270px;
            /* espacio para el sidebar */
            margin-top: 80px;
            /* espacio para la top-bar */
        }

    .search-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 15px;
    }

    .search-container input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        max-width: 300px;
        outline: none;
    }

    .search-container button {
        background-color: #0097A7;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        color: white;
        margin-left: 5px;
    }

    .search-container button i {
        font-size: 16px;
    }

    /* Tabla */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        border: 1px solid #ccc;
        text-align: center;
    }

    th {
        background-color: #5cb85c;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    /* Botones */
    .action-btn {
        border: none;
        padding: 8px 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-view {
        background-color: #5bc0de;
        color: white;
    }

    .btn-edit {
        background-color: #5cb85c;
        color: white;
    }

    .btn-delete {
        background-color: #d9534f;
        color: white;
    }

    .btn-confirm, .btn-cancel {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-confirm {
        background-color: #d9534f;
        color: white;
    }

    .btn-cancel {
        background-color: #5bc0de;
        color: white;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 10;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 10px;
        width: 400px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .modal-content h3 {
        margin-bottom: 10px;
        font-size: 20px;
    }

    .modal-content p {
        margin-top: 10px;
        font-size: 14px;
        color: #888;
    }

    .modal-icon {
        font-size: 50px;
        color: #f39c12;
        margin-bottom: 10px;
    }

    .modal-btns {
        margin-top: 20px;
        display: flex;
        justify-content: space-around;
    }

    /* Pie de página */
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
</style>

</head>
<body>
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

    <div class="container">
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

        <div class="content">
            <h2>Lista de Productos</h2>
           <div class="search-container">
                <input type="text" id="search" placeholder="Buscar Producto por Nombre o Categoria" onkeyup="buscarProducto()">
                <button onclick="buscarProducto()"><i class="fas fa-search"></i></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Categoria</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaProductos">
                    <?php
                    $registros = obtenerProductos($pdo);
                    foreach ($registros as $row) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['nombre_producto']}</td>
                            <td>{$row['cantidad']}</td>
                            <td>$ {$row['precio']}</td>
                            <td>{$row['categoria']}</td>                            
                            <td>
                                <a href='VentaPro.php?id={$row['id']}' class='action-btn btn-edit'><i class='fa-solid fa-cart-shopping'></i></a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>

    <script>
        

       

        function buscarProducto() {
            const input = document.getElementById('search').value.toLowerCase();
            const filas = document.querySelectorAll("#tablaProductos tr");

            filas.forEach(fila => {
                const nombre = fila.children[1]?.textContent.toLowerCase(); // columna del nombre
                const categoria = fila.children[4]?.textContent.toLowerCase(); // columna de categoría

                if (nombre.includes(input) || categoria.includes(input)) {
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            });
        }
        document.addEventListener("DOMContentLoaded", function() {
            const toggleLink = document.querySelector(".toggle-submenu");
            const submenu = document.getElementById("submenu-usuarios");

            toggleLink.addEventListener("click", function(e) {
                e.preventDefault();
                submenu.style.display = submenu.style.display === "none" ? "flex" : "none";
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
        const toggles = document.querySelectorAll(".toggle-submenu2");

        toggles.forEach(function (toggle) {
            toggle.addEventListener("click", function (e) {
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

    document.addEventListener("DOMContentLoaded", function () {
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
