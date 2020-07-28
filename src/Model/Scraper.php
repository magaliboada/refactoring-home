<?php

namespace App\Model;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;
use App\Model\MyCurl;

class Scraper
{
    
    private $url;
    private $price = 0;
    private $image;
    private $site;

    public function __construct($url) {
        
        $this->url = $url;
        $this->setSite();
        
        if(!empty($this->site)) {
            $this->{'handle'.$this->site}();
        }

        
    }
    
    public static function fromFileString(string $url): self
    {
        return new self($url);
    }

    private function handleAmazon(): void
    {
        $html = $price = '';
        try {
            // $html = file_get_contents($this->url);

            
            $myCurl = new MyCurl($this->url, true, 30, 4, false, true, false);
            $myCurl->createCurl($this->url);
            $html = $myCurl->__tostring();
            echo var_export($html, true);

            $price = $this->get_string_between($html, 'priceBlockBuyingPriceString">', '</span>');
            $price = str_replace(",", ".", $price);
            $this->price = floatval($price);
            $html = $myCurl->__tostring();
            $this->image = $this->get_string_between($html, '"main":{"', '":[');
        

            
        
        } catch (\Throwable $th) {
            //throw $th;
        }

        
    }

    private function handleIkea(): void
    {
        // $this->url = urlencode($this->url);
        $myCurl = new MyCurl($this->url, true, 30, 4, false, true, false);
        $myCurl->createCurl($this->url);
        $html = $myCurl->__tostring();
        $price = $this->get_string_between($html, '<span class="product-pip__price__value">', '</span>');
        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);

        $this->image = $this->get_string_between($html, 'og:image" content="', '" />');
    }

    private function handleLeroy(): void
    {
        // $this->url = urlencode($this->url);

        $myCurl = new MyCurl($this->url, true, 30, 4, false, true, false);
        $myCurl->createCurl($this->url);
        $html = $myCurl->__tostring();
        $price = $this->get_string_between($html, '  &#34;price&#34;: &#34;', '&#34');
        // echo var_export($price, true);
        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);

        
        $this->image = $this->get_string_between($html, '"image_src" href="', '<meta');
        $this->image = str_replace('"/>', '', $this->image);

        // echo var_export($html, true);
    }

    private function handleCasa(): void
    {
        // $this->url = urlencode($this->url);

        $myCurl = new MyCurl($this->url, true, 30, 4, false, true, false);
        $myCurl->createCurl($this->url);
        $html = $myCurl->__tostring();
        $integer = $this->get_string_between($html, '<span class="c-price__base">', '</span>');
        $decimal =  $this->get_string_between($html, '</span> <span class="c-price__mod">', '</span>');
        $price = $integer. ',' . $decimal;
        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);

        $this->image = $this->get_string_between($html, '"image_src" href="', '<meta');
        $this->image = str_replace('"/>', '', $this->image);

        // echo var_export($this->image, true);
    }

    private function handleCorte(): void
    {
        // $this->url = urlencode($this->url);

        $myCurl = new MyCurl($this->url, true, 30, 4, false, true, false);
        $myCurl->createCurl($this->url);
        $html = $myCurl->__tostring();
        $price = $this->get_string_between($html, '<p class="price', '<span class="js-currency">€');
        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);

        // $html = file_get_contents($this->url);
        // $this->image = $this->get_string_between($html, '"image_src" href="', '<meta');
        // $this->image = str_replace('"/>', '', $this->image);

        // echo var_export($this->price, true);
    }

    private function handleMaison(): void
    {
        // $this->url = urlencode($this->url);
        $myCurl = new MyCurl($this->url, true, 30, 4, false, true, false);
        $myCurl->createCurl($this->url);
        $html = $myCurl->__tostring();
        $price = $this->get_string_between($html, 'base-price font-weight-semibold', '</b>');
        $price = $this->get_string_between($price, '>', '€');

        // echo var_export('price: '. $price, true);

        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);
        //<meta property="og:image" content="https://medias.maisonsdumonde.com/image/upload/q_auto,f_auto/w_500/img/farolillo-de-bambu-y-cristal-1000-4-19-195589_1.jpg" ="true">

        $this->image = $this->get_string_between($html, 'og:image" content="', '"><meta data-n-head="');
        $this->image = str_replace('"/>', '', $this->image);

        
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getImage()
    {
        return $this->image;
    }

    private function setSite(): void
    {

        if (strpos($this->url, 'amazon') !== false) {
            $this->site = 'Amazon';
        } elseif (strpos($this->url, 'leroymerlin') !== false) {
            $this->site = 'Leroy';
        } elseif (strpos($this->url, 'ikea') !== false) {
            $this->site = 'Ikea';
        } elseif (strpos($this->url, 'maisonsdumonde') !== false) {
            $this->site = 'Maison';
        } elseif (strpos($this->url, 'corteingles') !== false) {
            $this->site = 'Corte';
        } elseif (strpos($this->url, 'ikea') !== false) {
            $this->site = 'Ikea';
        } else {
            $this->site = '';
        }

    }

    private function get_string_between($string, $start, $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    


}