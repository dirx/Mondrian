<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

use PhpParser\Node;
use Trismegiste\Mondrian\Graph\Graph;
use Trismegiste\Mondrian\Transform\GraphContext;
use Trismegiste\Mondrian\Transform\ReflectionContext;

/**
 * VisitorContext is a contract for a context of State Pattern
 */
interface VisitorContext
{

    /**
     * Push a new state associated with a node on the stack
     *
     * @param State $stateKey
     *
     * @return State the previous state
     */
    public function pushState($stateKey, Node $node);

    /**
     * @param string $stateKey
     *
     * @return Node
     */
    public function getNodeFor($stateKey);

    /**
     * @param string $stateKey
     *
     * @return State
     */
    public function getState($stateKey);

    /**
     * @return ReflectionContext
     */
    public function getReflectionContext();

    /**
     * @return GraphContext
     */
    public function getGraphContext();

    /**
     * @return Graph
     */
    public function getGraph();
}
