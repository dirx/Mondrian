<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

use PhpParser\Node;
use Trismegiste\Mondrian\Graph\Graph;
use Trismegiste\Mondrian\Graph\Vertex;
use Trismegiste\Mondrian\Transform\GraphContext;
use Trismegiste\Mondrian\Transform\ReflectionContext;

/**
 * AbstractState is a abstract state
 */
abstract class AbstractState implements State
{

    /** @var VisitorContext */
    protected $context;

    /**
     * @inheritdoc
     */
    public function setContext(VisitorContext $ctx)
    {
        $this->context = $ctx;
    }

    /**
     * @inheritdoc
     */
    public function leave(Node $node)
    {

    }

    /**
     * @return ReflectionContext
     */
    protected function getReflectionContext()
    {
        return $this->context->getReflectionContext();
    }

    /**
     * @return GraphContext
     */
    protected function getGraphContext()
    {
        return $this->context->getGraphContext();
    }

    /**
     * @return Graph
     */
    protected function getGraph()
    {
        return $this->context->getGraph();
    }

    /**
     * Search for a vertex of a given type
     *
     * @param string $type trait|class|interface|param|method|impl
     * @param string $key the key for this vertex
     *
     * @return Vertex
     */
    protected function findVertex($type, $key)
    {
        return $this->context->getGraphContext()->findVertex($type, $key);
    }

}
