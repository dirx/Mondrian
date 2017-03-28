<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\Edge;

use PhpParser\Node;
use PhpParser\Node\Param;
use Trismegiste\Mondrian\Graph\Vertex;
use Trismegiste\Mondrian\Transform\Vertex\ParamVertex;
use Trismegiste\Mondrian\Visitor\State\AbstractObjectLevel;

/**
 * ObjectLevelHelper is
 */
abstract class ObjectLevelHelper extends AbstractObjectLevel
{

    /**
     * Find a class or interface
     *
     * @param string $type fqcn to be found
     *
     * @return Vertex
     */
    protected function findTypeVertex($type)
    {
        foreach (['class', 'interface'] as $pool) {
            $typeVertex = $this->findVertex($pool, $type);
            if (!is_null($typeVertex)) {
                return $typeVertex;
            }
        }

        return null;
    }

    protected function typeHintParam(Param $param, ParamVertex $source)
    {
        if ($param->type instanceof Node\Name) {
            $paramType = (string)$this->context->getState('file')->resolveClassName($param->type);
            // there is a type, we add a link to the type, if it is found
            $typeVertex = $this->findTypeVertex($paramType);
            if (!is_null($typeVertex)) {
                // we add the edge
                $this->getGraph()->addEdge($source, $typeVertex);
            }
        }
    }

}
