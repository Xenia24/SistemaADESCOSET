<?php
session_start();
include('../includes/db.php');

// Guardar nuevo derechohabiente
if (isset($_POST['guardar'])) {
    $stmt = $pdo->prepare("
      INSERT INTO agregarderechohabiente
        (codigo,nombre_completo,identificacion,direccion,estado,telefono,tipo_derechohabiente)
      VALUES 
        (:codigo,:nombre,:identificacion,:direccion,:estado,:telefono,:tipo)
    ");
    $stmt->execute([
        ':codigo'         => $_POST['codigo'],
        ':nombre'         => $_POST['nombre'],
        ':identificacion' => $_POST['identificacion'],
        ':direccion'      => $_POST['direccion'],
        ':estado'         => $_POST['estado'],
        ':telefono'       => $_POST['telefono'],
        ':tipo'           => $_POST['tipo_derecho']
    ]);
    $success = "¡Registro guardado exitosamente!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Derechohabiente – Sistema de Cobro</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
    body { display:flex; flex-direction:column; height:100vh; background:#E0F7FA; }

    .top-bar {
      width:100%; height:60px; background:#0097A7;
      display:flex; justify-content:space-between; align-items:center;
      padding:0 20px; color:#fff;
    }
    .top-bar a { color:#fff; text-decoration:underline; }

    .container {
      display:flex; flex:1; padding-top:-1px;  /* ← espacio para la top-bar */
    }
    .sidebar {
      width:250px; background:#0097A7; color:#fff; padding:20px;
      display:flex; flex-direction:column; gap:10px;
    }
    .sidebar img.logo {
      width:120px; margin:0 auto 20px; border-radius:10px;
    }
    .sidebar a, .sidebar .toggle {
      color:#fff; text-decoration:none;
      display:flex; align-items:center; gap:10px;
      padding:10px; border-radius:5px; cursor:pointer;
      transition:background .3s;
    }
    .sidebar a:hover, .sidebar .toggle:hover { background:#007c91; }

    .sidebar a img, .toggle img { width:20px; height:20px; }

    .submenu {
      display:none;
      flex-direction:column;
      gap:5px;
      padding-left:20px;
      margin-top:-5px;          /* ajusta un poco hacia arriba */
    }
    .submenu.show { display:flex; }
    .submenu a {
      background:rgba(255,255,255,0.2);
      padding:8px; border-radius:5px;
      display:flex; align-items:center; gap:8px;
    }
    .submenu a:hover { background:rgba(255,255,255,0.4); }
    .submenu a img { width:16px; height:16px; }

    .content {
      flex:1; background:#fff; padding:20px;
      overflow-y:auto; margin-left:20px; border-radius:10px;
    }
    .form-container {
      background:#F1F1F1; padding:20px; border-radius:10px;
    }
    .form-group { margin-bottom:15px; }
    label { font-weight:bold; display:block; margin-bottom:5px; }
    input, select {
      width:100%; padding:8px; border:1px solid #ccc;
      border-radius:5px;
    }
    .buttons {
      display:flex; justify-content:flex-end; gap:10px;
      margin-top:20px;
    }
    .btn { padding:10px 20px; border:none; border-radius:5px; cursor:pointer;}
    .btn-save { background:#0097A7; color:#fff; }
    .btn-cancel { background:red; color:#fff; }

    .bottom-bar {
      width:100%; text-align:center; padding:10px;
      background:#0097A7; color:#fff;
    }
  </style>
</head>
<body>
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="container">
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" alt="Logo ADESCOSET" class="logo">

      <a href="dashboard.php">
        <img src="../Image/hogarM.png" alt=""> Inicio
      </a>

      <div class="toggle">
        <img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷
      </div>
      <div class="submenu">
        <a href="Agregarderecho.php">
          <img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente
        </a>
        <a href="Natural.php">
          <img src="../Image/usuario1.png" alt=""> Natural
        </a>
        <a href="juridica.php">
          <img src="../Image/grandes-almacenes.png" alt=""> Jurídica
        </a>
      </div>

      <a href="recibo.php">
        <img src="../Image/factura.png" alt=""> Recibo
      </a>
      <a href="listado.php">
        <img src="../Image/lista.png" alt=""> Listado
      </a>
      <a href="reporte.php">
        <img src="../Image/reporte.png" alt=""> Reporte
      </a>
    </div>

    <div class="content">
      <h1>Agregar Derechohabiente</h1>
      <?php if (isset($success)): ?>
        <p style="color:green"><?= $success ?></p>
      <?php endif; ?>

      <div class="form-container">
        <form method="POST">
          <div class="form-group">
            <label for="codigo">Código</label>
            <input type="number" id="codigo" name="codigo" required>
          </div>
          <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" required>
          </div>
          <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" required>
          </div>
          <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" required>
          </div>
          <div class="form-group">
            <label for="identificacion">Identificación</label>
            <input type="text" id="identificacion" name="identificacion" required>
          </div>
          <div class="form-group">
            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>
          <div class="form-group">
            <label for="tipo_derecho">Tipo de derechohabiente</label>
            <select id="tipo_derecho" name="tipo_derecho" required>
              <option value="natural">Natural</option>
              <option value="juridica">Jurídica</option>
            </select>
          </div>
          <div class="buttons">
            <button type="reset" class="btn btn-cancel">Cancelar</button>
            <button type="submit" name="guardar" class="btn btn-save">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>  

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    document.querySelector('.toggle').onclick = () => {
      document.querySelector('.submenu').classList.toggle('show');
    };
  </script>
</body>
</html>
