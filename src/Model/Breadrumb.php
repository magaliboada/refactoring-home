<?php

namespace App\Model;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;
use App\Entity\Item;
use App\Model\Scraper;
use Doctrine\ORM\EntityManagerInterface;


class Breadrumb
{

    public static function generateBC(String $path) : Array
    {
        $itemsBC = [];
        $items = explode($path, '/');

        $currentPath = '/';
        foreach ($items as $item) {
            $currentItem['name'] = '';
            $currentItem['path'] = '';

            $itemsBC[] = $currentItem;
        }
        

        return [];
    }
}