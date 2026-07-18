<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
$mail = new PHPMailer(true);


    // Only process POST reqeusts.

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Get the form fields and remove MORALspace.

        // NAME

        if(isset($_POST["name"])){
            $name = trim($_POST["name"]);
        }else{
            $name = "hi";
        }

        // EMAIL
        $email = $_POST["email"];

        // IF LAST NAME SET
        if(isset($_POST["l_name"])){
            $name_2 = trim($_POST["l_name"]);
        }else{
            $name_2 = " ";
        }

        // IF SUBJECT SET
        if(isset($_POST["subject"])){
            $subject = trim($_POST["subject"]);
        }else{
            $subject = "hi";
        }

        // IF PHONE SET
        if(isset($_POST["phone"])){
            $phone = trim($_POST["phone"]);
        }else{
            $phone = "hi";
        }

        // IF COMPANY SET
        if(isset($_POST["company"])){
            $company = trim($_POST["company"]);
        }else{
            $company = "hi";
        }

        // IF MESSAGE SET
        if(isset($_POST["message"])){
            $message = trim($_POST["message"]);
        }else{
            $message = "hi";
        }

        // Check that data was sent to the mailer.

        if ( empty($name) OR empty($name_2) OR empty($subject) OR empty($message) OR empty($phone) OR empty($company) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            // Set a 400 (bad request) response code and exit.

            http_response_code(400);

            echo "Please complete the form and try again.";

            exit;

        }

        // Set the recipient email address.

        // FIXME: Update this to your desired email address.

        $recipient = "alexie.popescu2019@yahoo.com";



        // Set the email subject.

        $sender = "New contact from $name";



        //Email Header

        $head = " /// Theme_Pure \\\ ";



        // Build the email content.

        $email_content = "$head\n\n\n";

        if(isset($_POST["name"])){
            $email_content .= "Name: $name\n";
        }

        if(isset($_POST["name"]) AND isset($_POST["l_name"])){
            $email_content .= "Name: {$name} {$name_2}\n";
        }

        
        $email_content .= "Email: $email\n\n";

        // IF SET SUBJECT
        if(isset($_POST["subject"])){
            $email_content .= "Subject: $subject\n\n";
        }

        // IF SET COMPANY
        if(isset($_POST["company"])){
            $email_content .= "Company: $subject\n\n";
        }

        // IF SET PHONE
        if(isset($_POST["phone"])){
            $email_content .= "Phone: $phone\n\n";
        }

        // IF SET PHONE
        if(isset($_POST["message"])){
            $email_content .= "Message:\n$message\n";
        }


        // Build the email headers.

        if(isset($_POST["name"])){
            $email_headers = "From: $name <$email>";
        }


        if(isset($_POST["name"]) AND isset($_POST["l_name"])){
            $email_headers = "From: $name $name_2 <$email>";
        }
        



        // Send the email.


        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'mail.cab-it.ro';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'site@cab-it.ro';                     //SMTP username
            $mail->Password   = 'Alexie1981*#';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;       
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;                             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('site@cab-it.ro', 'site@cab-it.ro');
            $mail->addAddress('contact@cab-it.ro');     //Add a recipient
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Mesaj nou din site';
            $mail->Body    = '<b>Nume: '.$name." ".$name_2.'</b><br/><b>Email: '.$email.'</b><br/><b>Telefon: </b>'.$phone.'<br/><br/><b>Mesaj:</b><br/><br/>'.$message;
        
            $mail->send();
            // header('Location: email_sent.html');
            echo "Email Trimis cu succes!";
        } catch (Exception $e) {
            // header('Location: email_error.html');
            echo "Ne pare rau a aparut o eroare neasteptata!";
        }



        // if (mail($recipient, $sender, $email_content, $email_headers)) {

        //     // Set a 200 (okay) response code.

        //     http_response_code(200);

        //     echo "Thank You! Your message has been sent.";

        // } else {

        //     // Set a 500 (internal server error) response code.

        //     http_response_code(500);

        //     echo "Oops! Something went wrong and we couldn't send your message.";

        // }



    } else {

        // Not a POST request, set a 403 (forbidden) response code.

        http_response_code(403);

        echo "There was a problem with your submission, please try again.";

    }



?>

