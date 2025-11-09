<?php
session_start();
require_once "config.php";
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch thread, only allow author to edit
$sql = "SELECT * FROM threads WHERE thread_id = ? AND author_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $thread_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows != 1){
    die("You can't edit this thread.");
}
$thread = $result->fetch_assoc();
$stmt->close();

$title = $thread["title"];
$content = $thread["content"];
$status = $thread["status"];
$title_err = $content_err = $status_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    if(empty(trim($_POST["content"]))){
        $content_err = "Please enter content.";
    } else {
        $content = trim($_POST["content"]);
    }
    if(!isset($_POST["status"]) || !in_array($_POST["status"], ["draft","published"])){
        $status_err = "Select a status!";
    } else {
        $status = $_POST["status"];
    }
    if(empty($title_err) && empty($content_err)){
        $sql2 = "UPDATE threads SET title=?, content=?, status=?, updated_at=NOW() WHERE thread_id=? AND author_id=?";
        if($stmt2 = $mysqli->prepare($sql2)){
            $stmt2->bind_param("sssii", $title, $content, $status, $thread_id, $user_id);
            if($stmt2->execute()){
                header("Location: dashboard.php");
                exit;
            }
            $stmt2->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Thread | Tied Forum</title>
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
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
                <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>
            </div>
            <form action="" method="post">
                <input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($title); ?>" required style="font-weight:600;">
                <span class="minor" style="color:red;"><?php echo $title_err; ?></span>
                <textarea name="content" placeholder="Edit your thread..." required><?php echo htmlspecialchars($content); ?></textarea>
                <span class="minor" style="color:red;"><?php echo $content_err; ?></span>
                <select name="status" style="width:140px;margin-top:10px;border-radius:7px;padding:7px;">
                    <option value="draft" <?php if($status=='draft')echo 'selected'; ?>>Save as Draft</option>
                    <option value="published" <?php if($status=='published')echo 'selected'; ?>>Publish</option>
                </select>
                <span class="minor" style="color:red;"><?php echo $status_err; ?></span>
                <br>
                <button type="submit" class="button" style="margin-top:10px;">Update</button>
            </form>
        </div>
    </div>
</body>
</html>