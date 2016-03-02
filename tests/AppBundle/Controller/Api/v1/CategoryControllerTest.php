<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 02.03.16
 * Time: 0:12
 */

namespace tests\AppBundle\Controller\Api\v1;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class CategoryControllerTest extends WebTestCase
{

    //protected $crawler;
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->runCommand(['command' => 'doctrine:database:create']);
        $this->runCommand(['command' => 'doctrine:schema:update', '--force' => true]);
        $this->runCommand(['command' => 'hautelook_alice:doctrine:fixtures:load', '--no-interaction' => true]);
    }

    public function tearDown()
    {
        $this->runCommand(['command' => 'doctrine:database:drop', '--force' => true]);
        $this->client = null;
    }

    protected function runCommand(array $arguments = [])
    {
        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);
        $arguments['--quiet'] = true;
        $arguments['-e'] = 'test';
        $input = new ArrayInput($arguments);
        $application->run($input, new ConsoleOutput());
    }

    /**
     * @param $expectedCode
     * @param $route
     * @dataProvider statusCodesProvider
     */
    public function testStatusCodes($expectedCode, $route, $method)
    {
        $this->requestTest($expectedCode, $route, $method);
    }

    public function statusCodesProvider()
    {
        return [
            [200, "/api/v1/categories", "GET"],
            [409, "/api/v1/categories/1", "DELETE"], //can't delete category is not empty
            [204, "/api/v1/categories/3", "DELETE"], //category is empty
            [405, "/api/v1/categories", "DELETE"],
            [405, "/api/v1/categories/1", "POST"],
            [405, "/api/v1/categories", "PUT"],
        ];
    }

    public function testCgetCategory()
    {
        $this->client->request('GET', '/api/v1/categories');
        $content = $this->client->getResponse()->getContent();
        $array = json_decode($content, true);

        $this->assertTrue(3 == count($array));
        foreach ($array as $item) {

            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('id', $item);
        }
    }

    public function testPutCategory()
    {
        $data = array(
            'name' => 'new_name',
        );
        $this->client->request('PUT', '/api/v1/categories/1', $data);
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(204 == $statusCode);

    }

    public function testPostCategory()
    {
        $data = array(
            'name' => 'some_name',
        );
        $this->client->request('POST', '/api/v1/categories', $data);
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(204 == $statusCode);
    }

    protected function requestTest($expectedStatusCode, $path, $method = 'GET')
    {
        //$client = $this->client;
        //$client = static::createClient();

        $crawler = $this->client->request($method, $path);

        $this->assertEquals(
            $expectedStatusCode,
            $this->client->getResponse()->getStatusCode(),
            sprintf(
                'We expected that uri "%s" will return %s status code, but had received %d',
                $path,
                $expectedStatusCode,
                $this->client->getResponse()->getStatusCode()
            )
        );

        return $crawler;
    }
}