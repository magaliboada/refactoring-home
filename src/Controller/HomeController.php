<?php namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;

use App\Form\RoomType;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/{_locale}", 
     * name="home_index", methods={"GET"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function index(RoomRepository $roomRepository, UserRepository $userRepository, Request $request): Response
    {
        $rooms = $roomRepository->findByPublic();
        foreach ($rooms as &$room) {
            $userRoom = $userRepository->find($room->getUserId());
            $room->username = $userRoom->getName();
            $room->userslug = $userRoom->getUsername();
        }

        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findByPublic(),
            'home' => true,
            'user' => $this->getUser(),
            'locale' => $request->getLocale(),
        ]);
    }

    /**
     * @Route("/room-filter", name="room-filter", methods={"GET","POST"})
     */
    public function roomFilter(Request $request, RoomRepository $roomRepository, UserRepository $userRepository) : JsonResponse
    {
        $rooms = $roomRepository->findByType($request->request->get('type'));
        foreach ($rooms as &$room) {
            $userRoom = $userRepository->find($room->getUserId());
            $room->username = $userRoom->getName();
            $room->userslug = $userRoom->getUsername();
        }

        $html = $this->renderView('room/room-item.html.twig', [
            'rooms' => $rooms,
            'home' => true,
        ]);

        return new JsonResponse(strval($html));        
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // some logic to determine the $locale
        $request->setLocale($locale);
    }
}