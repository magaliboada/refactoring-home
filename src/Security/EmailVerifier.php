<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class EmailVerifier
{
    private $verifyEmailHelper;
    private $mailer;
    private $entityManager;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
    }

    /**
     * @Route(name="app_verify_email")
     */

    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = "smtp";

        $mail->SMTPDebug  = 1;  
        $mail->SMTPAuth   = TRUE;
        $mail->SMTPSecure = "tls";
        $mail->Port       = 587;
        $mail->Host       = "smtp.ionos.es";
        

        $mail->IsHTML(true);
        $mail->AddAddress($user->getEmail(), "recipient-name");
        $mail->SetFrom($mail->Username, "Home Refactor");
        $mail->AddReplyTo($mail->Username, "reply-to-name");

        $emailhash = hash('md5', $user->getEmail().$user->getId(), false);

        $verifyLink = 'https://www.homerefactor.com/verify/email/'.$emailhash.'/'.$user->getId();
        
        $mail->Subject = "Verify your account!";
        $content = 
        
        
        '<h1>Hi! Please confirm your email!</h1>

        <p>
            Please confirm your email address by clicking the following link: <br><br>
            '. $verifyLink  .' <br><br>
        </p>

        <p>
            Cheers!
        </p>';


        $mail->AltBody = $content;
        $mail->Body = $content;
        $mail->isHTML(true);



        if(!$mail->Send()) {
        //   echo "Error while sending Email.";
        //   var_dump($mail);
        } else {
        //   echo "Email sent successfully";
        };

    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getEmail(), $user->getEmail());

        $user->setIsVerified(true);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
