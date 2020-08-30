<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\RoomRepository;
use App\Service\MailerService;
use Psr\Container\ContainerInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/{_locale}/user/login",
     * name="app_login",  methods={"GET", "POST"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/{_locale}/info/contact",
     * name="contact",
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function contact(
        Request $request,
        ContainerInterface $container,
        MailerService $mailerService
    ): Response {
        $response = '';

        if (strlen($request->request->get('email')) > 0) {
            $mail = $mailerService->initializeEmail($container);
            $mail->AddAddress('refactorhome@gmail.com', 'recipient-name');
            $mail->SetFrom('contact@homerefactor.com', 'Home Refactor');
            $mail->AddReplyTo($request->request->get('email'), 'Reply to');
            $mail->Subject = $request->request->get('subject');

            $content =
                '<p> Name: ' .
                $request->request->get('name') .
                '<p>' .
                '<p> Email: ' .
                $request->request->get('email') .
                '<p>' .
                '<p> Message: ' .
                $request->request->get('message') .
                '<p>';

            $mail->MsgHTML($content);

            if (!$mail->Send()) {
                $response =
                    '<div class="alert alert-danger text-center">The message was not sent.</div>';
            } else {
                $formFields = ['name', 'email', 'subject', 'agree'];

                foreach ($formFields as $field) {
                    $request->request->set($field, '');
                }

                $response =
                    '<div class="alert alert-success text-center">The message was sent.</div>';
            }
        }

        // $message = $mailerService->getHappyMessage();

        return $this->render('legal/contact.html.twig', [
            'message' => $response,
        ]);
    }

    /**
     * @Route("/user/{id}/delete", name="delete_user", methods={"GET"})
     */
    public function delete(
        User $userSelected,
        RoomRepository $roomRepository,
        ItemRepository $itemRepository,
        TokenStorageInterface $tokenStorage,
        Request $request
    ): Response {
        $user = $this->getUser();

        if ($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif (
            $user->getId() != 1 &&
            $user->getId() != $userSelected->getId()
        ) {
            return $this->redirectToRoute('home_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $rooms = $roomRepository->findByUserField(
            $userSelected->getId(),
            false
        );

        foreach ($rooms as $room) {
            $itemRepository->deleteByRoom($room);
        }

        $roomRepository->deleteByUser($userSelected->getId());
        $entityManager->flush();

        $entityManager->remove($userSelected);
        $entityManager->flush();

        if ($user->getId() == 1) {
            return $this->redirectToRoute('admin');
        } else {
            $tokenStorage->setToken(null);
            return $this->redirectToRoute('app_login');
        }
    }

    public function encodePassword($raw, $salt)
    {
        if (!in_array($this->algorithm, hash_algos(), true)) {
            throw new \LogicException(
                sprintf(
                    'The algorithm "%s" is not supported.',
                    $this->algorithm
                )
            );
        }

        //$salted = $this->mergePasswordAndSalt($raw, $salt);
        $salted = $salt . $raw;
        $digest = hash($this->algorithm, $salted, true);

        // "stretch" hash
        for ($i = 1; $i < $this->iterations; ++$i) {
            $digest = hash($this->algorithm, $digest . $salted, true);
        }

        return $this->encodeHashAsBase64
            ? base64_encode($digest)
            : bin2hex($digest);
    }

    /**
     * @Route("/{_locale}/user/logout",
     * name="app_logout",
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function logout()
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
