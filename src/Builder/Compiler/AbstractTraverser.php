<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Builder\Compiler;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

/**
 * AbstractTraverser partly builds the compiler with a traverser
 */
abstract class AbstractTraverser implements BuilderInterface
{

    public function buildTraverser(NodeVisitor $collector)
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($collector);

        return $traverser;
    }

}
