<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\Format;

use Trismegiste\Mondrian\Graph\Graph;
use Trismegiste\Mondrian\Transform\Format\Factory;
use Trismegiste\Mondrian\Transform\Format\GraphExporter;

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
     * @expectedException \InvalidArgumentException
     */
    public function testInvalid()
    {
        $this->fac->create($this->createMock(Graph::class), 'docx');
    }

    public function testValid()
    {
        $formatter = $this->fac->create($this->createMock(Graph::class), 'dot');
        $this->assertInstanceOf(GraphExporter::class, $formatter);
    }

}
