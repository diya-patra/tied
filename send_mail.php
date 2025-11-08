<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $message = htmlspecialchars(trim($_POST['message']));

    $to = "diyapatra2nd@gmail.com"; // 🔸 replace with your actual email
    $subject = "New Message from Tied Contact Form";
    $body = "You have received a new message from $name.\n\nMessage:\n$message";
    $headers = "From: noreply@yourdomain.com\r\n";
    $headers .= "Reply-To: $to\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Message sent successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Failed to send message. Try again later.'); window.location.href='index.php';</script>";
    }
}
?>
