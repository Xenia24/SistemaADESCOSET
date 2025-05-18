<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Modo edición
$modo_edicion = false;
$derechohabiente = [
    'codigo'               => '',
    'nombre_completo'      => '',
    'direccion'            => '',
    'telefono'             => '',
    'identificacion'       => '',
    'estado'               => '',
    'tipo_derechohabiente' => 'juridica'
];

// Si viene un código por GET, cargar para edición
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $modo_edicion     = true;
        $derechohabiente  = $row;
    } else {
        echo "<script>
                alert('¡No se encontró el derechohabiente!');
                window.location.href='juridica.php';
              </script>";
        exit();
    }
}

// Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo               = $_POST['codigo'];
    $nombre_completo      = $_POST['nombre'];
    $direccion            = $_POST['direccion'];
    $telefono             = $_POST['telefono'];
    $identificacion       = $_POST['identificacion'];
    $estado               = $_POST['estado'];
    $tipo_derechohabiente = $_POST['tipo_derecho'];

    try {
        if ($modo_edicion) {
            $sql = "UPDATE agregarderechohabiente SET
                        nombre_completo      = :nombre_completo,
                        direccion            = :direccion,
                        telefono             = :telefono,
                        identificacion       = :identificacion,
                        estado               = :estado,
                        tipo_derechohabiente = :tipo_derechohabiente
                    WHERE codigo = :codigo";
            $mensaje = "¡Registro actualizado exitosamente!";
        } else {
            $sql = "INSERT INTO agregarderechohabiente
                        (codigo, nombre_completo, identificacion, direccion, estado, telefono, tipo_derechohabiente)
                    VALUES
                        (:codigo, :nombre_completo, :identificacion, :direccion, :estado, :telefono, :tipo_derechohabiente)";
            $mensaje = "¡Registro guardado exitosamente!";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':identificacion', $identificacion);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':tipo_derechohabiente', $tipo_derechohabiente);

        if ($stmt->execute()) {
            echo "<script>
                    alert('$mensaje');
                    window.location.href='juridica.php';
                  </script>";
            exit();
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente Jurídico</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
    body { display:flex; flex-direction:column; height:100vh; background:#f4f4f4; }
    .top-bar {
      position:fixed; top:0; left:0; right:0; height:60px;
      background:#0097A7; color:#fff;
      display:flex; justify-content:space-between; align-items:center;
      padding:0 20px; z-index:100;
    }
    .top-bar h2 { font-size:18px; }
    .top-bar a { color:#fff; text-decoration:underline; }
    .container { display:flex; flex:1; padding-top:60px; padding-bottom:60px; }
    .sidebar {
      width:250px; background:#0097A7; color:#fff;
      padding:20px; display:flex; flex-direction:column; gap:10px;
    }
    .sidebar img.logo { width:120px; margin:0 auto 20px; border-radius:10px; }
    .sidebar a, .sidebar .toggle {
      display:flex; align-items:center; gap:10px;
      padding:10px; color:#fff; text-decoration:none;
      border-radius:5px; transition:background .3s; cursor:pointer;
    }
    .sidebar a:hover, .sidebar .toggle:hover { background:#007c91; }
    .sidebar a img, .toggle img { width:20px; height:20px; }
    .submenu {
      display:none; flex-direction:column; gap:5px; padding-left:20px;
    }
    .submenu.show { display:flex; }
    .submenu a {
      display:flex; align-items:center; gap:8px;
      padding:8px; color:#fff; text-decoration:none;
      background:rgba(255,255,255,0.2); border-radius:5px; transition:background .3s;
    }
    .submenu a:hover { background:rgba(255,255,255,0.4); }
    .content {
      flex:1; background:#fff; padding:20px;
      border-radius:10px; margin:0 20px; overflow-y:auto;
    }
    .form-container { background:#F1F1F1; padding:20px; border-radius:10px; }
    .form-group { margin-bottom:15px; }
    label { font-weight:bold; display:block; margin-bottom:5px; }
    input, select {
      width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;
    }
    .buttons { display:flex; justify-content:space-between; margin-top:20px; }
    .btn { padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
    .btn-save { background:#0097A7; color:#fff; }
    .btn-cancel { background:red; color:#fff; }
    .bottom-bar {
      position:fixed; bottom:0; left:0; right:0; height:60px;
      background:#0097A7; color:#fff;
      display:flex; align-items:center; justify-content:center;
    }
  </style>
</head>
<body>
  <!-- Top bar -->
  <div class="top-bar">
    <h2><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente Jurídico</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo ADESCOSET">
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>
      <div class="toggle"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</div>
      <div class="submenu">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar</a>
        <a href="natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
        <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
      </div>
      <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
      <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
      <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
    </div>

    <!-- Content -->
    <div class="content">
      <h1><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente Jurídico</h1>
      <div class="form-container">
        <form method="POST">
          <div class="form-group">
            <label for="codigo">Código</label>
            <input type="number" id="codigo" name="codigo"
                   value="<?= htmlspecialchars($derechohabiente['codigo']) ?>"
                   required <?= $modo_edicion ? 'readonly' : '' ?>>
          </div>
          <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre"
                   value="<?= htmlspecialchars($derechohabiente['nombre_completo']) ?>" required>
          </div>
          <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion"
                   value="<?= htmlspecialchars($derechohabiente['direccion']) ?>" required>
          </div>
          <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono"
                   value="<?= htmlspecialchars($derechohabiente['telefono']) ?>" required>
          </div>
          <div class="form-group">
            <label for="identificacion">Identificación</label>
            <input type="text" id="identificacion" name="identificacion"
                   value="<?= htmlspecialchars($derechohabiente['identificacion']) ?>" required>
          </div>
          <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
              <option value="activo" <?= $derechohabiente['estado']=='activo' ? 'selected':'' ?>>Activo</option>
              <option value="inactivo" <?= $derechohabiente['estado']=='inactivo' ? 'selected':'' ?>>Inactivo</option>
            </select>
          </div>
          <div class="form-group">
            <label for="tipo_derecho">Tipo de derechohabiente</label>
            <select id="tipo_derecho" name="tipo_derecho" required>
              <option value="natural" <?= $derechohabiente['tipo_derechohabiente']=='natural'?'selected':'' ?>>Natural</option>
              <option value="juridica" <?= $derechohabiente['tipo_derechohabiente']=='juridica'?'selected':'' ?>>Jurídica</option>
            </select>
          </div>
          <div class="buttons">
            <a href="juridica.php" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-save"><?= $modo_edicion ? 'Actualizar' : 'Guardar' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Toggle submenú
    document.querySelector('.toggle').onclick = () => {
      document.querySelector('.submenu').classList.toggle('show');
    };
  </script>
</body>
</html>
