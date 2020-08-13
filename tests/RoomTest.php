<?php declare(strict_types=1);
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Room;


// require_once '/mnt/c/Users/boada/Documents/VisualStudio/FileSystem/src/File.php';

final class RoomTest extends WebTestCase
{

    public function testCreateRoom(): void
    {  
        $client = static::createClient();

        $client->request('GET', '/post/hello-world');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}