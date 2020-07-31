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

use App\Model\Scraper;

/**
 * @Route("/")
 */
class RoomController extends AbstractController
{

    /**
     * @Route("/room", name="room_index", methods={"GET"})
     */
    public function index(RoomRepository $roomRepository): Response
    {
        $user = $this->getUser();
        

        if($user != null){
            return $this->render('room/index.html.twig', [
                'rooms' => $roomRepository->findByUserField($user->getId()),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/new", name="room_new", methods={"GET","POST"})
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
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($room);
            $entityManager->flush();

            
            CronManager::refreshNull($this->getDoctrine()->getManager(), $user->getId());

            return $this->redirectToRoute('room_index');
        }

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="room_show", methods={"GET"})
     */
    public function show(Room $room): Response
    {
        $user = $this->getUser();
        $owner = false;

        // if($user == null) {
        //     return $this->redirectToRoute('app_login');
        // } elseif ($user->getId() != $room->getUserId()) {
        //     return $this->redirectToRoute('room_index');
        // }

        
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
        ]);
    }


    /**
     * @Route("/room/change-room-privacity", name="room_privacity", methods={"GET","POST"})
     */
    public function changePrivacity(Request $request, RoomRepository $roomRepository) : JsonResponse
    {
        if($request->request->get('privacity')){

            $roomArray = $request->request->get('privacity');
            //Look for existing room
            $room = $roomRepository->find($roomArray['room']);
            
            if($room) {                
                $room->setPublic($roomArray['public']);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($room);
                $entityManager->flush();
            }          

            $status = ['output' => $room->getPublic()];
            return new JsonResponse($status);
        }
    }

    /**
     * @Route("/{id}/edit", name="room_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Room $room): Response
    {
        $user = $this->getUser();
        
        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != $room->getUserId()) {
            return $this->redirectToRoute('room_index');
        }        

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {            

            $imageName = $form->get('Image')->getData();
            $room = $this->handleImage($imageName, $room);            

            $this->getDoctrine()->getManager()->flush();
            CronManager::refreshNull($this->getDoctrine()->getManager(), $user->getId());

            return $this->redirectToRoute('room_show', ['id' => $room->getId()]);
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
            return $this->redirectToRoute('room_index');
        }
        
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('room_index');
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
