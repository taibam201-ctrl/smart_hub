<?php
include 'config.php';
requireLogin();

$user = getCurrentUser();
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$sortBy = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

$sql = "SELECT a.*, u.username FROM announcements a JOIN users u ON a.created_by = u.id WHERE a.status = 'published'";

if ($searchTerm) {
    $sql .= " AND (a.title LIKE '%$searchTerm%' OR a.content LIKE '%$searchTerm%')";
}

if ($categoryFilter) {
    $sql .= " AND a.category = '$categoryFilter'";
}

if ($sortBy === 'oldest') {
    $sql .= " ORDER BY a.created_at ASC";
} elseif ($sortBy === 'views') {
    $sql .= " ORDER BY a.views DESC";
} else {
    $sql .= " ORDER BY a.is_pinned DESC, a.created_at DESC";
}

$result = $conn->query($sql);
$announcements = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Smart Community Hub</title>
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
                <?php if (isAdmin()): ?>
                    <li><a href="add-announcement.php">Add Announcement</a></li>
                <?php endif; ?>
                <li style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--secondary);">Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
                    <a href="logout.php" class="btn btn-small" style="margin: 0;">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Community Announcements</h1>
            <p>Stay updated with the latest news, events, and updates from your community</p>
        </div>

        <div class="search-filter">
            <form method="GET" style="display: contents;">
                <input type="text" name="search" placeholder="Search announcements..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Announcement" <?php echo $categoryFilter === 'Announcement' ? 'selected' : ''; ?>>Announcement</option>
                    <option value="Community" <?php echo $categoryFilter === 'Community' ? 'selected' : ''; ?>>Community</option>
                    <option value="Meeting" <?php echo $categoryFilter === 'Meeting' ? 'selected' : ''; ?>>Meeting</option>
                    <option value="Event" <?php echo $categoryFilter === 'Event' ? 'selected' : ''; ?>>Event</option>
                    <option value="Update" <?php echo $categoryFilter === 'Update' ? 'selected' : ''; ?>>Update</option>
                </select>

                <select name="sort">
                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="views" <?php echo $sortBy === 'views' ? 'selected' : ''; ?>>Most Viewed</option>
                </select>
            </form>
        </div>

        <?php if (count($announcements) > 0): ?>
            <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem; margin: 3rem auto;">
                <?php foreach ($announcements as $ann): 
                    $createdDate = new DateTime($ann['created_at']);
                    $isPinned = $ann['is_pinned'] == 1;
                ?>
                    <div class="announcement-card" style="<?php echo $isPinned ? 'border: 2px solid rgba(236, 72, 153, 0.5);' : ''; ?>">
                        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
                            <div style="position: relative; height: 200px; overflow: hidden; border-radius: 15px;">
                                <img src="<?php echo $ann['image_url']; ?>" alt="<?php echo htmlspecialchars($ann['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php if ($isPinned): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: linear-gradient(135deg, #ec4899, #f472b6); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">📌 Pinned</div>
                                <?php endif; ?>
                                <div class="announcement-badge"><?php echo htmlspecialchars($ann['category']); ?></div>
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.8rem; color: var(--light);"><?php echo htmlspecialchars($ann['title']); ?></h3>
                                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.9rem; color: rgba(226, 232, 240, 0.7); flex-wrap: wrap;">
                                        <span>By <strong><?php echo htmlspecialchars($ann['username']); ?></strong></span>
                                        <span>•</span>
                                        <span><?php echo $createdDate->format('M d, Y'); ?></span>
                                        <span>•</span>
                                        <span>👁 <?php echo $ann['views']; ?> views</span>
                                    </div>
                                    <p style="color: rgba(226, 232, 240, 0.8); line-height: 1.6; font-size: 1rem;"><?php echo substr(htmlspecialchars($ann['content']), 0, 250); ?>...</p>
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                                    <a href="announcement-detail.php?id=<?php echo $ann['id']; ?>" class="btn btn-small">Read More</a>
                                    <?php if (isAdmin() || isAnnouncementCreator($ann['id'])): ?>
                                        <a href="delete-announcement.php?id=<?php echo $ann['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this announcement?');">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Announcements Found</h3>
                <p>Check back soon for community updates</p>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .announcement-card {
            background: var(--glass);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .announcement-card:hover {
            border-color: var(--primary);
            box-shadow: 0 25px 50px rgba(124, 58, 237, 0.3);
            transform: translateY(-5px);
        }

        .announcement-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: white !important;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3) !important;
        }

        @media (max-width: 768px) {
            .announcement-card > div {
                grid-template-columns: 1fr !important;
            }

            .announcement-card {
                padding: 1.5rem;
            }
        }
    </style>
</body>
</html>