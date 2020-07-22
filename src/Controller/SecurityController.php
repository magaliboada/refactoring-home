<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ItemRepository;
use App\Repository\RoomRepository;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SecurityController extends AbstractController
{
    /**
     * @Route("/user/login", name="app_login")
     */

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('room_index');
        }
    
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
    

    /**
     * @Route("/user/admin", name="admin")
     */
    public function admin(UserRepository $userRepository, RoomRepository $roomRepository, ItemRepository $itemRepository): Response
    {
        $user = $this->getUser();

        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != 1) {
            return $this->redirectToRoute('room_index');
        }

        return $this->render('security/admin.html.twig', [
            'users' => $userRepository->findAll(),
            'rooms' => $roomRepository->findAll(),
            'items' => $itemRepository->findAll(),
        ]);

    }

    /**
     * @Route("/info/terms", name="terms")
     */
    public function legal(): Response
    {
        return $this->render('legal/terms.html.twig', [
        ]);
    }

    /**
     * @Route("/info/privacy", name="legal")
     */
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig', [
        ]);
    }

     /**
     * @Route("/info/contact", name="terms")
     */
    public function contact(Request $request): Response
    {
        $response = '';

        if( strlen($request->request->get('email')) > 0) {
            // $formFields = ['name', 'email', 'subject', 'agree'];
            // foreach ($formFields as $field) {
            //     echo var_export($request->request->get($field));
            // }

            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->Mailer = "smtp";

            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "ssl";
            $mail->Port       = 465 ;
            

            $mail->IsHTML(true);
            $mail->AddAddress('refactorhome@gmail.com', "recipient-name");
            $mail->SetFrom("contact@homerefactor.com", "Home Refactor");
            $mail->AddReplyTo($request->request->get('email'), "Reply to");
            $mail->Subject = $request->request->get('subject');
            
            $content = 
            "<p> Name: " . $request->request->get('name') . "<p>" .
            "<p> Email: " . $request->request->get('email') . "<p>" .
            "<p> Message: " . $request->request->get('message') . "<p>"            
            ;

            $mail->MsgHTML($content); 
            if(!$mail->Send()) {
            echo "Error while sending Email.";
                $response = '<div class="alert alert-danger text-center">The message was not sent.</div>';
            } else {
                $response = '<div class="alert alert-success text-center">The message was sent.</div>';
            };

            
        }



        // if ($form->isSubmitted() && $form->isValid()) { 

        
        // }

        // $response = 'Message sent successfully.';

        return $this->render('legal/contact.html.twig', [
            'message' => $response,
        ]);
    }


    /**
     * @Route("/user/{id}/delete", name="delete_user", methods={"GET"})
     */
    public function delete(User $userSelected, RoomRepository $roomRepository, TokenStorageInterface$tokenStorage, Request $request): Response
    {
        $user = $this->getUser();

        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != 1 && $user->getId() != $userSelected->getId()) {
            return $this->redirectToRoute('room_index');
        }

        $roomRepository->deleteByUser($user->getId());      

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($userSelected);
        $entityManager->flush();

        if  ($user->getId() == 1)
            return $this->redirectToRoute('admin');
        else{
            $tokenStorage->setToken(null);
            return $this->redirectToRoute('app_login');
        }
    }


    /**
     * @Route("/user/test-mail", name="test")
     */
    public function sendEmail()
    {
        // $user = $this->getUser();

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = "smtp";

        $mail->SMTPDebug  = 1;  
        $mail->SMTPAuth   = TRUE;
        $mail->SMTPSecure = "ssl";
        $mail->Port       = 465 ;

        $mail->IsHTML(true);
        $mail->AddAddress('refactorhome@gmail.com', "recipient-name");
        $mail->SetFrom("contact@homerefactor.com", "Home Refactor");
        // $mail->AddReplyTo($mail->Username, "reply-to-name");
        $mail->Subject = "Verify your account!";
        $content = "<h1>Hi! Please confirm your email!</h1>";

        $mail->MsgHTML($content); 
        if(!$mail->Send()) {
          echo "Error while sending Email.";
        //   var_dump($mail);
        } else {
          echo "Email sent successfully";
        };
        
        if(false)
            return $this->redirectToRoute('admin');
    }

    public function encodePassword($raw, $salt)
    {

        if (!in_array($this->algorithm, hash_algos(), true)) {
            throw new \LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
        }

        //$salted = $this->mergePasswordAndSalt($raw, $salt);
        $salted = $salt.$raw;
        $digest = hash($this->algorithm, $salted, true);

        // "stretch" hash
        for ($i = 1; $i < $this->iterations; ++$i) {
            $digest = hash($this->algorithm, $digest.$salted, true);
        }

        return $this->encodeHashAsBase64 ? base64_encode($digest) : bin2hex($digest);
    }

    /**
     * @Route("/user/logout", name="app_logout")
     */
    public function logout()
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
