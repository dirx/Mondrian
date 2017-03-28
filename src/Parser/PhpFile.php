<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Parser;

use PhpParser\Node;
use PhpParser\NodeAbstract;

/**
 * PhpFile is a node in a package repreenting a file
 *
 */
class PhpFile extends NodeAbstract
{
    protected $absPathName;

    /**
     * @var Node[] Statements
     */
    public $stmts;

    public function __construct($path, array $stmts, $newFile = false)
    {
        $this->absPathName = (string)$path;

        parent::__construct(['modified' => $newFile]);
        $this->stmts = $stmts;
    }

    public function getType()
    {
        return 'PhpFile';
    }

    public function getRealPath()
    {
        return $this->absPathName;
    }

    public function isModified()
    {
        return $this->getAttribute('modified');
    }

    public function modified()
    {
        $this->setAttribute('modified', true);
    }

    public function getSubNodeNames()
    {
        return ['stmts'];
    }
}
