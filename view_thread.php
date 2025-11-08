<?php
session_start();
require_once "config.php";
$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch thread, only allow author to view draft
$sql = "SELECT threads.*, users.username FROM threads JOIN users ON threads.author_id = users.user_id WHERE threads.thread_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows != 1){
    die("Thread not found.");
}
$thread = $result->fetch_assoc();
$stmt->close();

$loggedin = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]===true;
$is_author = ($loggedin && $_SESSION["user_id"] == $thread['author_id']);

if($thread['status'] == "draft" && !$is_author){
    die("You don't have access to view this draft thread.");
}

// Handle new comment post
$comment_err = "";
if($loggedin && $_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["comment"])){
    if(empty(trim($_POST["comment"]))){
        $comment_err = "Reply cannot be empty.";
    } else {
        $cmt = trim($_POST["comment"]);
        $cmtsql = "INSERT INTO comments (thread_id, author_id, content) VALUES (?,?,?)";
        if($stmt = $mysqli->prepare($cmtsql)){
            $stmt->bind_param("iis", $thread_id, $_SESSION["user_id"], $cmt);
            $stmt->execute();
            $stmt->close();
            // reload comments
            header("Location: view_thread.php?id=$thread_id");
            exit;
        }
    }
}

// Fetch comments for this thread
$csql = "SELECT comments.*, users.username FROM comments JOIN users ON comments.author_id = users.user_id WHERE comments.thread_id = ? ORDER BY comments.created_at ASC";
$cstmt = $mysqli->prepare($csql);
$cstmt->bind_param("i", $thread_id);
$cstmt->execute();
$comments = $cstmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($thread['title']); ?> | Tied Forum</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Joan:wght@400;700&family=Cedarville+Cursive&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <span class="logo">tied</span>
        <a href="index.php">Home</a>
        <a href="create_thread.php">Create Thread</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-container">
        <div class="card">
            <div class="author-row">
                <div class="author-avatar"></div>
                <span style="font-weight:600;"><?php echo htmlspecialchars($thread['username']); ?></span>
                <span style="margin-left:10px;font-family:'Cedarville Cursive',cursive;color: #d18401;">
                    <?php
                        $date = new DateTime($thread['created_at']);
                        $now = new DateTime();
                        $diff = $now->diff($date);
                        echo ($diff->days >= 365) ? floor($diff->days/365) . " Yr ago" : $diff->days . "d ago";
                    ?>
                </span>
            </div>
            <div class="thread-title"><?php echo htmlspecialchars($thread['title']); ?></div>
            <div class="thread-body"><?php echo nl2br(htmlspecialchars($thread['content'])); ?></div>
            <div class="comment-row">
                <h4 style="font-family:'Joan';margin:12px 0 6px 0;">Replies</h4>
                <?php if($comments->num_rows > 0): 
                    while($row = $comments->fetch_assoc()): ?>
                        <div style="margin-bottom:10px;padding-bottom:8px;border-bottom:1px dashed #ffd195;">
                            <strong><?php echo htmlspecialchars($row['username']); ?>:</strong>
                            <span><?php echo nl2br(htmlspecialchars($row['content'])); ?></span>
                            <span style="font-size:0.8em;color:#b68c2c;font-family:'Cedarville Cursive',cursive;">
                                <?php
                                    $cdate = new DateTime($row['created_at']);
                                    $cdiff = $now->diff($cdate);
                                    echo ($cdiff->days >= 365) ? floor($cdiff->days/365) . " Yr ago" : $cdiff->days . "d ago";
                                ?>
                            </span>
                        </div>
                <?php endwhile; else: ?>
                    <div>No replies yet.</div>
                <?php endif; ?>
                <?php if($loggedin): ?>
                <form action="" method="post" style="margin-top:16px;">
                    <textarea name="comment" placeholder="Reply..." style="width:90%;border-radius:8px;padding:7px;margin-bottom:8px;"></textarea>
                    <button type="submit" class="button">Post Reply</button>
                    <span class="minor" style="color:red;"><?php echo $comment_err; ?></span>
                </form>
                <?php else: ?>
                    <div style="margin-top:14px;">Login to post a reply.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>