<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit;
}

include "php/db.php";
$conn = CoreDB::connect();

$res = $conn->query("SELECT * FROM books");
$books = [];
while ($b = $res->fetch_assoc()) {
    $books[] = $b;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>MLibrary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand bg-dark navbar-dark px-3">
        <a class="navbar-brand" href="#">Library</a>
        <div class="navbar-nav">
            <a class="nav-link" href="catalog.php">Catálogo</a>
            <a class="nav-link" href="loans.php">Mis préstamos</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="admin.php">Administrar</a>
            <?php endif; ?>
            <a class="nav-link" href="logout.php">Cerrar sesión</a>
        </div>
    </nav>
    <div class="container mt-4">
        <h1 class="mb-4">Mis Préstamos</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Libro</th>
                    <th>Fecha préstamo</th>
                    <th>Fecha devolución</th>
                    <th>Penalización</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="loansList"></tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>