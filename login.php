<?php
session_start();
require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Username validation
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    // Password validation
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    // Check credentials
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT user_id, username, password FROM users WHERE username = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $stmt->bind_result($user_id, $username, $hashed_password);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["username"] = $username;
                            header("location: index.php");
                            exit();
                        } else{
                            $login_err = "Invalid password.";
                        }
                    }
                } else{
                    $login_err = "No account found with that username.";
                }
            } else{
                $login_err = "Something went wrong, try again.";
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Tied Forum</title>
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Joan:wght@400;700&family=Cedarville+Cursive&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-panel">
        <h2 style="font-family:'Cedarville Cursive',cursive;margin-bottom:32px;font-size:2.1rem;">Login with an existing account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <span class="minor" style="color:red;"><?php echo $username_err; ?></span>
            <input type="password" name="password" placeholder="Password" required>
            <span class="minor" style="color:red;"><?php echo $password_err; ?></span>
            <button type="submit" class="button">Continue</button>
            <span class="minor" style="color:red;"><?php echo $login_err; ?></span>
        </form>
        <div style="margin-top:14px;">
            <a href="#">Forgot Password?</a>
        </div>
        <p class="minor">Don’t have an account? <a href="register.php" style="color:#2655c1;">Sign-Up Here</a></p>
    </div>
</body>
</html>