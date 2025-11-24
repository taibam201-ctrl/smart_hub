<?php
include 'config.php';
requireLogin();

$user = getCurrentUser();
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$sortBy = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

$sql = "SELECT * FROM events WHERE 1=1";

if ($searchTerm) {
    $sql .= " AND (title LIKE '%$searchTerm%' OR location LIKE '%$searchTerm%' OR description LIKE '%$searchTerm%')";
}

if ($categoryFilter) {
    $sql .= " AND category = '$categoryFilter'";
}

if ($sortBy === 'oldest') {
    $sql .= " ORDER BY date ASC";
} elseif ($sortBy === 'popular') {
    $sql .= " ORDER BY registered DESC";
} else {
    $sql .= " ORDER BY date DESC";
}

$result = $conn->query($sql);
$events = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events - Smart Community Hub</title>
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
                <li><a href="events.php" class="active">Browse Events</a></li>
                <li><a href="announcements.php">Announcements</a></li>
                <li><a href="register.php">My Registrations</a></li>
                <li style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--secondary);">Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
                    <a href="logout.php" class="btn btn-small" style="margin: 0;">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Discover Amazing Events</h1>
            <p>Find and join incredible community events. Learn something new, meet amazing people, and create lasting memories</p>
        </div>

        <div class="search-filter">
            <form method="GET" style="display: contents;">
                <input type="text" name="search" placeholder="Search events by name or location..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Tech" <?php echo $categoryFilter === 'Tech' ? 'selected' : ''; ?>>Technology</option>
                    <option value="Art" <?php echo $categoryFilter === 'Art' ? 'selected' : ''; ?>>Art & Culture</option>
                    <option value="Sports" <?php echo $categoryFilter === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                    <option value="Wellness" <?php echo $categoryFilter === 'Wellness' ? 'selected' : ''; ?>>Wellness</option>
                    <option value="Education" <?php echo $categoryFilter === 'Education' ? 'selected' : ''; ?>>Education</option>
                    <option value="Social" <?php echo $categoryFilter === 'Social' ? 'selected' : ''; ?>>Social & Networking</option>
                </select>

                <select name="sort">
                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                </select>
            </form>
        </div>

        <?php if (count($events) > 0): ?>
            <div class="grid">
                <?php foreach ($events as $event): 
                    $spotsLeft = $event['capacity'] - $event['registered'];
                    $percentage = ($event['registered'] / $event['capacity']) * 100;
                    $eventDate = new DateTime($event['date']);
                    $eventTime = new DateTime($event['time']);
                ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="<?php echo $event['image_url']; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <div class="event-badge"><?php echo htmlspecialchars($event['category']); ?></div>
                        </div>
                        <div class="event-info">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-details">
                                <div class="event-detail-row">
                                    <span class="event-detail-icon">📅</span>
                                    <span><?php echo $eventDate->format('M d, Y'); ?></span>
                                </div>
                                <div class="event-detail-row">
                                    <span class="event-detail-icon">⏰</span>
                                    <span><?php echo $eventTime->format('h:i A'); ?></span>
                                </div>
                                <div class="event-detail-row">
                                    <span class="event-detail-icon">📍</span>
                                    <span><?php echo substr(htmlspecialchars($event['location']), 0, 40); ?></span>
                                </div>
                            </div>
                            <p class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p>
                            <div class="capacity-section">
                                <div class="capacity-text"><?php echo $spotsLeft > 0 ? $spotsLeft . ' spots available' : 'Sold Out'; ?></div>
                                <div class="capacity-bar">
                                    <div class="capacity-fill" style="width: <?php echo min($percentage, 100); ?>%;"></div>
                                </div>
                            </div>
                            <div class="event-footer">
                                <span style="color: rgba(226, 232, 240, 0.6); font-size: 0.9rem;"><?php echo $event['registered']; ?> registered</span>
                                <?php if ($spotsLeft > 0): ?>
                                    <a href="register.php?event_id=<?php echo $event['id']; ?>" class="btn btn-small">Register</a>
                                <?php else: ?>
                                    <button class="btn btn-small" disabled style="opacity: 0.5; cursor: not-allowed;">Sold Out</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Events Found</h3>
                <p>Try adjusting your search or filters to find more events</p>
                <a href="events.php" class="btn">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>