<?php

namespace App\Model;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;
use App\Entity\Item;
use App\Model\Scraper;

// 1. Import the ORM EntityManager Interface
use Doctrine\ORM\EntityManagerInterface;


class CronManager
{

    public function __construct() {
        

        
    }

    public static function refreshNull(EntityManagerInterface $em) {
        // A. Access repositories
        $repo = $em->getRepository("App:Item");
        
        // B. Search using regular methods.
        $nullScraps = $repo->findByNullPrice();

        foreach ($nullScraps as $item) {
            do {
                $scraper = new Scraper($item->getLink());
            } while($scraper->getPrice() == 0);

            $item->setPrice($scraper->getPrice());
            $item->setImage($scraper->getImage());

            // C. Persist and flush
            $em->persist($item);
            $em->flush();
        }
    }

  
}