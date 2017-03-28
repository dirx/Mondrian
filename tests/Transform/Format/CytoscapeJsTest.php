<?php

/*
 * Mondrian
 */

namespace Transform\Format;

use Trismegiste\Mondrian\Graph\Digraph;
use Trismegiste\Mondrian\Tests\Transform\Format\NotPlanar;
use Trismegiste\Mondrian\Transform\Format\CytoscapeJs;

/**
 * CytoscapeJsTest is a test for CytoscapeJs decorator
 */
class CytoscapeJsTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $exporter = new CytoscapeJs(new Digraph());

        $content = json_decode($exporter->export(), true);

        $this->assertArrayHasKey('elements', $content);
        $this->assertArrayHasKey('nodes', $content['elements']);
        $this->assertEmpty($content['elements']['nodes']);
        $this->assertArrayHasKey('edges', $content['elements']);
        $this->assertEmpty($content['elements']['edges']);
    }

    public function testGenerate()
    {
        $exporter = new CytoscapeJs(new NotPlanar());

        $content = json_decode($exporter->export(), true);

        $this->assertArrayHasKey('elements', $content);
        $this->assertArrayHasKey('nodes', $content['elements']);
        $this->assertEquals(5, count($content['elements']['nodes']));
        $this->assertArrayHasKey('edges', $content['elements']);
        $this->assertEquals(5, count($content['elements']['edges']));
    }

    public function testRequiredNodeAttributes()
    {
        $exporter = new CytoscapeJs(new NotPlanar());

        $content = json_decode($exporter->export(), true);

        $this->assertArrayHasKey('data', $content['elements']['nodes'][0]);
        $node = $content['elements']['nodes'][0]['data'];
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('label', $node);
        $this->assertArrayHasKey('shape', $node);
        $this->assertArrayHasKey('color', $node);
    }

    public function testRequiredEdgeAttributes()
    {
        $exporter = new CytoscapeJs(new NotPlanar());

        $content = json_decode($exporter->export(), true);

        $this->assertArrayHasKey('data', $content['elements']['edges'][0]);
        $node = $content['elements']['edges'][0]['data'];
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('source', $node);
        $this->assertArrayHasKey('target', $node);
    }
}
