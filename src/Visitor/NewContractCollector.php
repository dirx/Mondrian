<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Trismegiste\Mondrian\Refactor\Refactored;

/**
 * NewContractCollector gather classe which needs to be refactor with a contract.
 *
 * Adds the new interface so changes could be made to the current PhpFile
 */
class NewContractCollector extends PublicCollector
{

    protected $context;

    public function __construct(Refactored $ctx)
    {
        $this->context = $ctx;
    }

    /**
     * {@inheritDoc}
     */
    protected function enterClassNode(Class_ $node)
    {
        $this->extractAnnotation($node);
        if ($node->hasAttribute('contractor')) {
            $interfaceName = new Name(reset($node->getAttribute('contractor')));
            $this->context->pushNewContract($this->getNamespacedName($node), (string)$this->resolveClassName($interfaceName));
            $node->implements[] = $interfaceName;
            $this->currentPhpFile->modified();
        }
    }

    /**
     * do nothing
     */
    protected function enterInterfaceNode(Interface_ $node)
    {

    }

    /**
     * do nothing
     */
    protected function enterPublicMethodNode(ClassMethod $node)
    {

    }

    /**
     * do nothing
     */
    protected function enterTraitNode(Trait_ $node)
    {

    }

}
