<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\Vertex;

use Trismegiste\Mondrian\Graph\Graph;
use Trismegiste\Mondrian\Transform\GraphContext;
use Trismegiste\Mondrian\Transform\ReflectionContext;
use Trismegiste\Mondrian\Visitor\State\PackageLevel;
use Trismegiste\Mondrian\Visitor\VisitorGateway;

/**
 * Collector is the main visitor for creating vertices for each relevant nodes
 */
class Collector extends VisitorGateway
{

    public function __construct(ReflectionContext $ref, GraphContext $grf, Graph $g)
    {
        $visitor = [
            new PackageLevel(),
            new FileLevel(),
            new ClassLevel(),
            new InterfaceLevel(),
            new TraitLevel(),
        ];

        parent::__construct($visitor, $ref, $grf, $g);
    }

}
