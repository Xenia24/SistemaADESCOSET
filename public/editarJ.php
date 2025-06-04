<?php
session_start();
include('../includes/db.php'); // Conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit();
}

// Inicializar variables para el modo de edición y para el SweetAlert posterior
$modo_edicion     = false;
$derechohabiente  = [
  'codigo' => '',
  'nombre_completo' => '',
  'direccion' => '',
  'telefono' => '',
  'identificacion' => '',
  'estado' => '',
  'tipo_derechohabiente' => 'natural'
];
$show_alert       = false;
$mensaje_exito    = '';

// Si viene el parámetro "codigo", cargamos el registro para editar
if (isset($_GET['codigo'])) {
  $codigo = $_GET['codigo'];
  $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE codigo = :codigo");
  $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
  $stmt->execute();
  $derechohabiente = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($derechohabiente) {
    $modo_edicion = true;
  } else {
    echo "<script>
                alert('¡No se encontró el derechohabiente!');
                window.location.href = 'juridica.php';
              </script>";
    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Recoger datos del formulario
  $codigo               = $_POST['codigo'];
  $nombre_completo      = $_POST['nombre'];
  $direccion            = $_POST['direccion'];
  $telefono             = $_POST['telefono'];
  $identificacion       = $_POST['identificacion'];
  $estado               = $_POST['estado'];
  $tipo_derechohabiente = $_POST['tipo_derecho'];

  try {
    if ($modo_edicion) {
      // Preparamos UPDATE
      $stmt = $pdo->prepare("
                UPDATE agregarderechohabiente SET 
                  nombre_completo      = :nombre_completo,
                  direccion            = :direccion,
                  telefono             = :telefono,
                  identificacion       = :identificacion,
                  estado               = :estado,
                  tipo_derechohabiente = :tipo_derechohabiente
                WHERE codigo = :codigo
            ");
      $mensaje_exito = "¡Registro actualizado exitosamente!";
    } else {
      // Preparamos INSERT
      $stmt = $pdo->prepare("
                INSERT INTO agregarderechohabiente 
                  (codigo, nombre_completo, identificacion, direccion, estado, telefono, tipo_derechohabiente)
                VALUES
                  (:codigo, :nombre_completo, :identificacion, :direccion, :estado, :telefono, :tipo_derechohabiente)
            ");
      $mensaje_exito = "¡Registro guardado exitosamente!";
    }
    // Vincular parámetros
    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':nombre_completo', $nombre_completo);
    $stmt->bindParam(':identificacion', $identificacion);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':tipo_derechohabiente', $tipo_derechohabiente);

    if ($stmt->execute()) {
      $show_alert = true;
    } else {
      echo "<script>
                    Swal.fire({
                      icon: 'error',
                      title: 'Error al guardar los cambios.',
                      confirmButtonColor: '#546E7A'
                    });
                  </script>";
    }
  } catch (PDOException $e) {
    $errorMsg = addslashes($e->getMessage());
    echo "<script>
                Swal.fire({
                  icon: 'error',
                  title: 'Error: $errorMsg',
                  confirmButtonColor: '#546E7A'
                });
              </script>";
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- SweetAlert2: CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      min-height: 100vh;
      background: #f4f4f4;
    }

    /* Top bar */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      color: #fff;
      z-index: 100;
    }

    .top-bar h2 {
      font-size: 18px;
    }

    .top-bar a {
      color: #fff;
      text-decoration: none;
    }

    /* Layout */
    .container {
      display: flex;
      flex: 1;
      padding-top: 60px;
      /* espacio para top-bar */
      padding-bottom: 60px;
      /* espacio para bottom-bar */
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .sidebar img.logo {
      width: 120px;
      margin: 0 auto 20px;
      border-radius: 10px;
    }

    .sidebar a,
    .sidebar .toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background .3s;
    }

    .sidebar a:hover,
    .sidebar .toggle:hover {
      background: #007c91;
    }

    .sidebar a img,
    .toggle img {
      width: 20px;
      height: 20px;
    }

    .submenu {
      display: none;
      flex-direction: column;
      gap: 5px;
      padding-left: 20px;
    }

    .submenu.show {
      display: flex;
    }

    .submenu a {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      transition: background .3s;
    }

    .submenu a:hover {
      background: rgba(255, 255, 255, 0.4);
    }

    /* Content: centra la tarjeta */
    .content {
      flex: 1;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px;
      margin: 0 20px;
      border-radius: 10px;
      overflow-y: auto;
    }

    /* Tarjeta + grid + borde del top-bar */
    .form-container {
      background: #F1F1F1;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      max-width: 700px;
      width: 100%;
      margin-top: 40px;
      border: 2px solid #0097A7;
    }

    .form-container h1 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      color: #0097A7;
      text-align: center;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      margin-bottom: 6px;
      font-weight: bold;
      color: #37474F;
    }

    .form-group input,
    .form-group select {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #0097A7;
      box-shadow: 0 0 0 3px rgba(0, 151, 167, 0.2);
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
      transition: transform .1s, box-shadow .1s;
    }

    .btn-save {
      background: #0097A7;
      color: #fff;
    }

    .btn-cancel {
      background: #B0BEC5;
      color: #37474F;
      /* Gris azulado neutro */
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    /* Bottom bar */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: #0097A7;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }

    @media (max-width:600px) {
      .content {
        align-items: flex-start;
        padding: 20px;
      }

      .form-container {
        padding: 20px;
      }

      .buttons {
        flex-direction: column-reverse;
      }

      .buttons .btn {
        width: 100%;
        margin-bottom: 10px;
      }
    }
  </style>
</head>

<body>

  <!-- Top bar -->
  <div class="top-bar">
    <h2><?= $modo_edicion ? 'Editar' : 'Agregar' ?> Derechohabiente</h2>
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
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
        <a href="juridica.php"><img src="../Image/grandes-almacenes.png" alt=""> Jurídica</a>
      </div>
      <a href="recibo.php"><img src="../Image/factura.png" alt=""> Recibo</a>
      <a href="listado.php"><img src="../Image/lista.png" alt=""> Listado</a>
      <div class="toggle" id="toggle-reporte">
        <img src="../Image/reporte.png" alt=""> Reporte ⏷
      </div>
      <div class="submenu" id="submenu-reporte">
        <a href="reporte.php?tipo=pagados">Recibos pagados</a>
        <a href="reporte.php?tipo=nopagados">No pagados</a>
        <a href="reporte.php?tipo=despues_vencimiento">Pagados tras venc.</a>
        <a href="reporte.php?tipo=mora">En mora</a>
        <a href="reporte.php?tipo=total">Total recaudado</a>
      </div>
    </div>

    <!-- Content -->
    <div class="content">
      <div class="form-container">
        <h1><?= $modo_edicion ? 'Editar Derechohabiente' : 'Agregar Derechohabiente' ?></h1>
        <form id="form-derecho" method="POST" action="">
          <div class="form-grid">
            <div class="form-group">
              <label for="codigo">Código</label>
              <input
                type="number"
                id="codigo"
                name="codigo"
                value="<?= htmlspecialchars($derechohabiente['codigo']) ?>"
                required
                <?= $modo_edicion ? 'readonly' : '' ?>
                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>
            <div class="form-group">
              <label for="nombre">Nombre Completo</label>
              <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?= htmlspecialchars($derechohabiente['nombre_completo']) ?>"
                required>
            </div>
            <div class="form-group">
              <label for="direccion">Dirección</label>
              <input
                type="text"
                id="direccion"
                name="direccion"
                value="<?= htmlspecialchars($derechohabiente['direccion']) ?>"
                required>
            </div>
            <div class="form-group">
              <label for="telefono">Teléfono</label>
              <input
                type="text"
                id="telefono"
                name="telefono"
                value="<?= htmlspecialchars($derechohabiente['telefono']) ?>"
                maxlength="8"
                required
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                inputmode="numeric"
                placeholder="Máx. 8 dígitos">
            </div>
            <div class="form-group">
              <label for="identificacion">Identificación</label>
              <input
                type="text"
                id="identificacion"
                name="identificacion"
                value="<?= htmlspecialchars($derechohabiente['identificacion']) ?>"
                maxlength="9"
                required
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                inputmode="numeric"
                placeholder="Máx. 9 dígitos">
            </div>
            <div class="form-group">
              <label for="estado">Estado</label>
              <select id="estado" name="estado" required>
                <option value="activo" <?= $derechohabiente['estado'] == 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $derechohabiente['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
              </select>
            </div>
            <div class="form-group">
              <label for="tipo_derecho">Tipo de derechohabiente</label>
              <select id="tipo_derecho" name="tipo_derecho" required>
                <option value="natural" <?= $derechohabiente['tipo_derechohabiente'] == 'natural' ? 'selected' : '' ?>>Natural</option>
                <option value="juridica" <?= $derechohabiente['tipo_derechohabiente'] == 'juridica' ? 'selected' : '' ?>>Jurídica</option>
              </select>
            </div>
          </div>
          <div class="buttons">
            <!-- Cancelar redirige a juridica.php -->
            <a href="juridica.php" class="btn btn-cancel">Cancelar</a>
            <!-- El botón Guardar/Actualizar dispara SweetAlert y envía el formulario si confirma -->
            <button type="button" id="btn-guardar" class="btn btn-save">
              <?= $modo_edicion ? 'Actualizar' : 'Guardar' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="bottom-bar">
    © 2025 Xenia, Ivania, Erick
  </div>

  <!-- Toggle submenú -->
  <script>
    document.querySelectorAll('.toggle').forEach(toggle => {
      toggle.addEventListener('click', () => {
        toggle.nextElementSibling.classList.toggle('show');
      });
    });
    document.getElementById('toggle-reporte').addEventListener('click', () => {
      document.getElementById('submenu-reporte').classList.toggle('show');
    });
  </script>

  <!-- SweetAlert2: confirmación antes de enviar el formulario -->
  <script>
    document.getElementById('btn-guardar').addEventListener('click', function() {
      Swal.fire({
        title: '¿Confirmar <?= $modo_edicion ? "actualización" : "guardado" ?>?',
        text: "Si acepta, se guardarán los datos en la base de datos.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, <?= $modo_edicion ? "actualizar" : "guardar" ?>',
        cancelButtonText: 'Cancelar',
        focusCancel: true,
        confirmButtonColor: '#0097A7',
        cancelButtonColor: '#546E7A'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('form-derecho').submit();
        }
      });
    });
  </script>

  <?php if ($show_alert): ?>
    <!-- SweetAlert2: éxito y redirección a juridica.php -->
    <script>
      Swal.fire({
        icon: 'success',
        title: '<?= $mensaje_exito ?>',
        confirmButtonColor: '#0097A7'
      }).then(() => {
        window.location.href = 'juridica.php';
      });
    </script>
  <?php endif; ?>
</body>

</html>