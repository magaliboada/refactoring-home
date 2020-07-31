<?php namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;

use App\Form\RoomType;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home_index", methods={"GET"})
     */
    public function index(RoomRepository $roomRepository, UserRepository $userRepository): Response
    {
        $rooms = $roomRepository->findByPublic();
        foreach ($rooms as &$room) {
            $userRoom = $userRepository->find($room->getUserId());
            $room->username = $userRoom->getName();
        }
        

        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findByPublic(),
            'home' => true,
        ]);
    }
}