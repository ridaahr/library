<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit;
}

include "php/db.php";
$conn = CoreDB::connect();

if (isset($_POST['book_id'])) {
    $_SESSION['bookId'] = (int)$_POST['book_id'];
}

if (!isset($_SESSION['bookId'])) {
    echo "No se ha seleccionado ningún libro.";
    exit;
}

$bookId = $_SESSION['bookId'];

$res = $conn->query("SELECT * FROM books WHERE id=$bookId");
if ($res->num_rows === 0) {
    echo "Libro no encontrado.";
    exit;
}

$book = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= ($book['title']) ?></title>
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
        <a href="catalog.php" class="btn btn-secondary mb-3">Volver al catálogo</a>
        <script>
            let div = document.createElement("div");
            let book = <?php echo json_encode($book) ?>;
            console.log(book);
            title = document.createElement("h3");
            title.textContent = book.title;
            div.appendChild(title);
            author = document.createElement("p");
            author.textContent = book.author;
            div.appendChild(author);
            category = document.createElement("p");
            category.textContent = book.category;
            div.appendChild(category);
            available = document.createElement("p");
            if (book.available_copies > 0) {
                available.style.color = "green";
                available.textContent = `Disponible: ${book.available_copies} copias`;
            } else {
                available.style.color = "red";
                available.textContent = "No disponible";
            }
            div.appendChild(available);
            div.classList.add("container")
            document.body.appendChild(div);
        </script>

    </div>
</body>

</html>