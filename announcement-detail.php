<?php
include 'config.php';
requireLogin();

$user = getCurrentUser();
$announcementId = (int)($_GET['id'] ?? 0);

if (!$announcementId) {
    header('Location: announcements.php');
    exit();
}

$sql = "SELECT a.*, u.username FROM announcements a JOIN users u ON a.created_by = u.id WHERE a.id = $announcementId AND a.status = 'published'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: announcements.php');
    exit();
}

$announcement = $result->fetch_assoc();
$createdDate = new DateTime($announcement['created_at']);
$updatedDate = new DateTime($announcement['updated_at']);

// Update view count
$conn->query("UPDATE announcements SET views = views + 1 WHERE id = $announcementId");
$announcement['views']++;

// <CHANGE> Handle comment submission
$commentMessage = '';
$commentType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    $comment = $conn->real_escape_string($_POST['comment'] ?? '');
    
    if (trim($comment)) {
        $userId = $user['id'];
        $insertSql = "INSERT INTO announcement_comments (announcement_id, user_id, comment) VALUES ($announcementId, $userId, '$comment')";
        
        if ($conn->query($insertSql) === TRUE) {
            $commentMessage = 'Comment posted successfully!';
            $commentType = 'success';
        } else {
            $commentMessage = 'Error posting comment: ' . $conn->error;
            $commentType = 'error';
        }
    } else {
        $commentMessage = 'Comment cannot be empty!';
        $commentType = 'error';
    }
}

// <CHANGE> Fetch all comments for this announcement
$commentsResult = $conn->query("SELECT c.*, u.username FROM announcement_comments c JOIN users u ON c.user_id = u.id WHERE c.announcement_id = $announcementId ORDER BY c.created_at DESC");
$comments = $commentsResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($announcement['title']); ?> - Smart Community Hub</title>
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
        <div style="max-width: 900px; margin: 0 auto;">
            <a href="announcements.php" class="btn btn-small" style="margin-bottom: 2rem;">← Back to Announcements</a>

            <div style="background: var(--glass); backdrop-filter: blur(30px); border: 1px solid rgba(124, 58, 237, 0.3); border-radius: 20px; overflow: hidden; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);">
                <div style="width: 100%; height: 400px; overflow: hidden;">
                    <img src="<?php echo $announcement['image_url']; ?>" alt="<?php echo htmlspecialchars($announcement['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>

                <div style="padding: 3rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
                        <div>
                            <div style="display: inline-block; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem;"><?php echo htmlspecialchars($announcement['category']); ?></div>
                            <h1 style="font-size: 2.5rem; font-weight: 900; margin: 1rem 0; color: var(--light);"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <!-- <CHANGE> Add delete button for admin or creator -->
                            <?php if (isAdmin() || isAnnouncementCreator($announcementId)): ?>
                                <a href="delete-announcement.php?id=<?php echo $announcementId; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this announcement? This action cannot be undone.');">Delete</a>
                            <?php endif; ?>
                            <?php if ($announcement['is_pinned']): ?>
                                <div style="background: rgba(236, 72, 153, 0.2); border: 1px solid rgba(236, 72, 153, 0.5); padding: 1rem; border-radius: 12px; text-align: center;">
                                    <span style="font-size: 1.5rem;">📌</span>
                                    <p style="margin: 0.5rem 0 0 0; color: var(--accent); font-size: 0.9rem; font-weight: 700;">Pinned</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 2rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(124, 58, 237, 0.2); flex-wrap: wrap;">
                        <div>
                            <span style="color: rgba(226, 232, 240, 0.6); font-size: 0.9rem;">By</span>
                            <p style="margin: 0.5rem 0 0 0; color: var(--secondary); font-weight: 700;"><?php echo htmlspecialchars($announcement['username']); ?></p>
                        </div>
                        <div>
                            <span style="color: rgba(226, 232, 240, 0.6); font-size: 0.9rem;">Published</span>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text); font-weight: 700;"><?php echo $createdDate->format('M d, Y h:i A'); ?></p>
                        </div>
                        <div>
                            <span style="color: rgba(226, 232, 240, 0.6); font-size: 0.9rem;">Views</span>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text); font-weight: 700;">👁 <?php echo $announcement['views']; ?></p>
                        </div>
                    </div>

                    <div style="font-size: 1.1rem; line-height: 1.8; color: rgba(226, 232, 240, 0.9); margin-bottom: 3rem;">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>

                    <!-- <CHANGE> Comments section -->
                    <div style="border-top: 2px solid rgba(124, 58, 237, 0.3); padding-top: 2rem;">
                        <h3 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--light);">Comments (<?php echo count($comments); ?>)</h3>

                        <?php if ($commentMessage): ?>
                            <div class="message <?php echo $commentType; ?>" style="margin-bottom: 1.5rem;">
                                <?php echo $commentMessage; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Comment Form -->
                        <div style="background: rgba(124, 58, 237, 0.1); border: 1px solid rgba(124, 58, 237, 0.3); padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem;">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="comment" style="display: block; margin-bottom: 0.5rem; color: var(--light); font-weight: 700;">Add Your Comment</label>
                                    <textarea id="comment" name="comment" placeholder="Share your thoughts about this announcement..." required style="width: 100%; min-height: 100px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(124, 58, 237, 0.5); border-radius: 10px; padding: 1rem; color: var(--light); font-size: 0.95rem; resize: vertical;"></textarea>
                                </div>
                                <button type="submit" name="add_comment" class="btn" style="margin-top: 1rem;">Post Comment</button>
                            </form>
                        </div>

                        <!-- Comments List -->
                        <?php if (count($comments) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <?php foreach ($comments as $comment): 
                                    $commentDate = new DateTime($comment['created_at']);
                                ?>
                                    <div style="background: rgba(124, 58, 237, 0.1); border-left: 3px solid var(--primary); padding: 1.5rem; border-radius: 10px;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.8rem;">
                                            <div>
                                                <p style="font-weight: 700; color: var(--secondary); margin: 0;"><?php echo htmlspecialchars($comment['username']); ?></p>
                                                <p style="font-size: 0.85rem; color: rgba(226, 232, 240, 0.6); margin: 0.3rem 0 0 0;"><?php echo $commentDate->format('M d, Y h:i A'); ?></p>
                                            </div>
                                        </div>
                                        <p style="color: rgba(226, 232, 240, 0.9); line-height: 1.6; margin: 0;"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: rgba(226, 232, 240, 0.6); padding: 2rem;">No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(124, 58, 237, 0.2);">
                        <a href="announcements.php" class="btn">Back to Announcements</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>