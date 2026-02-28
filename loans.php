<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit;
}

include "php/db.php";
$conn = CoreDB::connect();

$userId = $_SESSION['userId'];
$currentDate = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['returnLoan'])) {

    $loanId = $_POST['loan_id'];

    $loanResult = $conn->query("
        SELECT *
        FROM loans
        WHERE id=$loanId AND user_id=$userId AND returned=0
    ");

    $loanData = $loanResult->fetch_assoc();

    if (!$loanData) {

        $_SESSION['message'] = "Préstamo no encontrado o ya devuelto.";

    } else {

        $conn->query("
            UPDATE loans
            SET returned=1, return_date='$currentDate'
            WHERE id=$loanId
        ");

        $conn->query("
            UPDATE books
            SET available_copies = available_copies + 1
            WHERE id=" . $loanData['book_id']
        );

        if ($currentDate > $loanData['due_date']) {
            $penaltyUntil = date('Y-m-d', strtotime('+7 days'));
            $conn->query("
                UPDATE users
                SET penalty_until='$penaltyUntil'
                WHERE id=$userId
            ");
            $_SESSION['success'] = "Devuelto con retraso. Penalización hasta $penaltyUntil";
        } else {
            $_SESSION['success'] = "Libro devuelto correctamente ✅";
        }
    }

    header("Location: loans.php");
    exit;
}

$message = "";
$success = "";

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $_SESSION['message'] = "";
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    $_SESSION['success'] = "";
}


$loans = [];
$loansResult = $conn->query("
    SELECT loans.*, books.title
    FROM loans
    JOIN books ON books.id = loans.book_id
    WHERE loans.user_id=$userId
    ORDER BY loans.returned ASC, loans.loan_date DESC
");

while ($loanRow = $loansResult->fetch_assoc()) {
    $loans[] = $loanRow;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis préstamos</title>
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

<?php if ($message != ""): ?>
    <div class="container mt-3">
        <div class="alert alert-danger"><?= $message ?></div>
    </div>
<?php endif; ?>

<?php if ($success != ""): ?>
    <div class="container mt-3">
        <div class="alert alert-success"><?= $success ?></div>
    </div>
<?php endif; ?>

<div class="container mt-4">
    <h1 class="mb-3">Mis préstamos</h1>

    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th>Libro</th>
            <th>Fecha préstamo</th>
            <th>Fecha límite</th>
            <th>Devuelto</th>
            <th>Acción</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($loans as $loan): ?>
            <tr>
                <td><?= $loan['title'] ?></td>
                <td><?= $loan['loan_date'] ?></td>
                <td>
                    <?= $loan['due_date'] ?>
                    <?php if ($loan['returned'] == 0 && $currentDate > $loan['due_date']): ?>
                        <span class="badge text-bg-danger ms-2">Retraso</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $loan['returned'] == 1 ? $loan['return_date'] : "No" ?>
                </td>
                <td>
                    <?php if ($loan['returned'] == 0): ?>
                        <form method="POST" class="m-0">
                            <input type="hidden" name="returnLoan" value="1">
                            <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                            <button class="btn btn-success btn-sm">Devolver</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>

    </table>
</div>

</body>
</html>