<?php

namespace App\Controller;

use App\Entity\Room;
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

        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="room_new", methods={"GET","POST"})
     */
    public function new(Request $request)
    {
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

        foreach ($room->getItems() as &$item) {
            $scraper = new Scraper($item->getLink());
            $item->setPrice($scraper->getPrice());
            $item->setImage($scraper->getImage());
            $item->store = $scraper->getSite();
        }

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

        if ($form->isSubmitted() && $form->isValid()) {

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

            // return $this->redirectToRoute('room_index');
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
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('room_index');
    }
}
