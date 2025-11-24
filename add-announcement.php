<?php
include 'config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: announcements.php');
    exit();
}

$user = getCurrentUser();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $category = $conn->real_escape_string($_POST['category'] ?? '');
    $content = $conn->real_escape_string($_POST['content'] ?? '');
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    $imageUrl = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop';
    $userId = $user['id'];

    if ($title && $category && $content) {
        $sql = "INSERT INTO announcements (title, category, content, image_url, created_by, is_pinned, status) 
                VALUES ('$title', '$category', '$content', '$imageUrl', $userId, $isPinned, 'published')";
        
        if ($conn->query($sql) === TRUE) {
            $message = 'Announcement published successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error publishing announcement: ' . $conn->error;
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill all required fields!';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Announcement - Smart Community Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="background">
        <div class="float-shape shape-1"></div>
        <div class="float-shape shape-2"></div>
        <div class="float-shape shape-3"></div>
    </div>

    <nav>
        <div class="nav-container">
            <div class="logo">🚀 SmartHub</div>
            <ul>
                <li><a href="index.php">Create Event</a></li>
                <li><a href="events.php">Browse Events</a></li>
                <li><a href="announcements.php" class="active">Announcements</a></li>
                <li><a href="register.php">My Registrations</a></li>
                <li><a href="add-announcement.php">Add Announcement</a></li>
                <li style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--secondary);">Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
                    <a href="logout.php" class="btn btn-small" style="margin: 0;">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Create New Announcement</h1>
            <p>Share important updates and news with your community</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="form-group">
                        <label for="title">Announcement Title *</label>
                        <input type="text" id="title" name="title" placeholder="e.g., Community Clean-up Day" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Announcement">Announcement</option>
                            <option value="Community">Community</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Event">Event</option>
                            <option value="Update">Update</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" placeholder="Write your announcement content here..." required style="min-height: 300px;"></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                    <input type="checkbox" id="is_pinned" name="is_pinned" style="width: auto; cursor: pointer;">
                    <label for="is_pinned" style="margin: 0; cursor: pointer;">Pin this announcement to top</label>
                </div>

                <button type="submit" class="btn">Publish Announcement</button>
            </form>
        </div>
    </div>
</body>
</html>