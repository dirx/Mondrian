<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Parser;

use Trismegiste\Mondrian\Parser\BuilderFactory;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Parser\PhpFileBuilder;

/**
 * BuilderFactoryTest tests the enhanced builder factory with PhpFile node
 */
class BuilderFactoryTest extends \PHPUnit_Framework_TestCase
{

    protected $factory;

    protected function setUp()
    {
        $this->factory = new BuilderFactory();
    }

    public function testCreatesBuilder()
    {
        $builder = $this->factory->file('abc.php');
        $this->assertInstanceOf(PhpFileBuilder::class, $builder);
        $default = $builder->getNode();
        $this->assertInstanceOf(PhpFile::class, $default);
        $this->assertEquals('abc.php', $default->getRealPath());
    }

}
