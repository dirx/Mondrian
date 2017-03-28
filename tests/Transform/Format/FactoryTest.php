<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\Format;

use Trismegiste\Mondrian\Transform\Format\Factory;

/**
 * FactoryTest is a test for factory exporter
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{

    protected $fac;

    protected function setUp()
    {
        $this->fac = new Factory();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalid()
    {
        $this->fac->create($this->createMock('Trismegiste\Mondrian\Graph\Graph'), 'docx');
    }

    public function testValid()
    {
        $formatter = $this->fac->create($this->createMock('Trismegiste\Mondrian\Graph\Graph'), 'dot');
        $this->assertInstanceOf('Trismegiste\Mondrian\Transform\Format\GraphExporter', $formatter);
    }

}
