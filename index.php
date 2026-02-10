<?php
session_start();
include "php/db.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $conn = CoreDB::connect();
    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$pass'");

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $_SESSION['userId'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin.php");
            exit;
        } else {
            header("Location: catalog.php");
            exit;
        }
    } else {
        $message = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card p-4 shadow">
                <h2 class="text-center mb-3">Iniciar sesión</h2>
                <form method="POST">
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña" required>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
                <?php if ($message): ?>
                    <p class="mt-3 text-center text-danger"><?= $message ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>