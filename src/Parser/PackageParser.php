<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Parser;

use PhpParser\Parser;
use Symfony\Component\Finder\SplFileInfo;

/**
 * PackageParser is a parser for multiple files
 */
class PackageParser
{

    protected $fileParser;

    public function __construct(Parser $parser)
    {
        $this->fileParser = $parser;
    }

    public function parse(\Iterator $iter)
    {
        $node = [];
        foreach ($iter as $fch) {
            $node[] = $this->createPhpFileNode($fch);
        }

        return $node;
    }

    protected function createPhpFileNode(SplFileInfo $fch)
    {
        return new PhpFile($fch->getRealPath(), $this->fileParser->parse($fch->getContents()));
    }

}
