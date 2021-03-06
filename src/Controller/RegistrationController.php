<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use App\Repository\UserRepository;
use App\Form\UserType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\MailerService;
use Psr\Container\ContainerInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/{_locale}/user/register",
     * name="app_register",  methods={"GET", "POST"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        ContainerInterface $container,
        MailerService $mailerService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setUsername($user->getEmail());
            $user->setIsVerified(false);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(
                        new Address(
                            'contact@homerefactor.com',
                            'Refactoring Home'
                        )
                    )
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig'),
                $container,
                $mailerService
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{_locale}/user/profile",
     * name="profile",  methods={"GET", "POST"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */

    public function profile(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($user == null) {
            return $this->redirectToRoute('app_login');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home_index');
        }

        return $this->render('security/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email/{id}/{user}", name="registration_confirmation_route", methods={"GET"})
     */
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        ContainerInterface $container
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $userRepository->find($request->attributes->get('user'));

        if (
            $request->attributes->get('id') ==
            hash(
                'md5',
                $user->getEmail() .
                    $user->getId() .
                    $container->getParameter('accountVerify.secretWord'),
                false
            )
        ) {
            $user->setIsVerified(true);
            $user->generateUser();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your e-mail address has been verified.'
            );
        }

        return $this->redirectToRoute('app_login');
    }
}
