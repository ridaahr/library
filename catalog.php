<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit;
}

include "php/db.php";
$conn = CoreDB::connect();

$settingsResult = $conn->query("SELECT * FROM settings WHERE id=1");
$settings = $settingsResult->fetch_assoc();

$res = $conn->query("SELECT * FROM books");
$books = [];
while ($b = $res->fetch_assoc()) {
    $books[] = $b;
}
$booksPHP = json_encode($books);

$message = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loanBook'])) {

    $bookId = $_POST['book_id'];
    $userId = $_SESSION['userId'];
    $todayDate = date('Y-m-d');

    $userResult = $conn->query("SELECT penalty_until FROM users WHERE id=$userId");
    $userData = $userResult->fetch_assoc();

    if ($userData['penalty_until'] && $userData['penalty_until'] >= $todayDate) {

        $message = "Tienes una penalización activa hasta " . $userData['penalty_until'];
    } else {

        $activeLoansResult = $conn->query("
            SELECT COUNT(*) AS total_active_loans
            FROM loans
            WHERE user_id=$userId AND returned=0
        ");

        $activeLoans = $activeLoansResult->fetch_assoc();

        if ($activeLoans['total_active_loans'] >= $settings['max_loans']) {

            $message = "Ya tienes 3 préstamos activos.";
        } else {

            $bookResult = $conn->query("
                SELECT available_copies
                FROM books
                WHERE id=$bookId
            ");

            $bookData = $bookResult->fetch_assoc();

            if ($bookData['available_copies'] <= 0) {

                $message = "No hay copias disponibles.";
            } else {

                $loanDate = $todayDate;
                $dueDate = date('Y-m-d', strtotime('+' . $settings['loan_days'] . ' days'));

                $conn->query("
                    INSERT INTO loans
                    (user_id, book_id, loan_date, due_date, return_date, returned)
                    VALUES
                    ($userId, $bookId, '$loanDate', '$dueDate', NULL, 0)
                ");

                $conn->query("
                    UPDATE books
                    SET available_copies = available_copies - 1,
                        times_loaned = times_loaned + 1
                    WHERE id=$bookId
                ");

                $success = "Préstamo realizado. Devuelve antes de $dueDate";
            }
        }
    }

    $_SESSION['message'] = $message;
    $_SESSION['success'] = $success;

    header("Location: catalog.php");
    exit;
}
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

    <?php
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $_SESSION['message'] = "";
    }

    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        $_SESSION['success'] = "";
    }
    ?>

    <?php if ($message != ""): ?>
        <div class="container mt-3">
            <div class="alert alert-danger">
                <?= $message ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="container mt-3">
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        </div>
    <?php endif; ?>

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

            let loanForm = document.createElement("form");
            loanForm.method = "POST";

            let loanFlag = document.createElement("input");
            loanFlag.type = "hidden";
            loanFlag.name = "loanBook";
            loanFlag.value = "1";

            let bookIdInput = document.createElement("input");
            bookIdInput.type = "hidden";
            bookIdInput.name = "book_id";
            bookIdInput.value = book.id;

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

            button.type = "submit";

            loanForm.appendChild(loanFlag);
            loanForm.appendChild(bookIdInput);
            loanForm.appendChild(button);

            spacer.appendChild(loanForm);

            card.appendChild(body);
            col.appendChild(card);
            showBooks.appendChild(col);
        }
    </script>


</body>

</html>