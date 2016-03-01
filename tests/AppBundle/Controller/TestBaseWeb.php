<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 01.03.16
 * Time: 0:36
 */

namespace tests\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestBaseWeb extends WebTestCase
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

}