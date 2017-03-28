<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\SymbolMap;

use PhpParser\Node\Stmt;
use Trismegiste\Mondrian\Visitor\State\AbstractObjectLevel;

/**
 * TraitUserLevel is a helper for traits users (class & trait) level state
 */
abstract class TraitUserLevel extends AbstractObjectLevel
{

    protected function importSignatureTrait(Stmt\TraitUse $node)
    {
        $fileState = $this->context->getState('file');
        $fqcn = $this->getCurrentFqcn();
        // @todo do not forget aliases
        foreach ($node->traits as $import) {
            $name = (string)$fileState->resolveClassName($import);
            $this->getReflectionContext()->initTrait($name);
            $this->getReflectionContext()->pushUseTrait($fqcn, $name);
        }
    }

}
