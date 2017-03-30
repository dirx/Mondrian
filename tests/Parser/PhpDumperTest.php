<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Parser;

use PhpParser\Node\Stmt\Class_;
use Trismegiste\Mondrian\Parser\PhpDumper;
use Trismegiste\Mondrian\Parser\PhpFile;

/**
 * PhpDumperTest tests the dumper
 */
class PhpDumperTest extends \PHPUnit_Framework_TestCase
{

    public function getNode()
    {
        return [[
            sys_get_temp_dir() . '/2del' . time() . '.php',
            new Class_('Trash'),
        ]];
    }

    /**
     * @dataProvider getNode
     */
    public function testWrite($dest, $node)
    {
        $dump = new PhpDumper();
        $dump->write(new PhpFile($dest, [$node]));
        $this->assertFileExists($dest);
    }

}
