<?php
include 'config.php';
requireLogin();

$announcementId = (int)($_GET['id'] ?? 0);

if (!$announcementId) {
    header('Location: announcements.php');
    exit();
}

// Check if user is admin or announcement creator
if (!isAdmin() && !isAnnouncementCreator($announcementId)) {
    header('Location: announcements.php');
    exit();
}

// Delete the announcement
$deleteSql = "DELETE FROM announcements WHERE id = $announcementId";

if ($conn->query($deleteSql) === TRUE) {
    header('Location: announcements.php?message=deleted');
} else {
    header('Location: announcements.php?message=error');
}
exit();
?>