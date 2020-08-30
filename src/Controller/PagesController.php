<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ItemRepository;
use App\Repository\RoomRepository;



class PagesController extends AbstractController
{


    /**
     * @Route("/user/admin", name="admin")
     */
    public function admin(UserRepository $userRepository, RoomRepository $roomRepository, ItemRepository $itemRepository): Response
    {
        $user = $this->getUser();

        if ($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != 1) {
            return $this->redirectToRoute('home_index');
        }

        return $this->render('security/admin.html.twig', [
            'users' => $userRepository->findAll(),
            'rooms' => $roomRepository->findAll(),
            'items' => $itemRepository->findAll(),
        ]);
    }

    // /**
    //  * @Route("/page/selling", name="selling")
    //  */
    // public function selling(): Response
    // {
    //     return $this->render('pages/selling.html.twig', [
    //     ]);
    // }

    /**
     * @Route("/{_locale}/info/terms", 
     * name="terms", 
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function legal(): Response
    {
        return $this->render('legal/terms.html.twig', []);
    }

    /**
     * @Route("/{_locale}/info/privacy", 
     * name="privacy", 
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig', []);
    }

    /**
     * @Route("/{_locale}/not-found", 
     * name="not_found", 
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function notFound(): Response
    {
        return $this->render('legal/not_found.html.twig', []);
    }



}
