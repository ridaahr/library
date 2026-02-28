<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

include "php/db.php";
$conn = CoreDB::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['addUser'])) {
    $name  = $_POST['newName'];
    $email = $_POST['newUserEmail'];
    $pass  = $_POST['newPassword'];
    $role  = $_POST['newUserRole'];

    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$pass','$role')");
    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['deleteUser'])) {
    $userId = $_POST['user_id'];

    if ($userId != $_SESSION['userId']) {
      $conn->query("DELETE FROM loans WHERE user_id=$userId");
      $conn->query("DELETE FROM users WHERE id=$userId");
    }

    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['setPenalty'])) {
    $userId = $_POST['user_id'];
    $penaltyUntil = $_POST['penalty_until'];

    $conn->query("UPDATE users SET penalty_until='$penaltyUntil' WHERE id=$userId");
    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['clearPenalty'])) {
    $userId = $_POST['user_id'];

    $conn->query("UPDATE users SET penalty_until=NULL WHERE id=$userId");
    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['addBook'])) {
    $title  = $_POST['newBookTitle'];
    $author = $_POST['newBookAuthor'];
    $cat    = $_POST['newBookCategory'];
    $copies = $_POST['newBookCopies'];

    $conn->query("INSERT INTO books (title, author, category, available_copies, total_copies) VALUES ('$title','$author','$cat',$copies,$copies)");
    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['deleteBook'])) {
    $bookId = $_POST['book_id'];

    $conn->query("DELETE FROM loans WHERE book_id=$bookId");
    $conn->query("DELETE FROM books WHERE id=$bookId");

    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['updateCopies'])) {
    $bookId = $_POST['book_id'];
    $totalCopies = $_POST['total_copies'];

    $conn->query("UPDATE books SET total_copies=$totalCopies, available_copies=$totalCopies WHERE id=$bookId");

    header("Location: admin.php");
    exit;
  }

  if (isset($_POST['saveSettings'])) {
    $maxLoans = $_POST['max_loans'];
    $loanDays = $_POST['loan_days'];

    $conn->query("UPDATE settings SET max_loans=$maxLoans, loan_days=$loanDays WHERE id=1");

    header("Location: admin.php");
    exit;
  }
}

$users = [];
$usersRes = $conn->query("SELECT * FROM users ORDER BY role DESC, email ASC");
while ($row = $usersRes->fetch_assoc()) $users[] = $row;

$books = [];
$booksRes = $conn->query("SELECT * FROM books ORDER BY title ASC");
while ($row = $booksRes->fetch_assoc()) $books[] = $row;

$settingsRes = $conn->query("SELECT * FROM settings WHERE id=1");
$settings = $settingsRes->fetch_assoc();

$topBooks = [];
$topBooksRes = $conn->query("SELECT title, times_loaned FROM books ORDER BY times_loaned DESC LIMIT 5");
while ($row = $topBooksRes->fetch_assoc()) $topBooks[] = $row;

$topLateUsers = [];
$topLateUsersRes = $conn->query("
  SELECT users.email, COUNT(*) AS late_count
  FROM loans
  JOIN users ON users.id = loans.user_id
  WHERE loans.returned=1 AND loans.return_date > loans.due_date
  GROUP BY users.id
  ORDER BY late_count DESC
  LIMIT 5
");
while ($row = $topLateUsersRes->fetch_assoc()) $topLateUsers[] = $row;

$avgReturnRes = $conn->query("
  SELECT AVG(DATEDIFF(return_date, loan_date)) AS avg_days
  FROM loans
  WHERE returned=1 AND return_date IS NOT NULL
");
$avgReturn = $avgReturnRes->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <nav class="navbar navbar-expand bg-dark navbar-dark px-3">
    <a class="navbar-brand" href="#">Library</a>
    <div class="navbar-nav">
      <a class="nav-link" href="catalog.php">Catálogo</a>
      <a class="nav-link" href="loans.php">Mis préstamos</a>
      <a class="nav-link active" href="admin.php">Administrar</a>
      <a class="nav-link" href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

  <div class="container mt-4">

    <h3>Reglas</h3>
    <form method="POST" class="row g-2 mb-4">
      <input type="hidden" name="saveSettings">
      <div class="col-md-3">
        <input type="number" name="max_loans" class="form-control" value="<?= $settings['max_loans'] ?>" required>
      </div>
      <div class="col-md-3">
        <input type="number" name="loan_days" class="form-control" value="<?= $settings['loan_days'] ?>" required>
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>

    <h3>Usuarios</h3>
    <table class="table table-bordered bg-white">
      <thead>
        <tr>
          <th>Email</th>
          <th>Rol</th>
          <th>Penalización</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['email'] ?></td>
            <td><?= $user['role'] ?></td>
            <td><?= $user['penalty_until'] ? $user['penalty_until'] : '-' ?></td>
            <td>

              <form method="POST" class="d-inline">
                <input type="hidden" name="setPenalty">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <input type="date" name="penalty_until" required>
                <button class="btn btn-warning btn-sm">Penalizar</button>
              </form>

              <form method="POST" class="d-inline">
                <input type="hidden" name="clearPenalty">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <button class="btn btn-secondary btn-sm">Quitar</button>
              </form>

              <form method="POST" class="d-inline">
                <input type="hidden" name="deleteUser">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <button class="btn btn-danger btn-sm">Borrar</button>
              </form>

            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Libros</h3>
    <table class="table table-bordered bg-white">
      <thead>
        <tr>
          <th>Título</th>
          <th>Autor</th>
          <th>Categoría</th>
          <th>Total</th>
          <th>Disponibles</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($books as $book): ?>
          <tr>
            <td><?= $book['title'] ?></td>
            <td><?= $book['author'] ?></td>
            <td><?= $book['category'] ?></td>
            <td><?= $book['total_copies'] ?></td>
            <td><?= $book['available_copies'] ?></td>
            <td>

              <form method="POST" class="d-inline">
                <input type="hidden" name="updateCopies">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <input type="number" name="total_copies" required>
                <button class="btn btn-primary btn-sm">Actualizar</button>
              </form>

              <form method="POST" class="d-inline">
                <input type="hidden" name="deleteBook">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <button class="btn btn-danger btn-sm">Borrar</button>
              </form>

            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Añadir usuario</h3>
    <form method="POST" class="mb-4">
      <input type="hidden" name="addUser">
      <input type="text" name="newName" class="form-control mb-2" placeholder="Nombre" required>
      <input type="email" name="newUserEmail" class="form-control mb-2" placeholder="Email" required>
      <input type="password" name="newPassword" class="form-control mb-2" placeholder="Contraseña" required>
      <select name="newUserRole" class="form-select mb-2">
        <option value="user">Usuario</option>
        <option value="admin">Admin</option>
      </select>
      <button class="btn btn-primary">Añadir usuario</button>
    </form>

    <h3>Añadir libro</h3>
    <form method="POST" class="mb-4">
      <input type="hidden" name="addBook">
      <input type="text" name="newBookTitle" class="form-control mb-2" placeholder="Título" required>
      <input type="text" name="newBookAuthor" class="form-control mb-2" placeholder="Autor" required>
      <input type="text" name="newBookCategory" class="form-control mb-2" placeholder="Categoría" required>
      <input type="number" name="newBookCopies" class="form-control mb-2" placeholder="Copias" required>
      <button class="btn btn-primary">Añadir libro</button>
    </form>

    <h3>Estadísticas</h3>

    <p><strong>Tiempo medio devolución:</strong>
      <?= $avgReturn['avg_days'] ? round($avgReturn['avg_days'], 1) : 0 ?> días
    </p>

    <ul>
      <?php foreach ($topBooks as $row): ?>
        <li><?= $row['title'] ?> (<?= $row['times_loaned'] ?>)</li>
      <?php endforeach; ?>
    </ul>

    <ul>
      <?php foreach ($topLateUsers as $row): ?>
        <li><?= $row['email'] ?> (<?= $row['late_count'] ?> retrasos)</li>
      <?php endforeach; ?>
    </ul>

  </div>

</body>

</html>