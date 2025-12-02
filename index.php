<?php
// ENABLE ERROR DISPLAY
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// LOAD CONFIG + LOGIN
require 'config.php';
requireLogin();

// Logged in user
$user = getCurrentUser();


// ==================== NEW CODE FOR NOTIFICATION ====================

// Latest announcement ID
$sqlLatest = "SELECT MAX(id) AS latest_id FROM announcements";
$resLatest = $conn->query($sqlLatest);
$rowLatest = $resLatest->fetch_assoc();
$latestAnnouncementId = (int)$rowLatest['latest_id'];

// User ne last konsa announcement dekha?
$userLastSeen = isset($user['last_seen_announcement_id']) 
    ? (int)$user['last_seen_announcement_id'] 
    : 0;

// Unread announcements kitne?
$unreadCount = ($latestAnnouncementId > $userLastSeen)
    ? ($latestAnnouncementId - $userLastSeen)
    : 0;


// ==================== EVENT CREATION LOGIC ====================

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $category = $conn->real_escape_string($_POST['category'] ?? '');
    $date = $conn->real_escape_string($_POST['date'] ?? '');
    $time = $conn->real_escape_string($_POST['time'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);

    if ($title && $category && $date && $time && $location && $capacity > 0) {

        $imageUrl = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop';
        
        $sql = "INSERT INTO events (title, category, date, time, location, description, capacity, image_url) 
                VALUES ('$title', '$category', '$date', '$time', '$location', '$description', $capacity, '$imageUrl')";
        
        if ($conn->query($sql) === TRUE) {
            $message = 'Event created successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error creating event: ' . $conn->error;
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill all required fields!';
        $messageType = 'error';
    }
}

// Fetch recent events
$result = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 4");
$recentEvents = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Smart Community Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav>
        <div class="nav-container">
            <div class="logo">🚀 SmartHub</div>
            <ul>
                <li><a href="index.php" class="active">Create Event</a></li>
                <li><a href="events.php">Browse Events</a></li>

                <!-- Notification Badge -->
                <li>
                    <a href="announcements.php" style="position: relative;">
                        Announcements
                        <?php if ($unreadCount > 0): ?>
                            <span style="
                                position:absolute;
                                top:-8px;
                                right:-10px;
                                background:red;
                                color:white;
                                padding:2px 7px;
                                font-size:12px;
                                border-radius:50%;
                            ">
                                <?= $unreadCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <li><a href="register.php">My Registrations</a></li>

                <li style="margin-left:auto;">
                    <span>Welcome, <strong><?= htmlspecialchars($user['username']) ?></strong></span>
                    <a href="logout.php" class="btn btn-small">Logout</a>
                </li>
            </ul>
        </div>
    </nav>


    <div class="container">
        <div class="hero">
            <h1>Create Your Event</h1>
            <p>Organize amazing community experiences</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">

                <div class="form-group">
                    <label>Event Title *</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Tech">Technology</option>
                        <option value="Art">Art & Culture</option>
                        <option value="Sports">Sports</option>
                        <option value="Wellness">Wellness</option>
                        <option value="Education">Education</option>
                        <option value="Social">Social & Networking</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="date" required>
                </div>

                <div class="form-group">
                    <label>Time *</label>
                    <input type="time" name="time" required>
                </div>

                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" required>
                </div>

                <div class="form-group">
                    <label>Capacity *</label>
                    <input type="number" name="capacity" min="1" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>

                <button type="submit" class="btn">Create Event</button>
            </form>
        </div>

    </div>

</body>
</html>
