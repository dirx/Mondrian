<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Parser;

use PhpParser\BuilderAbstract;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

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
     * @param mixed $stmt a Node_Stmt or a Node_Builder
     *
     * @return \Trismegiste\Mondrian\Parser\PhpFileBuilder this instance
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
     * @return \Trismegiste\Mondrian\Parser\PhpFileBuilder this instance
     */
    public function ns($str)
    {
        $this->fileNamespace = new Namespace_(
            new Name((string)$str));

        return $this;
    }

    /**
     * Add an "use fqcn"
     *
     * @param string $str
     *
     * @return \Trismegiste\Mondrian\Parser\PhpFileBuilder this instance
     */
    public function addUse($str)
    {
        $this->useList[] = new Use_(
            [
                new UseUse(
                    new Name(
                        (string)$str))]);

        return $this;
    }

}
