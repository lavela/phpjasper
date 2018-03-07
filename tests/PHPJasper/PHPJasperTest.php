<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use PHPJasper\PHPJasper;

final class PHPJasperTest extends TestCase
    /**
     * Class PHPJasperTest
     *
     * @author Rafael Queiroz <rafaelfqf@gmail.com>
     * @author Daniel Rodrigues Lima ( geekcom ) <danielrodrigues-ti@hotmail.com>
     * @package PHPJasper
     */
{
    private $PHPJasper;

    public function setUp()
    {
        $this->PHPJasper = new PHPJasper();
    }

    public function tearDown()
    {
        unset($this->PHPJasper);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(PHPJasper::class, new PHPJasper());
    }

    public function testCompile()
    {
        $result = $this->PHPJasper->compile('{input_file}', '{output_file}');

        $this->assertInstanceOf(PHPJasper::class, $result);
        $this->assertEquals('jasperstarter compile "{input_file}" -o "{output_file}"', $result->output());
    }

    public function testListParameters()
    {
        $result = $this->PHPJasper->listParameters('{input_fille}');

        $this->assertInstanceOf(PHPJasper::class, $result);
        $this->assertEquals('jasperstarter list_parameters "{input_fille}"', $result->output());
    }

}