<?php

namespace App\Model;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;
use App\Entity\Item;
use App\Model\Scraper;
use Doctrine\ORM\EntityManagerInterface;


class CronManager
{

    public static function refreshNull(EntityManagerInterface $em, $user_id) {
        // A. Access repositories
        $repo = $em->getRepository("App:Item");
        
        if ($user_id != null)
            $nullScraps = $repo->findByNullFieldByUser($user_id);
        else
            $nullScraps = $repo->findByNullField();

        foreach ($nullScraps as $item) {
            do {
                $scraper = new Scraper($item->getLink());
            } while($scraper->getPrice() == 0);

            $item->setPrice($scraper->getPrice());
            $item->setImage($scraper->getImage());
            $item->setStore($scraper->getSite());

            // C. Persist and flush
            $em->persist($item);
            $em->flush();
        }
    }

  
}