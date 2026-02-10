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
$booksPHP = json_encode($books);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

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
        <div class="row" id="books">
        </div>
    </div>

    <script>
        let books = <?php echo json_encode($books); ?>;
        console.log(books);

        let booksRow = document.getElementById("books");

        for (let book of books) {
            let div = document.createElement("div");
            div.classList.add("col", "mb-4");

            let title = document.createElement("h3");
            title.textContent = book.title;
            div.appendChild(title);

            let author = document.createElement("p");
            author.textContent = book.author;
            div.appendChild(author);

            let category = document.createElement("p");
            category.textContent = book.category;
            div.appendChild(category);

            let available = document.createElement("p");
            if (book.available_copies > 0) {
                available.style.color = "green";
                available.textContent = `Disponible: ${book.available_copies} copias`;
            } else {
                available.style.color = "red";
                available.textContent = "No disponible";
            }
            div.appendChild(available);
            booksRow.appendChild(div);
        }
    </script>


</body>

</html>