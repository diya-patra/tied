<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Handle thread deletion
if (isset($_GET['delete_thread'])) {
    $del_thr_id = intval($_GET['delete_thread']);
    $del_q = $mysqli->prepare("DELETE FROM threads WHERE thread_id = ? AND author_id = ?");
    $del_q->bind_param("ii", $del_thr_id, $user_id);
    $del_q->execute();
    $del_q->close();
    header("Location: dashboard.php");
    exit;
}

// Handle comment deletion
if (isset($_GET['delete_comment'])) {
    $del_cmt_id = intval($_GET['delete_comment']);
    $del_q = $mysqli->prepare("DELETE FROM comments WHERE post_id = ? AND author_id = ?");
    $del_q->bind_param("ii", $del_cmt_id, $user_id);
    $del_q->execute();
    $del_q->close();
    header("Location: dashboard.php");
    exit;
}

// Fetch user's threads
$sql_thr = "SELECT * FROM threads WHERE author_id = ? ORDER BY created_at DESC";
$stmt_thr = $mysqli->prepare($sql_thr);
$stmt_thr->bind_param("i", $user_id);
$stmt_thr->execute();
$threads = $stmt_thr->get_result();
$stmt_thr->close();

// Fetch user's comments
$sql_cmt = "SELECT comments.post_id, comments.content, comments.created_at, threads.title 
            FROM comments
            JOIN threads ON comments.thread_id = threads.thread_id
            WHERE comments.author_id = ?
            ORDER BY comments.created_at DESC";
$stmt_cmt = $mysqli->prepare($sql_cmt);
$stmt_cmt->bind_param("i", $user_id);
$stmt_cmt->execute();
$comments = $stmt_cmt->get_result();
$stmt_cmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Tied Forum</title>
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Joan:wght@400;700&family=Cedarville+Cursive&display=swap" rel="stylesheet">
    <style>
        .post-preview { max-height: 80px; overflow: hidden; position: relative; }
        .more-btn { color: #5946d4; cursor: pointer; font-size: 0.9em; display: inline-block; margin-top: 6px; }
        .button {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            background: #5946d4;
            color: #fff;
            font-size: 0.9rem;
            margin-right: 8px;
        }
        .button:hover { opacity: 0.85; }
        .card { margin-bottom: 22px; padding: 16px; border: 1px solid #ffd195; border-radius: 12px; background: #fff8f0; }

        /* Fix thread action buttons layout on dashboard */
.thread-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 12px;
}

.thread-actions a.button {
    position: static !important;
    float: none !important;
    background: #FFA538;
    color: #000;
    padding: 8px 16px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 3px 10px #ecb05633;
    transition: background 0.2s;
}

.thread-actions a.button:hover {
    background: #ffb84d;
}

/* Optional: keep your orange and red variants */
.thread-actions a[style*="background:orange"] {
    background: orange !important;
}
.thread-actions a[style*="background:#d72323"] {
    background: #d72323 !important;
}
/* Fix layout for delete button in "Your Replies" section */
.card .reply-delete {
    position: static !important;
    float: none !important;
    margin-top: 8px;
    display: inline-block;
    background: #d72323 !important;
    color: #000000ff !important;
    border-radius: 25px;
    padding: 8px 16px;
    font-weight: 600;
    box-shadow: 0 3px 10px #ecb05633;
    transition: background 0.2s;
    text-decoration: none;
}

.card .reply-delete:hover {
    background: #e53935 !important;
}

    </style>
</head>
<body>
    <div class="sidebar">
        <span class="logo">tied</span>
        <a href="index.php">Home</a>
        <a href="create_thread.php">Create Thread</a>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-container">
        <h2 style="font-family:'Joan',serif;font-size:2rem;font-weight:400;margin-bottom:19px;">
            Hi <?php echo htmlspecialchars($username); ?> 👋
        </h2>

        <!-- THREADS -->
        <div class="card">
            <h3>Your Threads</h3>
            <?php if ($threads->num_rows > 0): ?>
                <?php while ($row = $threads->fetch_assoc()): ?>
                    <div style="border-bottom:1px dashed #ffd195;padding:6px 0 10px 0;">
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                        <?php if ($row['status'] == 'draft'): ?>
                        <span class="badge" style="background:orange;color:#fff;border-radius:8px;padding:3px 9px;margin-left:8px;font-size:0.95em;">Draft</span>
                        <?php elseif ($row['status'] == 'published'): ?>
                        <span class="badge" style="background:green;color:#fff;border-radius:8px;padding:3px 9px;margin-left:8px;font-size:0.95em;">Published</span>
                        <?php endif; ?>

                        <div class="post-preview" id="content-<?php echo $row['thread_id']; ?>">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>

                        <?php if (strlen($row['content']) > 300): ?>
                        <span class="more-btn" onclick="showMore(<?php echo $row['thread_id']; ?>)">Read More</span>
                        <?php endif; ?>

                        <div class="thread-actions" style="margin-top:10px; display:flex; gap:10px;">
                            <a href="view_thread.php?id=<?php echo $row['thread_id']; ?>" class="button" style="background: #5946d4;">View</a>
                            <a href="edit_thread.php?id=<?php echo $row['thread_id']; ?>" class="button" style="background: orange;">Edit</a>
                            <a href="dashboard.php?delete_thread=<?php echo $row['thread_id']; ?>" class="button" style="background:#d72323;" onclick="return confirm('Delete this thread?');">Delete</a>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div style="margin:18px 0;">No threads yet.</div>
            <?php endif; ?>
        </div>

        <!-- COMMENTS -->
        <div class="card">
            <h3>Your Replies</h3>
            <?php if ($comments->num_rows > 0): ?>
                <?php while ($row = $comments->fetch_assoc()): ?>
                    <div style="border-bottom:1px dashed #ffd195;padding:6px 0 10px 0;">
                        <strong>On:</strong> <?php echo htmlspecialchars($row['title']); ?> — 
                        <span><?php echo nl2br(htmlspecialchars($row['content'])); ?></span>
                        <div style="margin-top:8px;">
                            <a href="dashboard.php?delete_comment=<?php echo $row['post_id']; ?>" class="reply-delete" onclick="return confirm('Delete this reply?');">Delete</a>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div style="margin:18px 0;">No replies yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showMore(id) {
            const div = document.getElementById('content-' + id);
            div.style.maxHeight = 'none';
            div.nextElementSibling.style.display = 'none';
        }
    </script>
</body>
</html>
