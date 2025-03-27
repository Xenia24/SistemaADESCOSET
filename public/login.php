<?php
session_start();
include('../includes/db.php');

if (isset($_POST['submit'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && md5($contrasena) == $usuario['contrasena']) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f0f0;
        }
        .top-bar {
            width: 100%;
            height: 60px;
            background-color:rgb(71, 146, 167);
            position: fixed;
            top: 0;
            left: 0;
        }
        .container {
            text-align: center;
            width: 350px;
            background: #e0f7fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            position: relative;
        }
        .user-icon {
            width: 80px;
            height: 80px;
           
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px auto;
        }
        .user-icon img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .input-container {
            position: relative;
            margin: 15px auto;
            width: 100%;
        }
        .input-container input {
            width: calc(100% - 50px);
            padding: 10px 10px 10px 50px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
            margin: 0 auto;
        }
        .input-container img {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 25px;
            height: 25px;
        }
        button {
            width: 50%;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color:rgb(87, 100, 100);
            color: black;
            cursor: pointer;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="top-bar"></div>
    <div class="container">
        <div class="user-icon">
            <img src="avatar.png"   alt="Avatar">
        </div>
        <h2>INICIO DE SESIÓN</h2>
        <form method="POST" action="login.php">
            <div class="input-container">
                <img src="avatar.png" alt="Usuario">
                <input type="email" name="correo" placeholder="Correo" required>
            </div>
            <div class="input-container">
                <img src="candado.png" alt="Candado">
                <input type="password" name="contrasena" placeholder="Contraseña" required>
            </div>
            <button type="submit" name="submit">Entrar</button>
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        </form>
        <p>Desarrolladores<br>&copy; 2025 xenia,  Erick, Ivania</p>
    </div>
</body>
</html>
