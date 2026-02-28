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
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3" id="books">
        </div>
    </div>

    <script>
        let books = <?php echo json_encode($books); ?>;
        let showBooks = document.getElementById("books");

        for (let book of books) {
            let col = document.createElement("div");
            col.classList.add("col");

            let card = document.createElement("div");
            card.classList.add("card", "h-100", "shadow-sm");

            let body = document.createElement("div");
            body.classList.add("card-body", "d-flex", "flex-column");

            let title = document.createElement("h5");
            title.textContent = book.title;
            title.classList.add("card-title");

            let author = document.createElement("p");
            author.textContent = `Autor: ${book.author}`;
            author.classList.add("card-text", "text-muted", "mb-1");

            let category = document.createElement("span");
            category.textContent = book.category;
            category.classList.add("badge", "text-bg-secondary", "align-self-start", "mb-3");

            let available = document.createElement("p");
            available.classList.add("card-text", "mb-3", "fw-semibold");

            if (book.available_copies > 0) {
                available.classList.add("text-success");
                available.textContent = `Disponible: ${book.available_copies} copias`;
            } else {
                available.classList.add("text-danger");
                available.textContent = "No disponible";
            }

            let spacer = document.createElement("div");
            spacer.classList.add("mt-auto");

            let button = document.createElement("button");
            button.textContent = "Pedir préstamo";
            button.classList.add("btn", "btn-primary", "w-100");

            if (book.available_copies <= 0) {
                button.disabled = true;
                button.classList.replace("btn-primary", "btn-secondary");
            }

            body.appendChild(title);
            body.appendChild(author);
            body.appendChild(category);
            body.appendChild(available);
            body.appendChild(spacer);
            body.appendChild(button);

            card.appendChild(body);
            col.appendChild(card);
            showBooks.appendChild(col);
        }
    </script>


</body>

</html>