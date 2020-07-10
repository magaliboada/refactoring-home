<?php

namespace App\Model;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;

class Scraper
{
    
    private $url;
    private $price;
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
        // $this->url = urlencode($this->url);

        $html = $price = '';
        try {
            $html = file_get_contents($this->url);
        $price = $this->get_string_between($html, '<span id="priceblock_ourprice" class="a-size-medium a-color-price priceBlockBuyingPriceString">', '</span>');
        $price = str_replace(",", ".", $price);
        

        $html = file_get_contents($this->url);
        
        } catch (\Throwable $th) {
            //throw $th;
        }

        $this->price = floatval($price);
        $this->image = $this->get_string_between($html, 'data-old-hires="', '" onload');
    }

    private function handleIkea(): void
    {
        // $this->url = urlencode($this->url);

        $html = file_get_contents($this->url);
        $price = $this->get_string_between($html, '<span class="product-pip__price__value">', '</span>');
        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);

        $html = file_get_contents($this->url);
        $this->image = $this->get_string_between($html, 'og:image" content="', '" />');
    }

    private function handleLeroy(): void
    {
        // $this->url = urlencode($this->url);

        $html = file_get_contents($this->url);
        $price = $this->get_string_between($html, '<div class="price ">', '</span>');
        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);

        $html = file_get_contents($this->url);
        $this->image = $this->get_string_between($html, '"image_src" href="', '<meta');
        $this->image = str_replace('"/>', '', $this->image);

        // echo var_export($this->image, true);
    }

    private function handleMaison(): void
    {
        // $this->url = urlencode($this->url);
        $html = file_get_contents($this->url);
        $price = $this->get_string_between($html, 'base-price font-weight-semibold', '</b>');
        $price = $this->get_string_between($price, '>', '€');

        // echo var_export('price: '. $price, true);

        $price = str_replace(",", ".", $price);
        $this->price = floatval($price);
        //<meta property="og:image" content="https://medias.maisonsdumonde.com/image/upload/q_auto,f_auto/w_500/img/farolillo-de-bambu-y-cristal-1000-4-19-195589_1.jpg" ="true">
        $html = file_get_contents($this->url);
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
        }elseif (strpos($this->url, 'ikea') !== false) {
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