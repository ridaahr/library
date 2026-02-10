<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

include "php/db.php";
$conn = CoreDB::connect();

$usersRes = $conn->query("SELECT * FROM users");
$users = [];
while ($u = $usersRes->fetch_assoc()) {
  $users[] = $u;
}

$booksRes = $conn->query("SELECT * FROM books");
$books = [];
while ($b = $booksRes->fetch_assoc()) {
  $books[] = $b;
}

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

  if (isset($_POST['addBook'])) {
    $title  = $_POST['newBookTitle'];
    $author = $_POST['newBookAuthor'];
    $cat    = $_POST['newBookCategory'];
    $copies = $_POST['newBookCopies'];
    $conn->query("INSERT INTO books (title, author, category, available_copies) VALUES ('$title','$author','$cat',$copies)");
    header("Location: admin.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Administración</title>
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
    <h1>Administración</h1>

    <h3>Usuarios</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Email</th>
          <th>Rol</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= $u['email'] ?></td>
            <td><?= $u['role'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Libros</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Título</th>
          <th>Autor</th>
          <th>Categoría</th>
          <th>Disponibles</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($books as $b): ?>
          <tr>
            <td><?= $b['title'] ?></td>
            <td><?= $b['author'] ?></td>
            <td><?= $b['category'] ?></td>
            <td><?= $b['available_copies'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Añadir usuario</h3>
    <form method="POST">
      <input type="hidden" name="addUser">
      <input type="text" name="newName" placeholder="Nombre" class="form-control mb-2" required>
      <input type="email" name="newUserEmail" placeholder="Email" class="form-control mb-2" required>
      <input type="password" name="newPassword" placeholder="Contraseña" class="form-control mb-2" required>
      <select name="newUserRole" class="form-select mb-2">
        <option value="user">Usuario normal</option>
        <option value="admin">Admin</option>
      </select>
      <button class="btn btn-primary" type="submit">Añadir usuario</button>
    </form>

    <h3>Añadir libro</h3>
    <form method="POST">
      <input type="hidden" name="addBook">
      <input type="text" name="newBookTitle" placeholder="Título" class="form-control mb-2" required>
      <input type="text" name="newBookAuthor" placeholder="Autor" class="form-control mb-2" required>
      <input type="text" name="newBookCategory" placeholder="Categoría" class="form-control mb-2" required>
      <input type="number" name="newBookCopies" placeholder="Copias disponibles" class="form-control mb-2" required>
      <button class="btn btn-primary" type="submit">Añadir libro</button>
    </form>
  </div>

</body>

</html>