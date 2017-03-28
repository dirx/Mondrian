<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Transform\Format;

use Trismegiste\Mondrian\Graph\Graph;

/**
 * Factory is a simple factory for export format for Graph
 */
class Factory
{

    protected $typeList = [
        'dot' => Graphviz::class,
        'json' => Json::class,
        'svg' => Svg::class,
        'html' => Html::class,
        'cyjs' => CytoscapeJs::class,
    ];

    public function create(Graph $graph, $format)
    {
        if (array_key_exists($format, $this->typeList)) {
            $className = $this->typeList[$format];
            return new $className($graph);
        }

        throw new \InvalidArgumentException("Format $format is unknown");
    }
}
