<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Parser;

use PhpParser\BuilderAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt;

/**
 * PhpFileBuilder is a builder for a PhpFile node :
 * Enforces the PSR-0 : one class per file
 */
class PhpFileBuilder extends BuilderAbstract
{

    protected $filename;
    protected $fileNamespace = false;
    protected $theClass = null;
    protected $useList = [];

    public function __construct($absPath)
    {
        $this->filename = $absPath;
    }

    public function getNode()
    {
        $stmts = [];
        if ($this->fileNamespace) {
            $stmts[] = $this->fileNamespace;
        }
        if (count($this->useList)) {
            $stmts = array_merge($stmts, $this->useList);
        }
        if (!is_null($this->theClass)) {
            $stmts[] = $this->theClass;
        }

        return new PhpFile($this->filename, $stmts);
    }

    /**
     * Declares a class or an interface
     *
     * @param Node|Builder
     *
     * @return PhpFileBuilder this instance
     *
     * @throws \InvalidArgumentException
     */
    public function declaring($stmt)
    {
        $node = $this->normalizeNode($stmt);
        if (in_array($node->getType(), ['Stmt_Class', 'Stmt_Interface'])) {
            $this->theClass = $node;
        } else {
            throw new \InvalidArgumentException("Invalid node expected type " . $node->getType());
        }

        return $this;
    }

    /**
     * Namespace
     *
     * @param string $str
     *
     * @return $this
     */
    public function ns($str)
    {
        $this->fileNamespace = new Stmt\Namespace_(
            new Node\Name((string)$str));

        return $this;
    }

    /**
     * Add an "use fqcn"
     *
     * @param string $str
     *
     * @return $this
     */
    public function addUse($str)
    {
        $this->useList[] = new Stmt\Use_(
            [
                new Stmt\UseUse(
                    new Node\Name(
                        (string)$str))]);

        return $this;
    }

}
