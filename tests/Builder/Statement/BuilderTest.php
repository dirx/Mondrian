<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Builder\Statement;

use Trismegiste\Mondrian\Builder\Statement\Builder;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Tests\Fixtures\MockSplFileInfo;

/**
 * BuilderTest tests the build of the parser
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{

    protected $parser;

    protected function getMockFile($absolute, $content)
    {
        return new MockSplFileInfo(($absolute), $content);
    }

    protected function setUp()
    {
        $this->parser = new Builder();
        $this->parser->buildLexer();
        $this->parser->buildFileLevel();
        $this->parser->buildPackageLevel();
    }

    public function testParsing()
    {
        $iter = new \ArrayIterator([$this->getMockFile('abc', '<?php class abc {}')]);
        $stmt = $this->parser->getParsed($iter);
        $this->assertCount(1, $stmt);
        $this->assertInstanceOf(PhpFile::class, $stmt[0]);
        $content = new \ArrayIterator($stmt[0]->stmts);
        $this->assertCount(2, $content);
        $content->rewind();
        $this->assertEquals('Stmt_Namespace', $content->current()->getType());
        $content->next();
        $this->assertEquals('Stmt_Class', $content->current()->getType());
        $this->assertEquals('abc', $content->current()->name);
    }

}
