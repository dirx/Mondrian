<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Parser;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Parser\PhpFileBuilder;

/**
 * PhpFileBuilderTest test the builder of PhpFile
 */
class PhpFileBuilderTest extends \PHPUnit_Framework_TestCase
{

    protected $builder;

    public function testEmpty()
    {
        $file = $this->builder->getNode();
        $this->assertInstanceOf(PhpFile::class, $file);
        $this->assertEquals('abc.php', $file->getRealPath());
    }

    public function testNamespace()
    {
        $file = $this->builder->ns('Vertex')->getNode();
        $ns = current($file->stmts);
        $this->assertEquals('Vertex', (string)$ns->name);
    }

    public function testUsing()
    {
        $file = $this->builder->addUse('Nice')->addUse('Sprites')->getNode();
        $using = $file->stmts;
        $this->assertEquals('', (string)$using[0]->name);
        $this->assertEquals('Nice', (string)$using[1]->uses[0]->name);
        $this->assertEquals('Sprites', (string)$using[2]->uses[0]->name);
    }

    public function testClass()
    {
        $file = $this->builder
            ->declaring(new Class_('Scary'))
            ->getNode();
        $using = $file->stmts;
        $this->assertEquals('Scary', (string)$using[1]->name);
    }

    public function testOnlyOneClass()
    {
        $file = $this->builder
            ->declaring(new Class_('Scary'))
            ->declaring(new Class_('Monsters'))
            ->getNode();
        $using = $file->stmts;
        $this->assertEquals('Monsters', (string)$using[1]->name);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Stmt_ClassMethod
     */
    public function testInvalidNodeThrowsException()
    {
        $file = $this->builder
            ->declaring(new ClassMethod('Fail'))
            ->getNode();
    }

    protected function setUp()
    {
        $this->builder = new PhpFileBuilder('abc.php');
    }

}
