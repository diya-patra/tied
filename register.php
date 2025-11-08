<?php
session_start();
require_once "config.php";

$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = $register_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Username validation
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $username_err = "This username is already taken.";
            } else {
                $username = trim($_POST["username"]);
            }
            $stmt->close();
        }
    }
    // Email validation
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else{
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $email_err = "This email is already registered.";
            } else {
                $email = trim($_POST["email"]);
            }
            $stmt->close();
        }
    }
    // Password validation
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    // Confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if($password != $confirm_password){
            $confirm_password_err = "Password did not match.";
        }
    }

    // Final registration
    if(empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("sss", $param_username, $param_email, $param_password);
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            if($stmt->execute()){
                header("location: login.php");
                exit();
            } else{
                $register_err = "Something went wrong, try again.";
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
    <title>Sign Up | Tied Forum</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Joan:wght@400;700&family=Cedarville+Cursive&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-panel">
        <h2 style="font-family:'Cedarville Cursive',cursive;margin-bottom:32px;font-size:2.1rem;">Sign-Up with a new account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
            <span class="minor" style="color:red;"><?php echo $username_err; ?></span>
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            <span class="minor" style="color:red;"><?php echo $email_err; ?></span>
            <input type="password" name="password" placeholder="Password" required>
            <span class="minor" style="color:red;"><?php echo $password_err; ?></span>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <span class="minor" style="color:red;"><?php echo $confirm_password_err; ?></span>
            <button type="submit" class="button">Continue</button>
            <span class="minor" style="color:red;"><?php echo $register_err; ?></span>
        </form>
        <p class="minor">Already have an account? <a href="login.php" style="color:#2655c1;">Login Here</a></p>
    </div>
</body>
</html>