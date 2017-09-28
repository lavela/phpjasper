<?php

namespace JasperPHP;

class JasperPHPTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateJasperPHP()
    {
        $jasper = new JasperPHP();

        $input = __DIR__ . '/../../../examples/hello_world.jrxml';

        $jasper->compile($input)->execute();
        // do stuff here
    }
}
