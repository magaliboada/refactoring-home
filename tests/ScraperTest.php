<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Model\Scraper;

// require_once '/mnt/c/Users/boada/Documents/VisualStudio/FileSystem/src/File.php';

final class ScraperTest extends TestCase
{
    public function testScrapper(): void
    {            
        //file with extension
        // $this->assertInstanceOf(
        //     File::class,
        //     $file = File::fromFileString('file.exe')
        // );
        $url = 'https://www.maisonsdumonde.com/ES/es/p/alfombra-de-exterior-blanca-con-estampado-de-follaje-verde-140x200-amazonie-188545.htm';
        $scraper = new Scraper($url);

        // echo var_export($scraper, true);

        $this->assertEquals('file.exe', 'file.exe');

        // //file without extension
        // $file->setFileString('file');
        // $this->assertEquals('file', $file->getFileString());

    }
}
