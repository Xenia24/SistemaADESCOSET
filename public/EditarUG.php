<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se está editando un registro
$modo_edicion = false;
$Administrador = [
    'id' => '',
    'nombre_completo' => '',
    'correo' => '',
    'telefono' => '',
    'numero_dui' => '',
    'nombre_usuario' => '',
    'estado' => '',
    'tipo_usuario' => 'General',
];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM usuariosag WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $Administrador = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($Administrador) {
        $modo_edicion = true;
    } else {
        echo "<script>alert('¡No se encontró Usuario General!'); window.location.href='ListGeneral.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'];
    $nombre_completo = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $numero_dui = $_POST['numero_dui'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $estado = $_POST['estado'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Comprobar si es edición
    $stmt_check = $pdo->prepare("SELECT id FROM usuariosag WHERE id = :id");
    $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_check->execute();
    $modo_edicion = $stmt_check->fetch() ? true : false;

    try {
        if ($modo_edicion) {
            $stmt = $pdo->prepare("UPDATE usuariosag SET 
                                    nombre_completo = :nombre_completo,
                                    correo = :correo,
                                    telefono = :telefono,
                                    numero_dui = :numero_dui,
                                    nombre_usuario = :nombre_usuario,
                                    estado = :estado,
                                    tipo_usuario = :tipo_usuario
                                    WHERE id = :id");

            $stmt->bindParam(':id', $id);
            $mensaje_exito = "¡Registro actualizado exitosamente!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuariosag 
                                    ( nombre_completo, correo, telefono, numero_dui, nombre_usuario, estado, tipo_usuario)
                                    VALUES ( :nombre_completo, :correo, :telefono, :numero_dui, :nombre_usuario, :estado, :tipo_usuario)");
            $mensaje_exito = "¡Registro guardado exitosamente!";
        }

        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':numero_dui', $numero_dui);
        $stmt->bindParam(':nombre_usuario', $nombre_usuario);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);

        if ($stmt->execute()) {
            echo "<script>alert('$mensaje_exito'); window.location.href='ListGeneral.php';</script>";
        } else {
            echo "<script>alert('Error al guardar los cambios.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicion ? 'Editar' : 'Agregar' ?>Usuario General</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <style>
    /* === Reset y base === */
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

    label {
        font-weight: bold;
        display: block;
    }

    input,
    select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    /* === Barra superior === */
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

    /* === Contenedor principal === */
    .container {
        display: flex;
        flex: 1;
    }

    /* === Sidebar === */
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
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.3s;
    }

    .sidebar a:hover {
        background-color: #007c91;
    }

    .sidebar a img {
        width: 20px;
        height: 20px;
    }
    /* --- */

    .sidebar a:hover {
            background-color: #007c91;
        }

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

    /* === Formularios === */
    .form-container {
        background: #F1F1F1;
        padding: 20px;
        border-radius: 10px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
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

    /* === Barra inferior === */
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
<<<<<<< HEAD
        <div style="display: flex; align-items: center; gap: 10px;">
            <h2 style="margin: 0;">Sistema de Inventario</h2>
            <button id="toggleSidebarBtn" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="admin-container">
            <span class="icon">🔄</span>
            <span>Admin name 👤</span>
=======
        <h2><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Usuario General</h2>
         <div class="admin-container">
            <span class="icon"></span>
            <span><?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : 'Usuario'; ?> 👤</span><span></span>
>>>>>>> 919dddeba74d51ea28d15252ec51fbbb73acb2a4
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

        <div class="content">
            <h1><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Usuario General</h1>
            <div class="form-container">
                <form method="POST" action="">
                    <!-- <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="number" id="codigo" name="codigo" value="<?= htmlspecialchars($derechohabiente['codigo']) ?>" required <?= $modo_edicion ? 'readonly' : '' ?>>
                    </div> -->
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" value="<?= htmlspecialchars($Administrador['nombre_completo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="correo">Dirección</label>
                        <input type="text" id="correo" name="correo" value="<?= htmlspecialchars($Administrador['correo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($Administrador['telefono']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="numero_dui">Numero de Dui</label>
                        <input type="text" id="numero_dui" name="numero_dui" value="<?= htmlspecialchars($Administrador['numero_dui']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?= htmlspecialchars($Administrador['nombre_usuario']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="activo" <?= $Administrador['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $Administrador['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo de Usuario</label>
                        <select id="tipo_usuario" name="tipo_usuario" required>
                            <option value="Administrador" <?= $Administrador['tipo_usuario'] == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                            <option value="General" <?= $Administrador['tipo_usuario'] == 'General' ? 'selected' : '' ?>>General</option>
                        </select>
                    </div>
                    <div class="buttons">
                        <a href="ListGeneral.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" class="btn btn-save"><?= $modo_edicion ? 'Actualizar' : 'Guardar' ?></button>
                    </div>
                </form>
            </div>
        </div>
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
    </script>
    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
</body>
</html>
