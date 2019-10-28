<?php
    include('init.php');

    if(isset($_GET['email'])) {
        $email = $_GET['email'];
        $email = clean($email);
        $validation_code = md5($email . microtime());

        $subject = "Activate Account";
        $msg = "Please click the link below to activate your account
        http://localhost/login/activate.php?email=$email&code=$validation_code
        ";
        $headers = "From: norreply@yourwebsite.com";
        if(send_email($email, $subject, $msg, $headers)) {
            $sql = "UPDATE users SET validation_code = '$validation_code' WHERE email = '$email'";
            $result = query($sql);
            confirm($result);
            set_message("<p class='alert alert-success text-center'>Please check your email or spam folder for an activation link</p>");
            redirect("../index.php");
        } else {
            set_message("<p class='alert alert-danger text-center'>Email could not be sent</p>");
            redirect("../index.php");
        }
    }

?>