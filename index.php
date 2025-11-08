<?php
session_start();
require_once 'config.php';

$loggedin = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $loggedin ? $_SESSION["username"] : null;

// When logged in, show forum
if ($loggedin) {
    // Fetch published threads (latest first)
    $sql = "SELECT threads.*, users.username FROM threads 
            JOIN users ON threads.author_id = users.user_id
            WHERE threads.status = 'published'
            ORDER BY threads.created_at DESC";
    $result = $mysqli->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tied Forum</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Joan:wght@400;700&family=Cedarville+Cursive&display=swap" rel="stylesheet">
</head>
<body>
    <?php if(!$loggedin): ?>
        <!-- Visitor homepage (Forum tagline/about/contact) -->
        <div class="header"><span class="logo">tied</span>
            <nav class="nav">
                <a href="index.php" class="active">Home</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                <a href="login.php">Login</a>
            </nav>
        </div>
        <div class="main-container">
            <h1 style="font-family:'Joan',serif;font-size:2.5rem;margin-top:36px;">Forum Tagline</h1>
            <h2 style="font-family:'Joan',serif; font-weight:400; color:#b07800;">Forum Subline</h2>
            <a href="register.php" class="button" style="margin:30px 0;font-size:1.3rem;">Join Now</a>
            <hr style="margin: 40px 0; border: none; border-bottom: 1.5px solid #f6a547;">
            <h2 id="about" style="font-family:'Joan',serif;">About Us</h2>
            <div style="background:#fbeed6cc;border-radius:20px;padding:22px 34px;max-width:500px;font-size:1.13rem;">
                We’re a small group of creators trying to create something new our very first forum web app! We have always loved how conversations can bring people together and we wanted to build a space  where anyone can share ideas, connect and just be themselves. We’re still learning, improving and figuring things out as we go, but every click, post and message helps us grow. Our goal isn’t to be perfect it’s to make a friendly, open space where  real people talk about real things. Thanks for checking us out. Your support means everything this is just the BEGINNING! 
            </div>
            <a href="register.php" class="button" style="margin:30px 0;">Join Us Now</a>
            <hr style="margin: 40px 0; border: none; border-bottom: 1.5px solid #f6a547;">
            <h2 id="contact" style="font-family:'Joan',serif;">Contact Us</h2>
            <form style="max-width:380px;margin-bottom:30px;">
                <div style="margin-bottom:14px;">
                    <label>Name:</label>
                    <input type="text" style="width:100%;border-radius:13px;padding:11px 17px;border:2px solid #f6a547;">
                </div>
                <div style="margin-bottom:14px;">
                    <label>Message:</label>
                    <textarea style="width:100%;border-radius:13px;padding:11px 17px;border:2px solid #f6a547;min-height:90px;"></textarea>
                </div>
                <button type="submit" class="button">Send</button>
            </form>
            <footer style="margin-top:40px;">
                <span class="logo" style="font-size:2rem;">tied</span>
                <!-- social links icons can go here -->
            </footer>
        </div>
    <?php else: ?>
        <!-- Logged-in forum homepage -->
        <div class="sidebar">
            <span class="logo">tied</span>
            <a href="index.php" class="active">Home</a>
            <a href="create_thread.php">Create Thread</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="main-container">
            <h2 style="font-family:'Joan',serif;font-size:1.6rem;">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <?php
            if($result && $result->num_rows > 0): 
                while($row = $result->fetch_assoc()):
            ?>
                <div class="card">
                    <div class="author-row">
                        <div class="author-avatar"></div>
                        <span style="font-weight:600;"><?php echo htmlspecialchars($row['username']); ?></span>
                        <span style="margin-left:10px;font-family:'Cedarville Cursive',cursive;color: #d18401;">
                            <?php
                                $date = new DateTime($row['created_at']);
                                $now = new DateTime();
                                $diff = $now->diff($date);
                                echo ($diff->days >= 365) ? floor($diff->days/365) . " Yr ago" : $diff->days . "d ago";
                            ?>
                        </span>
                    </div>
                    <div class="thread-title"><?php echo htmlspecialchars($row['title']); ?></div>
                    <div class="thread-body"><?php echo nl2br(htmlspecialchars(mb_strimwidth($row['content'],0,140,"..."))); ?></div>
                    <a href="view_thread.php?id=<?php echo $row['thread_id']; ?>" class="button" style="background:var(--accent);color:#fff;margin-top:14px;">more...</a>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <div class="card">
                    <div class="thread-title">No threads yet.</div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>