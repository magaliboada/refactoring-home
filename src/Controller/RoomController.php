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
               

        if($user){
            return $this->render('room/index.html.twig', [
                'rooms' => $roomRepository->findByUserField($user->getId(), 'false'),
                'home' => false,
                'user_name' => $user->getName(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/{id}/rooms", name="user_rooms", methods={"GET"})
     */
    public function userRooms(User $userRoom, RoomRepository $roomRepository): Response
    {
            $user = $this->getUser();
            $owner = false;
              
            
            return $this->render('room/index.html.twig', [
                'rooms' => $roomRepository->findByUserField($userRoom->getId(), 'true'),
                'home' => false,
                'user_name' => $userRoom->getName(),
            ]);
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

        return new JsonResponse('asd');
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
