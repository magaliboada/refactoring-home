<?php

namespace App\Model;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;


class Scrapper
{
    
    private $id;


    public function __construct() {
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);

        include('../includes/simple_html_dom.php');

        $html = file_get_html('http://www.amazon.co.uk/gp/product/B00AZYBFGY/ref=s9_simh_gw_p86_d0_i1?pf_rd_m=A3P5ROKL5A1OLE&pf_rd_s=center-2&pf_rd_r=1MP0FXRF8V70NWAN3ZWW&pf_r$');


        foreach($html->find('a-section') as $element) {
            echo $element->plaintext . '<br />';
        }

        echo $ret;

        
    }


}