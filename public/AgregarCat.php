<?php
session_start();
include('../includes/db.php'); // Incluye la conexión a la base de datos

//Verificar si el formulario de login ha sido enviado
if (isset($_POST['submit'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && md5($contrasena) == $usuario['contrasena']) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        header('Location: dashboard2.php');
        exit();
    } else {
        $error = "Llenar todos los campos";
    }
}

if (isset($_POST['guardar'])) {
    $nombre_categoria = $_POST['nombre_categoria'];

    if (isset($_POST['editar_id']) && !empty($_POST['editar_id'])) {
        // Modo edición
        $editar_id = $_POST['editar_id'];
        try {
            $stmt = $pdo->prepare("UPDATE categorias SET nombre_categoria = :nombre_categoria WHERE id = :id");
            $stmt->bindParam(':nombre_categoria', $nombre_categoria);
            $stmt->bindParam(':id', $editar_id);
            if ($stmt->execute()) {
                header("Location: AgregarCat.php?success=updated");
                exit();
            } else {
                $error_agregar = "Error al actualizar la categoría.";
            }
        } catch (PDOException $e) {
            $error_agregar = "Error: " . $e->getMessage();
        }
    } else {
        // Modo agregar
        try {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria) VALUES (:nombre_categoria)");
            $stmt->bindParam(':nombre_categoria', $nombre_categoria);

            if ($stmt->execute()) {
                header("Location: AgregarCat.php?success=created");
                exit();
            } else {
                $error_agregar = "Error al guardar la categoría.";
            }
        } catch (PDOException $e) {
            $error_agregar = "Error: " . $e->getMessage();
        }
    }
}



// Obtener las categorías existentes
$stmt = $pdo->query("SELECT * FROM categorias");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];

    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
    $stmt->bindParam(':id', $eliminar_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('¡Registro eliminado exitosamente!');</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro.');</script>";
    }
}

$nombre_categoria = "";
$editar_id = null;

if (isset($_GET['editar_id'])) {
    $editar_id = $_GET['editar_id'];
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
    $stmt->bindParam(':id', $editar_id, PDO::PARAM_INT);
    $stmt->execute();
    $categoria_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($categoria_a_editar) {
        $nombre_categoria = $categoria_a_editar['nombre_categoria'];
    }
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Sistema de Inventario</title>
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
            background-color: #E0F7FA;
        }

        /* Top bar */
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

        /* Admin container */
        .admin-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-container span {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
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

        .icon {
            font-size: 18px;
        }

        /* Layout container */
        .container {
            display: flex;
            flex: 1;
        }

        /* Sidebar */
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

        /* Submenu */
        .submenu {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding-left: 20px;
        }

        .submenu a {
            font-size: 14px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .submenu a img {
            width: 16px;
            height: 16px;
        }

        /* Main content */
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

        .content2 {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 80px;
            /* espacio para la top-bar */
        }

        /* Formulario */
        .form-container {
            background: #F1F1F1;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select,
        input,
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-save {
            background-color: #0097A7;
            color: white;
        }

        .btn-cancel {
            background-color: red;
            color: white;
        }

        .btn-cancel:hover {
            background-color: darkred;
        }

        .full-width {
            grid-column: span 2;
        }

        /* Estado toggle */
        .estado-container {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }

        .estado-container label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .estado-container input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkmark {
            height: 20px;
            width: 20px;
            border-radius: 5px;
            display: inline-block;
            border: 2px solid #ccc;
            background-color: #f4f4f4;
            transition: all 0.2s ease-in-out;
        }

        #activo:checked+.checkmark {
            background-color: green;
            border-color: green;
        }

        #inactivo:checked+.checkmark {
            background-color: red;
            border-color: red;
        }

        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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

        .action-btn {
            border: none;
            padding: 8px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-delete {
            background-color: #d9534f;
            color: white;
        }

        .btn-edit {
            background-color: #5cb85c;
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

        .btn-confirm,
        .btn-cancel {
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

        /* Search container */
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

        /* Mensajes */
        .success {
            color: green;
            margin-bottom: 10px;
        }

        .action-btn i {
            font-size: 16px;
            margin: 0 5px;
        }

        .btn-edit:hover {
            background-color: #449d44;
            /* verde más oscuro */
        }

        .btn-delete:hover {
            background-color: #c9302c;
            /* rojo más oscuro */
        }


        .error {
            color: red;
            margin-bottom: 10px;
        }

        /* Bottom bar */
        .bottom-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #0097A7;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
            }
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
        .botones-acciones {
            display: flex;
            justify-content: center;
            gap: 10px;
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

        <div class="admin-container">
            <span class="icon">🔄</span>
            <span>Admin name 👤</span>
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
                <img src="../Image/avatar1.png" alt="usuarios"> Usuarios ⏷
            </a>

            <div class="submenu" id="submenu-usuarios" style="display: none;">
                <a href="AgregarUsuario.php">
                    <img src="../Image/nuevo-usuario.png" alt="Agregar Usuario"> Agregar Usuario
                </a>
                <a href="ListAdministrador.php">
                    <img src="../Image/usuario1.png" alt="Administradores"> Administradores
                </a>
                <a href="ListGeneral.php">
                    <img src="../Image/grandes-almacenes.png" alt="Usuarios"> Usuarios
                </a>
            </div>


            <a href="AgregarCat.php">
                <img src="../Image/factura.png" alt="Categorias"> Categorias
            </a>

            <a href="#" class="toggle-submenu2">
                <img src="../Image/lista.png" alt="Listado"> Productos ⏷
            </a>


            <div class="submenu" id="submenu-productos" style="display: none;">
                <a href="ListProductos.php">
                    <img src="../Image/lista.png" alt="Listado"> Lista de Productos
                </a>
                <a href="AgregarPro.php">
                    <img src="../Image/lista.png" alt="Agregar Producto"> Agregar Producto
                </a>
                <a href="">
                    <img src="../Image/lista.png" alt="Listado"> Retirar Productos
                </a>

            </div>


            <a href="">
                <img src="../Image/reporte.png" alt="Reporte"> Reportes
            </a>
        </div>

        <!-- Contenido principal -->

        <div class="content">
            <h2>Agregar Categoría</h2>

            <?php if (isset($_GET['success'])): ?>
                <?php if ($_GET['success'] == 'updated'): ?>
                    <p class="success">¡Categoría actualizada exitosamente!</p>
                <?php elseif ($_GET['success'] == 'created'): ?>
                    <p class="success">¡Categoría guardada exitosamente!</p>
                <?php endif; ?>
            <?php elseif (isset($error_agregar)): ?>
                <p class="error"><?= $error_agregar ?></p>
            <?php endif; ?>


            <div class="form-container">
                <!-- <form method="POST" action=""> -->
                    <form method="POST" action="" id="formCategoria">
                    <div class="form-group">
                        <label for="nombre_categoria">Nombre de la Categoría</label>
                        <input type="text" id="nombre_categoria" name="nombre_categoria" value="<?= htmlspecialchars($nombre_categoria) ?>" required>
                        <?php if ($editar_id): ?>
                            <input type="hidden" name="editar_id" value="<?= $editar_id ?>">
                        <?php endif; ?>

                    </div>

                    <div class="buttons">
                        <button type="button" class="btn btn-cancel" onclick="window.location.href='AgregarCat.php';">Cancelar</button>
                        <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
                    </div>
                </form>
            </div>
            <div class="content2">
                <h2>Lista de Categorías</h2>

                <div class="search-container">
                    <input type="text" id="searchCategoria" placeholder="Buscar Categoría" onkeyup="buscarCategoria()">
                    <button onclick="buscarCategoria()"><i class="fas fa-search"></i></button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de la Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCategorias">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categorias");
                        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= $categoria['id'] ?></td>
                                <td><?= htmlspecialchars($categoria['nombre_categoria']) ?></td>
                                <td class="botones-acciones">
                                    <a href="?editar_id=<?= $categoria['id'] ?>" class="action-btn btn-edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button class="action-btn btn-delete" onclick="confirmarEliminacion(<?= $categoria['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <div id="modalEliminar" class="modal">
            <div class="modal-content">
                <i class="fas fa-exclamation-circle modal-icon"></i>
                <h3>¿Estás seguro de eliminar?</h3>
                <p>¡Esta acción no se puede deshacer!</p>
                <div class="modal-btns">
                    <button class="btn-confirm" onclick="eliminarDerechohabiente()">Sí, eliminar</button>
                    <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                </div>
            </div>
        </div>



        <script>
            let idEliminar = 0;

            function confirmarEliminacion(id) {
                idEliminar = id;
                document.getElementById('modalEliminar').style.display = 'block';
            }

            function cerrarModal() {
                document.getElementById('modalEliminar').style.display = 'none';
            }

            function eliminarDerechohabiente() {
                if (idEliminar !== 0) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'eliminar_id';
                    input.value = idEliminar;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function buscarDerechohabiente() {
                let input = document.getElementById("search").value.toLowerCase();
                let rows = document.querySelectorAll("#tablaDerechohabientes tr");
                rows.forEach(row => {
                    let nombre = row.cells[1].innerText.toLowerCase();
                    row.style.display = nombre.includes(input) ? "" : "none";
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

             document.getElementById('btnCancelar').addEventListener('click', function () {
        // Limpiar campo oculto si está presente
        const campoEditar = document.querySelector('input[name="editar_id"]');
        if (campoEditar) {
            campoEditar.value = '';
        }

        // Limpiar también el campo de texto manualmente (opcional si no funciona bien con reset)
        document.getElementById('nombre_categoria').value = '';
    });
        </script>



    </div>

    </div>


    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
</body>

</html>