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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; flex-direction: column; height: 100vh; background-color: #f4f4f4; }
        .top-bar { width: 100%; height: 60px; background-color: #0097A7; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; color: white; }
        .top-bar h2 { font-size: 18px; }
        .admin-container { display: flex; align-items: center; gap: 10px; }
        .admin-container a { text-decoration: none; background-color: red; color: white; padding: 8px 12px; border-radius: 5px; transition: background-color 0.3s; }
        .admin-container a:hover { background-color: darkred; }
        .container { display: flex; flex: 1; }
        .sidebar { width: 250px; background-color: #0097A7; color: white; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
        .sidebar img.logo { width: 100px; margin: 0 auto 15px auto; display: block; border-radius: 10px; }
        .sidebar h3 { text-align: center; margin-bottom: 15px; }
        .sidebar a { text-decoration: none; color: white; padding: 10px; border-radius: 5px; transition: background 0.3s; display: flex; align-items: center; gap: 10px; }
        .sidebar a:hover { background-color: #007c91; }
        .sidebar a img { width: 20px; height: 20px; }
        .content { flex: 1; background-color: white; padding: 20px; border-radius: 10px; margin: 20px; }
        .form-container { background: #F1F1F1; padding: 20px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        .buttons { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-save { background-color: #0097A7; color: white; }
        .btn-cancel { background-color: red; color: white; }
        .bottom-bar { width: 100%; text-align: center; padding: 10px; background-color: #0097A7; color: white; }
    </style>
</head>
<body>
    <div class="top-bar">
        <h2><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Usuario General</h2>
         <div class="admin-container">
            <span class="icon"></span>
            <span><?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : 'Usuario'; ?> 👤</span><span></span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>


    <div class="container">
    <div class="sidebar">
            <img src="logoadesco.jpg" alt="Logo de ADESCOSET" class="logo">
            <h3>Sistema de Inventario</h3>
            <a href="dashboard2.php"><img src="../Image/hogarM.png" alt="Inicio"> Inicio</a>
            <a href=""><img src="../Image/avatar1.png" alt="Tipo"> Usuarios ⏷</a>
            <a href="AgregarUsuario.php"><img src="../Image/nuevo-usuario.png" alt="Agregar"> Agregar Usuario</a>
            <a href="ListAdministrador.php"><img src="../Image/usuario1.png" alt="Natural"> Administrador</a>
            <a href=""><img src="../Image/grandes-almacenes.png" alt="Jurídica"> Usuario General</a>
            <a href=""><img src="../Image/factura.png" alt="Recibo"> Categorias</a>
            <a href=""><img src="../Image/lista.png" alt="Listado"> Productos</a>
            <a href=""><img src="../Image/reporte.png" alt="Reporte"> Reportes</a>
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

    <div class="bottom-bar">
        Desarrolladores © 2025 Xenia, Ivania, Erick
    </div>
</body>
</html>
