<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\Format;

use Trismegiste\Mondrian\Graph\Digraph;
use Trismegiste\Mondrian\Transform\Format\Svg;

/**
 * SvgTest is a test for SvgTest decorator
 */
class SvgTest extends \PHPUnit_Framework_TestCase
{

    public function testExists()
    {
        $exporter = new Svg(new Digraph());
        try {
            $exporter->export();
        } catch (\Exception $e) {

        }
    }

}

