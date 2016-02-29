<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 29.02.16
 * Time: 17:52
 */

namespace tests\AppBundle\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;


class ProductControllerTest extends WebTestCase
{
    protected $crawler;
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

    public function testGetProduct()
    {
        $this->client->request('GET', '/api/v1/products/1');
        $json = $this->client->getResponse()->getContent();

        $array = json_decode($json, true);

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('category', $array);
        $this->assertArrayHasKey('price', $array);
        $this->assertArrayHasKey('description', $array);
    }

    public function testCgetProduct()
    {
        $this->client->request('GET', '/api/v1/products');
        $content = $this->client->getResponse()->getContent();
        $array = json_decode($content, true);

        $this->assertTrue(4 == count($array));
        foreach ($array as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('category', $item);
            $this->assertArrayHasKey('price', $item);
            $this->assertArrayHasKey('description', $item);
        }
    }

    public function testPutProduct()
    {
        $data = array(
            'name' => 'new_name',
            'price' => 111
        );
        $this->client->request('PUT', '/api/v1/products/1', $data);
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(204 == $statusCode);

    }

    public function testPostProduct()
    {
        $data = array(
            'name' => 'some_name',
            'price' => 500,
            'category' => 'testcategory1',
            'description' => 'some description',
        );
        $this->client->request('POST', '/api/v1/products', $data);
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(204 == $statusCode);
    }

    public function statusCodesProvider()
    {
        return [
            [200, "/api/v1/products", "GET"],
            [200, "/api/v1/products/1", "GET"],
            [200, "/api/v1/products/2", "GET"],
            [400, "/api/v1/products/100", "GET"],
            [400, "/api/v1/products/0", "GET"],
            [405, "/api/v1/products/1", "POST"],
            [405, "/api/v1/products", "PUT"],
            [405, "/api/v1/products", "DELETE"],
            [204, "/api/v1/products/2", "DELETE"],
        ];
    }

    protected function requestTest($expectedStatusCode, $path, $method = 'GET')
    {
        $client = static::createClient();

        $crawler = $client->request($method, $path);

        $this->assertEquals(
            $expectedStatusCode,
            $client->getResponse()->getStatusCode(),
            sprintf(
                'We expected that uri "%s" will return %s status code, but had received %d',
                $path,
                $expectedStatusCode,
                $client->getResponse()->getStatusCode()
            )
        );

        return $crawler;
    }
}