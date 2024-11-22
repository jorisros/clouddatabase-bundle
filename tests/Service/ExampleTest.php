<?php

namespace JorisRos\CloudDatabaseBundle\Tests\Service;

require_once(__DIR__ . '/../../vendor/autoload.php');

use JorisRos\CloudDatabaseBundle\Service\Example;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testMultiply(): void
    {
        $example = new Example();
        $this->assertEquals($example->multiply(4), 16);
    }
}
