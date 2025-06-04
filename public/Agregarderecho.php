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
  <!-- Estilos existentes -->
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
      background: #E0F7FA;
    }

    .top-bar {
      width: 100%;
      height: 60px;
      background: #0097A7;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      color: #fff;
    }

    .top-bar a {
      color: #fff;
      text-decoration: none;
    }

    .container {
      display: flex;
      flex: 1;
      overflow: hidden;
    }

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
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
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
      margin-top: -5px;
    }

    .submenu.show {
      display: flex;
    }

    .submenu a {
      background: rgba(255, 255, 255, 0.2);
      padding: 8px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .submenu a:hover {
      background: rgba(255, 255, 255, 0.4);
    }

    .submenu a img {
      width: 16px;
      height: 16px;
    }

    /* Ahora content es flex para centrar la tarjeta */
    .content {
      flex: 1;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      overflow-y: auto;
      margin-left: 20px;
      border-radius: 10px;
    }

    /* Diseño “tarjeta + grid” con borde del top-bar */
    .form-container {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      max-width: 700px;
      width: 100%;
      border: 2px solid #0097A7;
      margin: 0;
    }

    .form-container h1 {
      font-size: 1.6rem;
      margin-bottom: 20px;
      color: #0097A7;
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
      padding: 10px 12px;
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
      justify-content: flex-end;
      gap: 12px;
      margin-top: 30px;
    }

    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
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

    .bottom-bar {
      width: 100%;
      text-align: center;
      padding: 10px;
      background: #0097A7;
      color: #fff;
    }

    @media (max-width:600px) {
      .content {
        align-items: flex-start;
      }

      .form-container {
        padding: 20px;
      }

      .buttons {
        flex-direction: column-reverse;
      }

      .buttons .btn {
        width: 100%;
      }
    }
  </style>

  <!-- SweetAlert2: CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      <a href="dashboard.php"><img src="../Image/hogarM.png" alt=""> Inicio</a>
      <div class="toggle"><img src="../Image/avatar1.png" alt=""> Tipo de derechohabiente ⏷</div>
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

    <div class="content">
      <div class="form-container">
        <h1>Agregar Derechohabiente</h1>
        <?php if (isset($success)): ?>
          <p style="color:#2E7D32; margin-bottom:20px; font-weight:bold;"><?= $success ?></p>
        <?php endif; ?>
        <form id="form-derecho" method="POST">
          <div class="form-grid">
            <div class="form-group">
              <label for="codigo">Código</label>
              <input
                type="number"
                id="codigo"
                name="codigo"
                required
                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>
            <div class="form-group">
              <label for="nombre">Nombre Completo</label>
              <input
                type="text"
                id="nombre"
                name="nombre"
                required>
            </div>
            <div class="form-group">
              <label for="identificacion">Identificación</label>
              <input
                type="text"
                id="identificacion"
                name="identificacion"
                maxlength="9"
                required
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                inputmode="numeric"
                placeholder="Máx. 9 dígitos">
            </div>
            <div class="form-group">
              <label for="direccion">Dirección</label>
              <input
                type="text"
                id="direccion"
                name="direccion"
                required>
            </div>
            <div class="form-group">
              <label for="telefono">Teléfono</label>
              <input
                type="text"
                id="telefono"
                name="telefono"
                maxlength="8"
                required
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                inputmode="numeric"
                placeholder="Máx. 8 dígitos">
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
          </div>
          <div class="buttons">
            <button type="reset" class="btn btn-cancel">Cancelar</button>
            <button type="button" id="btn-guardar" class="btn btn-save">Guardar</button>
            <input type="hidden" name="guardar" value="1">
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <!-- Script de toggles de menú -->
  <script>
    document.querySelector('.toggle').onclick = () => {
      document.querySelector('.submenu').classList.toggle('show');
    };
    document.getElementById('toggle-reporte').addEventListener('click', () => {
      document.getElementById('submenu-reporte').classList.toggle('show');
    });
  </script>

  <!-- Script para SweetAlert2 -->
  <script>
    document.getElementById('btn-guardar').addEventListener('click', function() {
      Swal.fire({
        title: '¿Desea guardar este registro?',
        text: "Se confirmará el guardado en la base de datos.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
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
</body>

</html>