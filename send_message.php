<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();                                           
    $mail->Host       = 'mail.kipeducationid.com';  // cPanel SMTP server
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = '_mainaccount@kipeducationid.com';  // Use the correct cPanel SMTP username
    $mail->Password   = $_ENV['MAIL_PASSWORD']; // The password for your cPanel email 
    $mail->SMTPSecure = 'ssl';  // Use SSL (465) or TLS (587)
    $mail->Port       = 465;    // SSL Port

    // Recipients
    $mail->setFrom('_mainaccount@kipeducationid.com', $_POST['name']); // Get the name from the form field
    $mail->addAddress('kip.edu.center@gmail.com'); // Send to the recipient

    // Content
    $mail->isHTML(true);                                  
    $mail->Subject = 'New Contact Message from ' . $_POST['name'];
    $mail->Body    = '<p><strong>Name:</strong> ' . $_POST['name'] . '</p>
                      <p><strong>Email:</strong> ' . $_POST['email'] . '</p>
                      <p><strong>Message:</strong> ' . $_POST['message'] . '</p>';
    
    $mail->AltBody = 
    "Name: " . $_POST['name'] . "\n" .
    "Email: " . $_POST['email'] . "\n\n" .
    "Message:\n" . $_POST['message'];
    // For non-HTML email clients

    $mail->send();
    header("Location: send_message_success");
    exit();
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
