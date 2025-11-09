<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Handle thread deletion
if(isset($_GET['delete_thread'])){
    $del_thr_id = intval($_GET['delete_thread']);
    $del_q = $mysqli->prepare("DELETE FROM threads WHERE thread_id = ? AND author_id = ?");
    $del_q->bind_param("ii", $del_thr_id, $user_id);
    $del_q->execute();
    $del_q->close();
    header("Location: dashboard.php");
    exit;
}
// Handle comment deletion
if(isset($_GET['delete_comment'])){
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
$sql_cmt = "SELECT comments.*, threads.title FROM comments JOIN threads ON comments.thread_id = threads.thread_id WHERE comments.author_id = ? ORDER BY comments.created_at DESC";
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
        <h2 style="font-family:'Joan',serif;font-size:2rem;font-weight:400;margin-bottom:19px;">Hi <?php echo htmlspecialchars($username); ?> 👋</h2>
        <div class="card">
            <h3>Your Threads</h3>
            <?php if($threads->num_rows > 0): 
                while($row = $threads->fetch_assoc()): ?>
                <div style="border-bottom:1px dashed #ffd195;padding:6px 0 7px 0;">
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong> - 
                    <em><?php echo htmlspecialchars($row['status']); ?></em>
                    <a href="view_thread.php?id=<?php echo $row['thread_id']; ?>" class="button" style="background:var(--accent);color:#fff;margin-left:10px;">View</a>
                    <a href="dashboard.php?delete_thread=<?php echo $row['thread_id']; ?>" class="button" style="background:#d72323;color:#fff;margin-left:10px;" onclick="return confirm('Delete this thread?');">Delete</a>
                </div>
                <?php endwhile; else: ?>
                <div style="margin:18px 0;">No threads yet.</div>
            <?php endif; ?>
        </div>
        <div class="card">
            <h3>Your Replies</h3>
            <?php if($comments->num_rows > 0): 
                while($row = $comments->fetch_assoc()): ?>
                <div style="border-bottom:1px dashed #ffd195;padding:6px 0 7px 0;">
                    <strong>On:</strong> <?php echo htmlspecialchars($row['title']); ?> —
                    <span><?php echo nl2br(htmlspecialchars($row['content'])); ?></span>
                    <a href="dashboard.php?delete_comment=<?php echo $row['post_id']; ?>" class="button" style="background:#d72323;color:#fff;margin-left:10px;" onclick="return confirm('Delete this reply?');">Delete</a>
                </div>
                <?php endwhile; else: ?>
                <div style="margin:18px 0;">No replies yet.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>