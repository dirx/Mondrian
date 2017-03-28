<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Transform\Format;

use MischiefCollective\ColorJizz\Formats\HSV;
use Trismegiste\Mondrian\Graph\Edge;
use Trismegiste\Mondrian\Graph\Vertex;
use Trismegiste\Mondrian\Transform\Vertex\StaticAnalysis;

/**
 * CytoscapeJs is a exporter for cytoscape networks in json format (*.cyjs)
 * It adds cyptoscape extra attributes and values for layout & filtering.
 * @see /doc/resources for importable cytoscape styles for nodes & edges
 */
class CytoscapeJs extends GraphExporter
{
    /**
     * @var \SplObjectStorage
     */
    protected $nodeIdMap;

    public function export()
    {
        $this->buildNodeIdMap();

        $dump = [
            "format_version" => "1.0",
            "generated_by" => "trismegiste/mondrian,v2",
            'elements' => [
                'nodes' => [],
                'edges' => [],
            ],
        ];

        // export nodes
        foreach ($this->graph->getVertexSet() as $id => $vertex) {
            $dump['elements']['nodes'][] = $this->exportVertex($id, $vertex);;
        }

        // export edges
        foreach ($this->graph->getEdgeSet() as $id => $edge) {
            $dump['elements']['edges'][] = $this->exportEdge($id, $edge);
        }

        return json_encode($dump, JSON_PRETTY_PRINT);
    }

    protected function buildNodeIdMap()
    {
        $this->nodeIdMap = new \SplObjectStorage();
        foreach ($this->graph->getVertexSet() as $id => $vertex) {
            $this->nodeIdMap[$vertex] = $id;
        }
    }

    /**
     * @param int $id
     * @param StaticAnalysis|Vertex $vertex
     *
     * @return array
     */
    protected function exportVertex($id, Vertex $vertex)
    {
        $symbolType = $this->resolveSymbolType($vertex);

        switch ($symbolType) {
            case 'interface' :
            case 'class' :
            case 'trait' :
                $label = $this->getShortClassname($vertex->getName());
                $shortName = $this->getShortName($vertex->getName());
                $namespace = $this->getNamespace($vertex->getName());
                $tooltip = $symbolType . ': ' . $vertex->getName();
                break;

            case 'impl' :
            case 'method' :
                list($class, $method) = explode('::', $vertex->getName());
                $label = $this->getShortClassname($class) . "\n" . '::' . $method;
                $shortName = $this->getShortName($class);
                $namespace = $this->getNamespace($class);
                $tooltip = $symbolType . ': ' . $vertex->getName();
                break;

            case 'param':
                preg_match('#(.+)::([^/]+)/(\d+)$#', $vertex->getName(), $capt);
                $label = $capt[3];
                $shortName = $this->getShortName($capt[1]);
                $namespace = $this->getNamespace($capt[1]);
                $tooltip = $symbolType . ': ' . $vertex->getName();
                break;

            default:
                $label = $vertex->getName();
                $shortName = '';
                $namespace = '';
                $tooltip = $symbolType . ': ' . $vertex->getName();
        }

        $attr = $vertex->getAttribute();

        // add meta
        foreach ($vertex->getMetas() as $key => $value) {
            $attr['meta' . ucfirst($key)] = $value;
        }

        if ($vertex->hasMeta('centrality')) {
            $color = new HSV($vertex->getMeta('centrality') * 0.7 * 360, 100, 100);
            $attr['color'] = '#' . $color->toRGB()->toHex();
        }

        $attr = array_merge(
            [
                'id' => 'n' . $id,
            ],
            $attr,
            [
                'type' => $symbolType,
                'label' => $label,
                'tooltip' => $tooltip,
                'shortName' => $shortName,
                'namespace' => $namespace,
            ]
        );

        // map shapes & colors for cytoscape
        $attr['color'] = $this->mapToCytoscapeColor($attr['color']);
        $attr['shape'] = $this->mapToCytoscapeShape($attr['shape']);

        return ['data' => $attr];
    }

    protected function resolveSymbolType(Vertex $vertex)
    {
        preg_match('#\\\\([^\\\\]+)Vertex$#', get_class($vertex), $capt);
        return strtolower($capt[1]);
    }

    protected function getShortClassname($name)
    {
        return $this->getShortNamespace($this->getNamespace($name)) . "\n" . $this->getShortName($name);
    }

    protected function getShortNamespace($namespace)
    {
        $ns = explode('\\', $namespace);
        $prefix = '';
        foreach ($ns as $item) {
            $prefix .= $item[0];
        }
        return $prefix;
    }

    protected function getNamespace($name)
    {
        return substr($name, 0, strrpos($name, '\\'));
    }

    protected function getShortName($name)
    {
        return substr($name, strrpos($name, '\\'));
    }

    protected function mapToCytoscapeColor($color)
    {
        $cytoscapeColors = [
            'green' => 'lime',
            'grey' => 'gray',
        ];

        return isset($cytoscapeColors[$color]) ? isset($cytoscapeColors[$color]) : $color;
    }

    protected function mapToCytoscapeShape($shape)
    {
        $cytoscapeShapes = [
            'invtriangle' => 'hexagon',
        ];

        return isset($cytoscapeShapes[$shape]) ? $cytoscapeShapes[$shape] : $shape;
    }

    /**
     * @param int $id
     * @param Edge $edge
     *
     * @return array
     */
    protected function exportEdge($id, Edge $edge)
    {
        $source = $edge->getSource();
        $target = $edge->getTarget();

        return [
            'data' => [
                'id' => 'e' . $id,
                'source' => 'n' . $this->resolveNodeId($source),
                'target' => 'n' . $this->resolveNodeId($target),
                'interaction' => $relation = $this->resolveInteraction($source, $target),
                'interactionColor' => $this->resolveInteractionColor($relation),
                'weight' => $this->resolveWeight($source, $target),
            ],
        ];
    }

    protected function resolveNodeId(Vertex $vertex)
    {
        return $this->nodeIdMap[$vertex];
    }

    protected function resolveInteraction(Vertex $source, Vertex $target)
    {
        $types = $this->resolveSymbolType($source) . '-' . $this->resolveSymbolType($target);

        switch ($types) {
            case 'class-class':
            case 'interface-interface':
                return 'extends';

            case 'class-interface':
                return 'implements';

            case 'class-trait':
                return 'uses';

            case 'class-method':
            case 'interface-method':
                return 'declares';

            case 'class-impl':
            case 'method-impl':
            case 'trait-impl':
            case 'method-param':
                return 'owns';

            case 'impl-class':
            case 'impl-param':
            case 'impl-trait':
                return 'depends';

            case 'impl-method':
                return 'calls';

            case 'param-class':
            case 'param-interface':
                return 'type-hints';
        }

        return 'unknown';
    }

    protected function resolveInteractionColor($interaction)
    {
        $colors = [
            'extends' => 'LimeGreen',
            'implements' => 'SteelBlue',
            'uses' => 'purple',
            'declares' => 'DarkTurquoise',
            'owns' => 'DarkOrange',
            'depends' => 'darkblue',
            'calls' => 'FireBrick',
            'type-hints' => 'GoldenRod',
        ];

        return isset($colors[$interaction]) ? $colors[$interaction] : 'DarkGray';
    }

    protected function resolveWeight(Vertex $source, Vertex $target)
    {
        $types = $this->resolveSymbolType($source) . '-' . $this->resolveSymbolType($target);

        switch ($types) {
            case 'class-class':
            case 'interface-interface':
                return 2;

            case 'class-interface':
                return 2;

            case 'class-trait':
                return 2;

            case 'class-method':
            case 'interface-method':
                return 2;

            case 'class-impl':
            case 'method-impl':
            case 'trait-impl':
            case 'method-param':
                return 2;

            case 'impl-class':
            case 'impl-param':
            case 'impl-trait':
                return 1;

            case 'impl-method':
                return 1;

            case 'param-class':
            case 'param-interface':
                return 2;
        }

        return 1;
    }
}
