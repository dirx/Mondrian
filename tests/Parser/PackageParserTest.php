<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Parser;

use PhpParser\Parser\Php7;
use Trismegiste\Mondrian\Parser\PackageParser;
use Trismegiste\Mondrian\Parser\PhpFile;

/**
 * PackageParserTest tests a parser of Package
 */
class PackageParserTest extends \PHPUnit_Framework_TestCase
{

    protected $package;
    protected $parser;

    public function getListing()
    {
        return [[[new \Trismegiste\Mondrian\Tests\Fixtures\MockSplFileInfo('abc', 'dummy')]]];
    }

    /**
     * @dataProvider getListing
     */
    public function testScanning($listing)
    {
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('dummy'))
            ->will($this->returnValue([]));

        $ret = $this->package->parse(new \ArrayIterator($listing));

        $this->assertCount(1, $ret);
        $this->assertInstanceOf(PhpFile::class, $ret[0]);
    }

    protected function setUp()
    {
        $this->parser = $this->getMockBuilder(Php7::class)
            ->disableOriginalConstructor()
            ->setMethods(['parse'])
            ->getMock();
        $this->package = new PackageParser($this->parser);
    }

}
