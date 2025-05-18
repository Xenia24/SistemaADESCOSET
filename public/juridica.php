<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$rol_usuario = $_SESSION['tipo_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];
    $stmt = $pdo->prepare("DELETE FROM agregarderechohabiente WHERE codigo = :codigo");
    $stmt->bindParam(':codigo', $eliminar_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo "<script>alert('¡Registro eliminado exitosamente!');</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro.');</script>";
    }
}

function obtenerDerechohabientes($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM agregarderechohabiente WHERE tipo_derechohabiente = 'juridica'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Listado Jurídica – Sistema de Cobro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;}
    body{display:flex;flex-direction:column;height:100vh;background:#f4f4f4;}
    /* Top bar */
    .top-bar{position:fixed;top:0;left:0;right:0;height:60px;
      background:#0097A7;color:#fff;
      display:flex;justify-content:space-between;align-items:center;
      padding:0 20px;z-index:100;
    }
    .top-bar h2{font-size:18px;}
    .top-bar a{color:#fff;text-decoration:underline;}
    /* Layout */
    .container{display:flex;flex:1;padding-top:60px;padding-bottom:60px;}
    /* Sidebar */
    .sidebar{width:250px;background:#0097A7;color:#fff;
      padding:20px;display:flex;flex-direction:column;gap:10px;
    }
    .sidebar img.logo{width:120px;margin:0 auto 20px;border-radius:10px;}
    .sidebar h3{text-align:center;margin-bottom:15px;}
    .sidebar a, .sidebar .toggle{
      display:flex;align-items:center;gap:10px;
      padding:10px;color:#fff;text-decoration:none;
      border-radius:5px;cursor:pointer;
      transition:background .3s;
    }
    .sidebar a:hover, .sidebar .toggle:hover{background:#007c91;}
    .sidebar a img, .toggle img{width:20px;height:20px;}
    .submenu{display:none;flex-direction:column;gap:5px;padding-left:20px;}
    .submenu.show{display:flex;}
    .submenu a{
      display:flex;align-items:center;gap:8px;
      padding:8px;color:#fff;text-decoration:none;
      background:rgba(255,255,255,0.2);border-radius:5px;
      transition:background .3s;
    }
    .submenu a:hover{background:rgba(255,255,255,0.4);}
    .submenu a img{width:16px;height:16px;}
    /* Content */
    .content{flex:1;background:#fff;padding:20px;border-radius:10px;margin:0 20px;overflow-y:auto;}
    .search-container{display:flex;justify-content:flex-end;margin-bottom:15px;}
    .search-container input{padding:10px;border:1px solid #ccc;border-radius:5px;width:100%;max-width:300px;outline:none;}
    .search-container button{
      background:#0097A7;border:none;padding:10px;border-radius:5px;
      color:#fff;cursor:pointer;margin-left:5px;
    }
    .search-container button i{font-size:16px;}
    table{width:100%;border-collapse:collapse;}
    th,td{padding:12px;border:1px solid #ccc;text-align:center;}
    th{background:#5cb85c;color:#fff;}
    tr:nth-child(even){background:#f2f2f2;}
    .action-btn{border:none;padding:8px 10px;border-radius:5px;cursor:pointer;}
    .btn-view{background:#5bc0de;color:#fff;}
    .btn-edit{background:#5cb85c;color:#fff;}
    .btn-delete{background:#d9534f;color:#fff;}
    /* Bottom bar */
    .bottom-bar{
      position:fixed;bottom:0;left:0;right:0;height:60px;
      background:#0097A7;color:#fff;
      display:flex;align-items:center;justify-content:center;
    }
  </style>
</head>
<body>

  <!-- Top bar -->
  <div class="top-bar">
    <h2>Sistema de Cobro</h2>
    <div>
      <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?> 👤 |
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <!-- Main container -->
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
      <a href="reporte.php"><img src="../Image/reporte.png" alt=""> Reporte</a>
    </div>

    <!-- Content -->
    <div class="content">
      <h2>Lista de Derechohabiente: Jurídica</h2>
      <div class="search-container">
        <input type="text" id="search" placeholder="Buscar Derechohabiente" onkeyup="buscarDerechohabiente()">
        <button onclick="buscarDerechohabiente()"><i class="fas fa-search"></i></button>
      </div>
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Identificación</th><th>Dirección</th>
            <th>Teléfono</th><th>Tipo</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaDerechohabientes">
          <?php
          foreach (obtenerDerechohabientes($pdo) as $row) {
            echo "<tr>
              <td>{$row['codigo']}</td>
              <td>{$row['nombre_completo']}</td>
              <td>{$row['identificacion']}</td>
              <td>{$row['direccion']}</td>
              <td>{$row['telefono']}</td>
              <td>{$row['tipo_derechohabiente']}</td>
              <td>{$row['estado']}</td>
              <td>
                <a href='verJ.php?codigo={$row['codigo']}' class='action-btn btn-view'><i class='fas fa-eye'></i></a>";
            if ($rol_usuario === 'Administrador') {
              echo "<a href='editarJ.php?codigo={$row['codigo']}' class='action-btn btn-edit'><i class='fas fa-edit'></i></a>
                    <button class='action-btn btn-delete' onclick='confirmarEliminacion({$row['codigo']})'><i class='fas fa-trash-alt'></i></button>";
            }
            echo   "</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="bottom-bar">
    Desarrolladores © 2025 Xenia, Ivania, Erick
  </div>

  <script>
    // Toggle submenú
    document.querySelector('.toggle').addEventListener('click', ()=>{
      document.querySelector('.submenu').classList.toggle('show');
    });
    // Funciones de búsqueda y eliminación (se mantienen igual)
    let idEliminar = 0;
    function confirmarEliminacion(id){
      idEliminar = id;
      document.getElementById('modalEliminar').style.display = 'block';
    }
    function cerrarModal(){
      document.getElementById('modalEliminar').style.display = 'none';
    }
    function eliminarDerechohabiente(){
      if(!idEliminar) return;
      const form = document.createElement('form');
      form.method = 'POST'; form.action = '';
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = 'eliminar_id'; input.value = idEliminar;
      form.appendChild(input); document.body.appendChild(form); form.submit();
    }
    function buscarDerechohabiente(){
      let filtro = document.getElementById('search').value.toLowerCase();
      document.querySelectorAll('#tablaDerechohabientes tr').forEach(r=>{
        r.style.display = r.cells[1].innerText.toLowerCase().includes(filtro) ? '' : 'none';
      });
    }
  </script>

</body>
</html>
