<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Model\Scraper;

// require_once '/mnt/c/Users/boada/Documents/VisualStudio/FileSystem/src/File.php';

final class ScraperTest extends TestCase
{
    public function testScrapperIkea(): void
    {            
        $url = 'https://www.ikea.com/es/es/p/borgann-cabecero-bomstad-blanco-70465543/';
        $scraper = new Scraper($url);

        // echo var_export($scraper, true);

        $this->assertNotEmpty($scraper->getImage());
        $this->assertNotEmpty($scraper->getPrice());
    }

    public function testScrapperMaison(): void
    {            
        $url = 'https://www.maisonsdumonde.com/ES/es/p/cabecero-de-cama-de-160-de-mango-con-relieve-shepherd-199259.htm';
        $scraper = new Scraper($url);

        // echo var_export($scraper, true);

        $this->assertNotEmpty($scraper->getImage());
        $this->assertNotEmpty($scraper->getPrice());
    }

    public function testScrapperLeroy(): void
    {            
        $url = 'https://www.leroymerlin.es/fp/81962931/pavimento-heritage-33-15x33-15-grey-c3';
        $scraper = new Scraper($url);

        // echo var_export($scraper, true);

        $this->assertNotEmpty($scraper->getImage());
        $this->assertNotEmpty($scraper->getPrice());
    }

    public function testScrapperAmazon(): void
    {            
        $url = 'https://www.amazon.es/murando-Fotomurales-decorativos-Fotogr%C3%A1fico-b-C-0242-j/dp/B07K8VZHM1/ref=pd_sbs_60_2/262-1841589-6485450?_encoding=UTF8&pd_rd_i=B07K8TXNSN&pd_rd_r=f69f9952-0d62-436c-ad57-12450f93152e&pd_rd_w=I8EZA&pd_rd_wg=Baxl5&pf_rd_p=8e0d0316-fa0d-4a75-b68c-17be1e5e1b5a&pf_rd_r=6326TEKJJNS472AZ6GBF&refRID=6326TEKJJNS472AZ6GBF&th=1';
        $scraper = new Scraper($url);

        // echo var_export($scraper, true);

        $this->assertNotEmpty($scraper->getImage());
        $this->assertNotEmpty($scraper->getPrice());
    }
}
