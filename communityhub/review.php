<?php
require __DIR__ . '/config.php';
require_login(); require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: admin.php'); exit; }

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$note = trim($_POST['note'] ?? '');

if ($id <= 0 || !in_array($action, ['approve','reject'], true)) {
  header('Location: admin.php'); exit;
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';
$stmt = $pdo->prepare("UPDATE announcements
  SET status=?, reviewed_by=?, reviewed_at=NOW(), review_note=?
  WHERE id=? AND status='pending'");
$stmt->execute([$newStatus, (int)$_SESSION['uid'], $note, $id]);

header('Location: admin.php?f=' . $newStatus);
