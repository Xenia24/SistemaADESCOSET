<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');

// Si vienen datos por POST, actualizamos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_recibo'])) {
    $stmt = $pdo->prepare("
        UPDATE recibos SET 
            fecha_emision   = :fecha_emision,
            fecha_vencimiento = :fecha_vencimiento,
            propietario      = :propietario,
            direccion        = :direccion,
            fecha_lectura    = :fecha_lectura,
            metros_cubicos   = :metros_cubicos,
            numero_suministro= :numero_suministro,
            numero_medidor   = :numero_medidor,
            lectura_anterior = :lectura_anterior,
            lectura_actual   = :lectura_actual,
            meses_pendiente  = :meses_pendiente,
            multas           = :multas,
            total            = :total,
            estado_pago      = :estado_pago
        WHERE numero_recibo = :numero_recibo
    ");
    $stmt->execute([
        ':fecha_emision'    => $_POST['fecha_emision'],
        ':fecha_vencimiento'=> $_POST['fecha_vencimiento'],
        ':propietario'      => $_POST['propietario'],
        ':direccion'        => $_POST['direccion'],
        ':fecha_lectura'    => $_POST['fecha_lectura'],
        ':metros_cubicos'   => $_POST['metros_cubicos'],
        ':numero_suministro'=> $_POST['numero_suministro'],
        ':numero_medidor'   => $_POST['numero_medidor'],
        ':lectura_anterior' => $_POST['lectura_anterior'],
        ':lectura_actual'   => $_POST['lectura_actual'],
        ':meses_pendiente'  => $_POST['meses_pendiente'],
        ':multas'           => $_POST['multas'],
        ':total'            => $_POST['total'],
        ':estado_pago'      => $_POST['estado_pago'],
        ':numero_recibo'    => $_POST['numero_recibo'],
    ]);
    echo "<script>
            alert('✅ Recibo actualizado correctamente');
            window.location.href='listado.php';
          </script>";
    exit();
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
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
    body { display:flex; flex-direction:column; height:100vh; background:#f4f4f4; }
    .top-bar {
      width:100%; height:60px; background:#0097A7;
      display:flex; justify-content:space-between; align-items:center;
      padding:0 20px; color:#fff;
    }
    .top-bar a { color:#fff; text-decoration:underline; }
    .container { display:flex; flex:1; padding-top:-1px; }
    .sidebar {
          width:250px; background:#0097A7; color:#fff; padding:20px;
      display:flex; flex-direction:column; gap:10px;
    }
    .sidebar img.logo { width:120px; margin:0 auto 20px; border-radius:10px; }
    .sidebar a, .sidebar .toggle {
      color:#fff; text-decoration:none; display:flex; align-items:center;
      gap:10px; padding:10px; border-radius:5px; cursor:pointer;
      transition:background .3s;
    }
    .sidebar a:hover, .sidebar .toggle:hover { background:#007c91; }
    .submenu {
      display:none; flex-direction:column; gap:5px; padding-left:20px;
    }
    .submenu.show { display:flex; }
    .submenu a {
      background:rgba(255,255,255,0.2); padding:8px; border-radius:5px;
    }
    .submenu a:hover { background:rgba(255,255,255,0.4); }

    .content {
      flex:1; overflow-y:auto; padding:20px;
    }

    .form-box {
      background:#fff; border:1px solid #ccc; border-radius:8px;
      padding:25px; max-width:900px; margin:0 auto;
      box-shadow:0 2px 5px rgba(0,0,0,0.1);
    }
    .form-box h3 { text-align:center; margin-bottom:20px; color:#0097A7; }

    .row { display:flex; flex-wrap:wrap; gap:15px; margin-bottom:15px; }
    .field { flex:1; min-width:200px; }
    .field label { display:block; margin-bottom:5px; font-weight:bold; }
    .field input, .field select {
      width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;
    }
    .buttons { text-align:center; margin-top:20px; }
    .btn { padding:10px 25px; border:none; border-radius:5px; font-weight:bold; cursor:pointer; margin:0 10px; }
    .btn-limpiar { background:#f08080; color:#fff; }
    .btn-guardar  { background:#0097A7; color:#fff; }

    .bottom-bar {
      width:100%; height:40px; background:#0097A7; color:#fff;
      display:flex; align-items:center; justify-content:center;
    }
  </style>
</head>
<body>
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario']) ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>
<div class="container">
    <div class="sidebar">
      <img src="../Image/logoadesco.jpg" class="logo" alt="Logo">
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
      <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
    </div>


    <div class="content">
      <div class="form-box">
        <h3>Actualizar Recibo Nº <?= htmlspecialchars($recibo['numero_recibo']) ?></h3>
        <form method="POST">
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
            <div class="field">
              <label>Lectura Anterior</label>
              <input type="number" step="0.01" name="lectura_anterior" value="<?= $recibo['lectura_anterior'] ?>" required>
            </div>
            <div class="field">
              <label>Lectura Actual</label>
              <input type="number" step="0.01" name="lectura_actual" value="<?= $recibo['lectura_actual'] ?>" required>
            </div>
          </div>
          <div class="row">
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
            <button type="submit" class="btn btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr(".datepicker",{ dateFormat:"Y-m-d" });
    document.querySelector('.toggle').onclick = ()=>{
      document.querySelector('.submenu').classList.toggle('show');
    };
  </script>
</body>
</html>
