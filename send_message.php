<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $to = "kip.edu.center@gmail.com";
    $subject = "New Contact Message from " . $name;
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    $headers = "From: $email";

    if (mail($to, $subject, $body, $headers)) {
        header("Location: send_message_success");
        exit(); 
    } else {
        echo "Error sending message.";
    }
}
?>
