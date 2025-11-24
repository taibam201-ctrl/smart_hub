<?php
include 'config.php';
requireLogin();

$user = getCurrentUser();
$message = '';
$messageType = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $eventId = (int)($_POST['event_id'] ?? 0);
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $participants = (int)($_POST['participants'] ?? 1);
    $specialRequests = $conn->real_escape_string($_POST['special_requests'] ?? '');

    if ($eventId && $name && $email && $phone && $participants > 0) {
        $sql = "INSERT INTO registrations (event_id, name, email, phone, participants, special_requests) 
                VALUES ($eventId, '$name', '$email', '$phone', $participants, '$specialRequests')";
        
        if ($conn->query($sql) === TRUE) {
            $updateSql = "UPDATE events SET registered = registered + $participants WHERE id = $eventId";
            $conn->query($updateSql);
            
            $message = 'Successfully registered for the event! Check your email for confirmation.';
            $messageType = 'success';
        } else {
            $message = 'Error registering: ' . $conn->error;
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill all required fields!';
        $messageType = 'error';
    }
}

// Handle cancellation
if (isset($_GET['cancel'])) {
    $registrationId = (int)$_GET['cancel'];
    
    $getSQL = "SELECT event_id, participants FROM registrations WHERE id = $registrationId";
    $getResult = $conn->query($getSQL);
    
    if ($getResult->num_rows > 0) {
        $row = $getResult->fetch_assoc();
        $eventId = $row['event_id'];
        $participants = $row['participants'];
        
        $deleteSql = "DELETE FROM registrations WHERE id = $registrationId";
        if ($conn->query($deleteSql) === TRUE) {
            $updateSql = "UPDATE events SET registered = registered - $participants WHERE id = $eventId";
            $conn->query($updateSql);
            
            $message = 'Registration cancelled successfully!';
            $messageType = 'success';
        }
    }
}

$registrationsResult = $conn->query("SELECT r.*, e.title, e.date, e.time, e.location FROM registrations r JOIN events e ON r.event_id = e.id ORDER BY r.registration_date DESC");
$registrations = $registrationsResult->fetch_all(MYSQLI_ASSOC);

$eventsResult = $conn->query("SELECT id, title, date, time, location, capacity, registered FROM events ORDER BY date ASC");
$events = $eventsResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Smart Community Hub</title>
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
                <li><a href="announcements.php">Announcements</a></li>
                <li><a href="register.php" class="active">My Registrations</a></li>
                <li style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--secondary);">Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
                    <a href="logout.php" class="btn btn-small" style="margin: 0;">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>My Event Registrations</h1>
            <p>Manage your registered events and registrations</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('my-registrations')">My Registrations (<?php echo count($registrations); ?>)</button>
            <button class="tab-btn" onclick="showTab('register-form')">Register for Event</button>
        </div>

        <div id="my-registrations" class="tab-content active">
            <?php if (count($registrations) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Your Name</th>
                                <th>Email</th>
                                <th>Participants</th>
                                <th>Registered On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): 
                                $regDate = new DateTime($reg['date']);
                                $regTime = new DateTime($reg['time']);
                                $registeredOn = new DateTime($reg['registration_date']);
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($reg['title']); ?></strong></td>
                                    <td><?php echo $regDate->format('M d, Y') . ' ' . $regTime->format('h:i A'); ?></td>
                                    <td><?php echo substr(htmlspecialchars($reg['location']), 0, 30); ?></td>
                                    <td><?php echo htmlspecialchars($reg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                    <td><?php echo $reg['participants']; ?></td>
                                    <td><?php echo $registeredOn->format('M d, Y h:i A'); ?></td>
                                    <td>
                                        <a href="register.php?cancel=<?php echo $reg['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure?');">Cancel</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Registrations Yet</h3>
                    <p>You haven't registered for any events yet.</p>
                    <a href="events.php" class="btn">Browse Events</a>
                </div>
            <?php endif; ?>
        </div>

        <div id="register-form" class="tab-content">
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="event_id">Select Event *</label>
                        <select id="event_id" name="event_id" required>
                            <option value="">Choose an event to register</option>
                            <?php foreach ($events as $event): 
                                $spotsLeft = $event['capacity'] - $event['registered'];
                                $eventDate = new DateTime($event['date']);
                            ?>
                                <option value="<?php echo $event['id']; ?>" <?php echo $spotsLeft <= 0 ? 'disabled' : ''; ?>>
                                    <?php echo htmlspecialchars($event['title']); ?> - <?php echo $eventDate->format('M d, Y'); ?> 
                                    (<?php echo $spotsLeft > 0 ? $spotsLeft . ' spots' : 'Sold Out'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div class="form-group">
                            <label for="name">Your Full Name *</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label for="participants">Number of Participants *</label>
                            <input type="number" id="participants" name="participants" placeholder="1" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" placeholder="Any special requirements?"></textarea>
                    </div>

                    <button type="submit" name="register" class="btn">Register for Event</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            var tabs = document.querySelectorAll('.tab-content');
            var buttons = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            
            buttons.forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>