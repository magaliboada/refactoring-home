<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;

use Symfony\Component\Security\Core\User\UserInterface;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Model\Scraper;

/**
 * @Route("/")
 */
class RoomController extends AbstractController
{

    /**
     * @Route("/", name="room_index", methods={"GET"})
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
            if(isset($imageName))
            {    
                $originalFilename = pathinfo($imageName->getClientOriginalName(), PATHINFO_FILENAME);
                // $safeFilename = $slugger->slug($originalFilename);
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

            $room->setUserId($user->getId());
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($room);
            $entityManager->flush();

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

        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != $room->getUserId()) {
            return $this->redirectToRoute('room_index');
        }

        

        // Asc sort
        
        return $this->render('room/show.html.twig', [
            'room' => $room,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="room_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Room $room): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        $user = $this->getUser();
        
        if($user == null) {
            return $this->redirectToRoute('app_login');
        } elseif ($user->getId() != $room->getUserId()) {
            return $this->redirectToRoute('room_index');
        }

        

        if ($form->isSubmitted() && $form->isValid()) {
            
            

            foreach ($room->getItems() as &$item) {
                $item = $this->refreshScraperValues($item);
            }

            $items = $room->getItems()->toArray();
    
            usort($items, function($first, $second) {
                return strtolower($first->getName()) > strtolower($second->getName());
            });

            $imageName = $form->get('Image')->getData();
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

            

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('room_show', ['id' => $room->getId()]);

        }

        $items = $room->getItems()->toArray();
         // Asc sort
         usort($items, function($first, $second) {
            return strtolower($first->getName()) > strtolower($second->getName());
        });

        $room->setItems($items);

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

    public function refreshScraperValues($item) {
        
        $scraper = new Scraper($item->getLink());

        if ($scraper->getImage() != '') 
            $item->setImage($scraper->getImage());

        if ($scraper->getPrice() != 0)
            $item->setPrice($scraper->getPrice());
            
        $item->setStore($scraper->getSite());        

        return $item;
    }
}
