<?php
require __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* Delete */
if (isset($_GET['delete'], $_GET['id']) && $_GET['delete'] === '1') {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
  $stmt->execute([$id]);
  header('Location: index.php');
  exit;
}

/* Create / Update */
if ($method === 'POST') {
  $full = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $id    = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  if ($full === '' || $email === '') {
    http_response_code(400);
    exit('Missing required fields.');
  }

  if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE contacts SET full_name=?, email=? WHERE id=?");
    $stmt->execute([$full, $email, $id]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO contacts (full_name, email) VALUES (?, ?)");
    $stmt->execute([$full, $email]);
  }
  header('Location: index.php');
  exit;
}

header('Location: index.php');
