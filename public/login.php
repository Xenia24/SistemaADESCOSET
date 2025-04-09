<?php
session_start();
include('../includes/db.php');

// Login
if (isset($_POST['submit'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contraseña'];

    $stmt = $pdo->prepare("SELECT * FROM usuariosag WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contraseña'])) {
        // Si las contraseñas coinciden, guarda los datos en la sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        header('Location: opciones.php');
        exit();
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      margin: 0;
      background: url('../Image/fon1.png') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding-top: 80px;
      padding-bottom: 60px;
    }

    .top-bar {
      width: 100%;
      height: 60px;
      background-color: rgb(0, 144, 175);
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 18px;
      font-weight: 600;
      z-index: 1000;
    }

    .container {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 30px 25px;
      width: 330px;
      border: 1px solid #333;
      border-radius: 6px;
      box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2);
      text-align: center;
    }

    .user-icon {
      width: 90px;
      height: 90px;
      margin: 0 auto 15px;
    }

    .user-icon img {
      width: 100%;
      height: 100%;
    }

    h2 {
      margin-bottom: 20px;
      font-weight: 600;
    }

    .input-container {
      position: relative;
      margin-bottom: 18px;
    }

    .input-container input {
      width: 100%;
      padding: 10px 10px 10px 40px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .input-container img {
      position: absolute;
      top: 50%;
      left: 10px;
      width: 20px;
      height: 20px;
      transform: translateY(-50%);
    }

    button {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 4px;
      background-color: rgb(87, 100, 100);
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background-color: rgb(67, 80, 80);
    }

    .error {
      color: red;
      margin-top: 10px;
    }

    .footer-bar {
      width: 100%;
      height: 50px;
      background-color: rgb(0, 144, 175);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      position: fixed;
      bottom: 0;
      left: 0;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <div class="top-bar">

  </div>

  <div class="container">
    <div class="user-icon">
      <img src="avatar.png" alt="Icono usuario" />
    </div>
    <h2>INICIO DE SESIÓN</h2>
    <form method="POST" action="login.php">
      <div class="input-container">
        <img src="avatar.png" alt="Usuario">
        <input type="email" name="correo" placeholder="Correo" required />
      </div>
      <div class="input-container">
        <img src="candado.png" alt="Contraseña">
        <input type="password" name="contraseña" placeholder="Contraseña" required />
      </div>
      <button type="submit" name="submit">Entrar</button>
      <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    </form>
  </div>

  <div class="footer-bar">
    &copy; 2025 Xenia, Erick, Ivania — Todos los derechos reservados
  </div>

</body>
</html>
