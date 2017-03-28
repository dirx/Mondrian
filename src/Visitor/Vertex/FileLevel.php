<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\Vertex;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Trismegiste\Mondrian\Transform\Vertex\ClassVertex;
use Trismegiste\Mondrian\Transform\Vertex\InterfaceVertex;
use Trismegiste\Mondrian\Transform\Vertex\TraitVertex;
use Trismegiste\Mondrian\Visitor\State\FileLevelTemplate;

/**
 * FileLevel is a visitor for file level to add vertex of class, trait, interface
 */
class FileLevel extends FileLevelTemplate
{

    protected function enterClassNode(Stmt\Class_ $node)
    {
        $this->factoryPrototype($node, 'class', ClassVertex::class);
    }

    protected function enterInterfaceNode(Stmt\Interface_ $node)
    {
        $this->factoryPrototype($node, 'interface', InterfaceVertex::class);
    }

    protected function enterTraitNode(Stmt\Trait_ $node)
    {
        $this->factoryPrototype($node, 'trait', TraitVertex::class);
    }

    private function factoryPrototype(Stmt $node, $type, $vertexClass)
    {
        $index = $this->getNamespacedName($node);

        if (!$this->getGraphContext()->existsVertex($type, $index)) {
            $factory = new \ReflectionClass($vertexClass);
            $v = $factory->newInstance($index);
            $this->getGraph()->addVertex($v);
            $this->getGraphContext()->indicesVertex($type, $index, $v);
        }
    }

}
