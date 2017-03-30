<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\BuildGraph;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\PrettyPrinter\Standard;

/**
 * CallingTest tests edges for method calls
 */
class CallingTest extends GraphBuilderTestCase
{

    public function testStaticCall()
    {
        $fac = $this->createFactory();
        $package[0] = $fac->file('sample1.php')
            ->ns('Project')
            ->declaring(
                $fac->class('Helper')
                    ->addStmt($fac->method('purr')->makeStatic()->makePublic())
            )->getNode();

        $package[1] = $fac->file('sample2.php')
            ->ns('Kitty')
            ->declaring(
                $fac->class('Service')
                    ->addStmt($fac->method('run')
                        ->addStmt(
                            new StaticCall(
                                new FullyQualified('Project\Helper'), 'purr')))
            )->getNode();

        $this->graph->expects($this->exactly(7))->method('addEdge');
        $this->expectsAddEdge(12, 'impl', 'Kitty\Service::run', 'method', 'Project\Helper::purr');

        $this->compile($package);
    }

    protected function dump($f)
    {
        $stmt = iterator_to_array($f->getIterator());
        $pp = new Standard();
        echo $pp->prettyPrint($stmt);
    }

}
