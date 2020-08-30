<?php namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;

class MailerService
{
    public function initializeEmail($container): PHPMailer
    {
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = 'smtp';

        $mail->IsHTML(true);
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->Host = $container->getParameter('mailer.host');
        $mail->Username = $container->getParameter('mailer.user');
        $mail->Password = $container->getParameter('mailer.password');

        return $mail;
    }
}
