<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');

$show_alert    = false;
$mensaje_exito = "";

// Si vienen datos por POST, actualizamos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_recibo'])) {
    $stmt = $pdo->prepare("
        UPDATE recibos SET 
            fecha_emision     = :fecha_emision,
            fecha_vencimiento = :fecha_vencimiento,
            propietario       = :propietario,
            direccion         = :direccion,
            fecha_lectura     = :fecha_lectura,
            metros_cubicos    = :metros_cubicos,
            numero_suministro = :numero_suministro,
            numero_medidor    = :numero_medidor,
            lectura_anterior  = :lectura_anterior,
            lectura_actual    = :lectura_actual,
            meses_pendiente   = :meses_pendiente,
            multas            = :multas,
            total             = :total,
            estado_pago       = :estado_pago
        WHERE numero_recibo = :numero_recibo
    ");
    $stmt->execute([
        ':fecha_emision'     => $_POST['fecha_emision'],
        ':fecha_vencimiento' => $_POST['fecha_vencimiento'],
        ':propietario'       => $_POST['propietario'],
        ':direccion'         => $_POST['direccion'],
        ':fecha_lectura'     => $_POST['fecha_lectura'],
        ':metros_cubicos'    => $_POST['metros_cubicos'],
        ':numero_suministro' => $_POST['numero_suministro'],
        ':numero_medidor'    => $_POST['numero_medidor'],
        ':lectura_anterior'  => $_POST['lectura_anterior'],
        ':lectura_actual'    => $_POST['lectura_actual'],
        ':meses_pendiente'   => $_POST['meses_pendiente'],
        ':multas'            => $_POST['multas'],
        ':total'             => $_POST['total'],
        ':estado_pago'       => $_POST['estado_pago'],
        ':numero_recibo'     => $_POST['numero_recibo'],
    ]);

    $show_alert    = true;
    $mensaje_exito = "✅ Recibo actualizado correctamente";
}

// Si vienen por GET cargamos el recibo
if (!isset($_GET['numero'])) {
    die('No se proporcionó un número de recibo.');
}
$numero = $_GET['numero'];
$stmt   = $pdo->prepare("SELECT * FROM recibos WHERE numero_recibo = ?");
$stmt->execute([$numero]);
$recibo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recibo) {
    die('Recibo no encontrado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actualizar Recibo</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- SweetAlert2 CDN -->
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
      height: 100vh;
      background: #f4f4f4;
      overflow: hidden; /* Evita scrollbar en body principal */
    }

    /* Barra superior fija */
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
      z-index: 1000;
    }
    .top-bar a {
      color: #fff;
      text-decoration: none;
    }

    /* Barra inferior fija */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 40px;
      background: #0097A7;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    /* Layout general: espacio para barras fija arriba y abajo */
    .container {
      display: flex;
      flex: 1;
      margin-top: 60px;   /* Altura de .top-bar */
      margin-bottom: 40px; /* Altura de .bottom-bar */
      overflow: hidden;    /* El contenido interno manejará su propio scroll */
    }

    /* Sidebar fija dentro del container (no cambia) */
    .sidebar {
      width: 250px;
      background: #0097A7;
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      overflow: auto; /* Si se desborda el menú, que haga scroll sólo la sidebar */
    }
    .sidebar img.logo {
      width: 120px;
      margin: 0 auto 20px;
      border-radius: 10px;
    }
    .sidebar a,
    .sidebar .toggle {
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
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
      background: rgba(255, 255, 255, 0.2);
      padding: 8px;
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
    }
    .submenu a:hover {
      background: rgba(255, 255, 255, 0.4);
    }

    /* Contenido principal con scroll interno */
    .content {
      flex: 1;
      background: #e0e0e0;
      padding: 90px; /* Menos padding si lo prefieres */
      overflow-y: auto; /* Sólo el contenido central hace scroll */
    }

    /* Caja del formulario */
    .form-box {
      background: #f1f1f1;
      border: 2px solid #0097A7;
      border-radius: 8px;
      padding: 25px;
      max-width: 900px;
      margin: 0 auto 20px; /* Margen inferior para separación */
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .form-box h3 {
      text-align: center;
      margin-bottom: 20px;
      color: #0097A7;
    }
    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 15px;
    }
    .field {
      flex: 1;
      min-width: 200px;
    }
    .field label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #37474F;
    }
    .field input,
    .field select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .buttons {
      text-align: center;
      margin-top: 20px;
    }
    .btn {
      padding: 10px 25px;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      margin: 0 10px;
    }
    .btn-limpiar {
      background:rgb(165, 164, 164);
      color: #fff;
    }
    .btn-guardar {
      background: #0097A7;
      color: #fff;
    }

    @media (max-width: 600px) {
      .content {
        padding: 10px;
      }
      .form-box {
        padding: 20px;
      }
      .buttons .btn {
        width: 100%;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <!-- Barra superior fija -->
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario']) ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="container">
    <!-- Sidebar (puede hacer scroll si se desborda) -->
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo ADESCOSET">
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>
      <div class="toggle">
        <img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷
      </div>
      <div class="submenu">
        <a href="Agregarderecho.php"><img src="../Image/nuevo-usuario.png" alt=""> Agregar derechohabiente</a>
        <a href="Natural.php"><img src="../Image/usuario1.png" alt=""> Natural</a>
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

    <!-- Contenido central, con scroll propio -->
    <div class="content">
      <div class="form-box">
        <h3>Actualizar Recibo Nº <?= htmlspecialchars($recibo['numero_recibo']) ?></h3>
        <form id="form-recibo" method="POST" action="">
          <input type="hidden" name="numero_recibo" value="<?= $recibo['numero_recibo'] ?>">
          <div class="row">
            <div class="field">
              <label>Fecha Emisión</label>
              <input type="text" name="fecha_emision" class="datepicker" value="<?= $recibo['fecha_emision'] ?>" required>
            </div>
            <div class="field">
              <label>Fecha Vencimiento</label>
              <input type="text" name="fecha_vencimiento" class="datepicker" value="<?= $recibo['fecha_vencimiento'] ?>" required>
            </div>
          </div>
          <div class="row">
            <div class="field">
              <label>Propietario</label>
              <input type="text" name="propietario" value="<?= htmlspecialchars($recibo['propietario']) ?>" required>
            </div>
            <div class="field">
              <label>Dirección</label>
              <input type="text" name="direccion" value="<?= htmlspecialchars($recibo['direccion']) ?>" required>
            </div>
          </div>
          <div class="row">
            <div class="field">
              <label>Fecha Lectura</label>
              <input type="text" name="fecha_lectura" class="datepicker" value="<?= $recibo['fecha_lectura'] ?>" required>
            </div>
            <div class="field">
              <label>Metros Cúbicos</label>
              <input type="number" step="0.01" name="metros_cubicos" value="<?= $recibo['metros_cubicos'] ?>" required>
            </div>
            <div class="field">
              <label>N° Suministro</label>
              <input type="text" name="numero_suministro" value="<?= htmlspecialchars($recibo['numero_suministro']) ?>" required>
            </div>
            <div class="field">
              <label>N° Medidor</label>
              <input type="text" name="numero_medidor" value="<?= htmlspecialchars($recibo['numero_medidor']) ?>" required>
            </div>
          </div>
          <div class="row">
            <div class="field">
              <label>Lectura Anterior</label>
              <input type="number" step="0.01" name="lectura_anterior" value="<?= $recibo['lectura_anterior'] ?>" required>
            </div>
            <div class="field">
              <label>Lectura Actual</label>
              <input type="number" step="0.01" name="lectura_actual" value="<?= $recibo['lectura_actual'] ?>" required>
            </div>
            <div class="field">
              <label>Meses Pendientes</label>
              <input type="number" name="meses_pendiente" value="<?= $recibo['meses_pendiente'] ?>" required>
            </div>
            <div class="field">
              <label>Multas</label>
              <input type="number" step="0.01" name="multas" value="<?= $recibo['multas'] ?>" required>
            </div>
            <div class="field">
              <label>Total</label>
              <input type="number" step="0.01" name="total" value="<?= $recibo['total'] ?>" required>
            </div>
            <div class="field">
              <label>Estado</label>
              <select name="estado_pago" required>
                <option value="">Seleccione...</option>
                <option value="Pagado" <?= $recibo['estado_pago']==='Pagado'?'selected':'' ?>>Pagado</option>
                <option value="No pagado" <?= $recibo['estado_pago']==='No pagado'?'selected':'' ?>>No pagado</option>
                <option value="En mora" <?= $recibo['estado_pago']==='En mora'?'selected':'' ?>>En mora</option>
                <option value="Pagado fuera de fecha" <?= $recibo['estado_pago']==='Pagado fuera de fecha'?'selected':'' ?>>Pagado fuera de fecha</option>
              </select>
            </div>
          </div>
          <div class="buttons">
            <a href="listado.php" class="btn btn-limpiar"><i class="fas fa-times"></i> Cancelar</a>
            <button type="button" id="btn-guardar" class="btn btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Barra inferior fija -->
  <div class="bottom-bar">
    © 2025 Xenia, Ivania, Erick
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr(".datepicker", { dateFormat: "Y-m-d" });

    // Toggle de los submenús
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
        title: '¿Desea guardar los cambios?',
        text: "Si acepta, se actualizará este recibo en la base de datos.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar cambios',
        cancelButtonText: 'Cancelar',
        focusCancel: true,
        confirmButtonColor: '#0097A7',
        cancelButtonColor: '#546E7A'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('form-recibo').submit();
        }
      });
    });
  </script>

  <?php if ($show_alert): ?>
    <!-- SweetAlert2: éxito y redirección a listado.php -->
    <script>
      Swal.fire({
        icon: 'success',
        title: '<?= $mensaje_exito ?>',
        confirmButtonColor: '#0097A7'
      }).then(() => {
        window.location.href = 'listado.php';
      });
    </script>
  <?php endif; ?>
</body>
</html>
