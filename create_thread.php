<?php
session_start();
require_once "config.php";
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$title = $content = $status = "";
$title_err = $content_err = $status_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    if (empty(trim($_POST["content"]))){
        $content_err = "Please write your thread content.";
    } else {
        $content = trim($_POST["content"]);
    }
    if (!isset($_POST["status"]) || !in_array($_POST["status"], ["draft","published"])){
        $status_err = "Select a status!";
    } else {
        $status = $_POST["status"];
    }
    if(empty($title_err) && empty($content_err) && empty($status_err)){
        $sql = "INSERT INTO threads (title, content, author_id, status) VALUES (?,?,?,?)";
        if($stmt=$mysqli->prepare($sql)){
            $stmt->bind_param("ssis", $title, $content, $_SESSION["user_id"], $status);
            if($stmt->execute()){
                header("Location: dashboard.php");
                exit;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Thread | Tied Forum</title>
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Joan:wght@400;700&family=Cedarville+Cursive&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <span class="logo">tied</span>
        <a href="index.php">Home</a>
        <a href="create_thread.php" class="active">Create Thread</a>
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
                <input class="title" type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($title); ?>" required style="font-weight:600;">
                <span class="minor" style="color:red;"><?php echo $title_err; ?></span>
                <textarea class="content" name="content" placeholder="Text Here..." required><?php echo htmlspecialchars($content); ?></textarea>
                <span class="minor" style="color:red;"><?php echo $content_err; ?></span>
                <select name="status" style="width:140px;margin-top:10px;border-radius:7px;padding:7px;">
                    <option value="draft" <?php if($status=='draft')echo 'selected'; ?>>Save as Draft</option>
                    <option value="published" <?php if($status=='published')echo 'selected'; ?>>Publish</option>
                </select>
                <span class="minor" style="color:red;"><?php echo $status_err; ?></span>
                <br>
                <button type="submit" class="button" style="margin-top:10px;">Publish</button>
            </form>
        </div>
    </div>
</body>
</html>