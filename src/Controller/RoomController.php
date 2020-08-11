<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;

use App\Form\RoomType;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Model\CronManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Model\Scraper;
use App\Repository\UserRepository;

/**
 * @Route("/")
 */
class RoomController extends AbstractController
{

    /**
     * @Route("/{_locale}/u/{username}", 
     * name="user_rooms", methods={"GET"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function userRooms(String $username, RoomRepository $roomRepository, UserRepository $userRepository, Request $request): Response
    {
        $userRoom = $userRepository->findByUsername($username);
        $user = $this->getUser();

        $owner = false;

        $rooms =  $roomRepository->findByUserField($userRoom->getId(), 'true');
        foreach ($rooms as &$room) {
            $userRoom = $userRepository->find($room->getUserId());
            $room->username = $userRoom->getName();
            $room->userslug = $userRoom->getUsername();
        }

        if ($user && $user->getId() == $userRoom->getId()) {
            $owner = true;
        }

        

        return $this->render('room/index.html.twig', [
            'rooms' => $rooms,
            'home' => false,
            'user_name' => $userRoom->getName(),
            'owner' => $owner,
            // 'locale' => $event->getRequest(),
        ]);
    }

     /**
     * @Route("/{_locale}/room/new", 
     * name="room_new",  methods={"GET","POST"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function new(Request $request)
    {
        $user = $this->getUser();

        if($user == null) {
            return $this->redirectToRoute('app_login');
        } 

        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $imageName = $form->get('Image')->getData();
            
            $room = $this->handleImage($imageName, $room);  
            $room->setUserId($user->getId());
            $room->setPublic(false);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($room);
            $entityManager->flush();

            
            CronManager::refreshNull($this->getDoctrine()->getManager(), $user->getId());

            return $this->redirectToRoute('home_index');
        }

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{_locale}/u/{username}/rooms/{id}", 
     * name="room_show",  methods={"GET"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function show(String $username, Room $room, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $owner = false;
        $userRoom = $userRepository->findByUsername($username);
        
        if ($user && $user->getId() == $room->getUserId()) {
            $owner = true;
        }
        

        $items = $room->getItems()->toArray();
         // Asc sort
         usort($items, function($first, $second) {
            return strtolower($first->getName()) > strtolower($second->getName());
        });

        $room->setItems($items);

        // Asc sort
        
        return $this->render('room/show.html.twig', [
            'room' => $room,
            'owner' => $owner,
            'user' => $userRoom,
        ]);
    }




    /**
     * @Route("/room/change-field", name="room_change", methods={"GET","POST"})
     */
    public function updateRoom(Request $request, RoomRepository $roomRepository) : JsonResponse
    {        
        if($request->request->get('value_change')){

            $roomArray = $request->request->get('value_change');
            //Look for existing room
            $room = $roomRepository->find($roomArray['room']);
            
            if($room) {                
                $room->{'set'.$roomArray['field']}($roomArray['value']);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($room);
                $entityManager->flush();
            }          

            $status = ['output' => $room->getPublic()];
            
        }

        return new JsonResponse('');
    }

    /**
     * @Route("/room/upload-image", name="upload_image", methods={"GET","POST"})
     */
    public function uploadImage(Request $request, RoomRepository $roomRepository) : JsonResponse
    {
        $file = $request->files->get('file');
        $status = array('status' => "success","fileUploaded" => false);
       
        // If a file was uploaded
        if($file){
            $room = $roomRepository->find($request->request->get('room'));
            
            if($room) {                

                $filename = uniqid().".".$file->getClientOriginalExtension();
                $path = 'images'.'/';
                $file->move($path,$filename); // move the file to a path
                $status = array('status' => "success","fileUploaded" => true);


                $room->setImage($path.$filename);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($room);
                $entityManager->flush();
            }             
        }
        
        // return new JsonResponse($status);
        return new JsonResponse($path.$filename);
    }

    /**
     * @Route("/{_locale}/u/{username}/rooms/{id}/edit", 
     * name="room_edit",  methods={"GET", "POST"},
     * requirements={
     *         "_locale": "en|es",
     *     })
     */
    public function edit(Request $request, Room $room): Response
    {
        $user = $this->getUser();
        
        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != $room->getUserId() || $user->getId() != 1) {
            return $this->redirectToRoute('home_index');
        }        

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {            

            $imageName = $form->get('Image')->getData();
            $room = $this->handleImage($imageName, $room);            

            $this->getDoctrine()->getManager()->flush();
            CronManager::refreshNull($this->getDoctrine()->getManager(), $user->getId());

            return $this->redirectToRoute('room_show', ['id' => $room->getId(), 'username' =>$user->getUsername()]);
        }


        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="room_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Room $room): Response
    {
        $user = $this->getUser();

        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != $room->getUserId()) {
            return $this->redirectToRoute('home_index');
        }
        
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home_index');
    }

    public function handleImage($imageName, Room $room) {
        if(isset($imageName))
            {    
                $originalFilename = pathinfo($imageName->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageName->guessExtension();
            
                // Move the file to the directory where images are stored
                try {
                    $imageName->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    $fullPath = 'images'.'/'.$newFilename;
                    $room->setImage($fullPath);

                    
                } catch (FileException $e) {
                    echo var_export( $e, true);
                }
            }

        return $room;
    }
   
}
