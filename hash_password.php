<?php
$password = 'Ha1@2004';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>
