<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use App\Service\MailerService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailVerifier extends AbstractController
{
    private $verifyEmailHelper;
    private $mailer;
    private $entityManager;

    public function __construct(
        VerifyEmailHelperInterface $helper,
        MailerInterface $mailer,
        EntityManagerInterface $manager
    ) {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
    }

    /**
     * @Route(name="app_verify_email")
     */

    public function sendEmailConfirmation(
        string $verifyEmailRouteName,
        UserInterface $user,
        TemplatedEmail $email,
        ContainerInterface $container,
        MailerService $mailerService
    ): void {
        $mail = $mailerService->initializeEmail($container);

        $mail->Subject = 'Account Verification';
        $mail->AddAddress($user->getEmail(), $user->getName());
        $mail->addBCC('boadamagali@gmail.com');
        $mail->SetFrom('contact@homerefactor.com', 'Home Refactor');
        $mail->AddReplyTo($mail->Username, $user->getName());

        $emailhash = hash(
            'md5',
            $user->getEmail() .
                $user->getId() .
                $container->getParameter('accountVerify.secretWord'),
            false
        );

        $verifyLink =
            'https://www.homerefactor.com/verify/email/' .
            $emailhash .
            '/' .
            $user->getId();

        $mail->Subject = 'Verify your account!';

        $content = $this->renderView(
            'registration/confirmation_email.html.twig',
            [
                'userName' => $user->getName(),
                'verifyLink' => $verifyLink,
            ]
        );

        $mail->AltBody = $content;
        $mail->Body = $content;
        $mail->isHTML(true);

        if (!$mail->Send()) {
            //   echo "Error while sending Email.";
            //   var_dump($mail);
        } else {
            //   echo "Email sent successfully";
        }
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(
        Request $request,
        UserInterface $user
    ): void {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $request->getUri(),
            $user->getEmail(),
            $user->getEmail()
        );

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
