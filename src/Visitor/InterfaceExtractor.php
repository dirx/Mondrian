<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Trismegiste\Mondrian\Parser\PhpPersistence;
use Trismegiste\Mondrian\Refactor\Refactored;

/**
 * InterfaceExtractor builds new contracts
 */
class InterfaceExtractor extends PublicCollector
{

    protected $newInterface = false;
    protected $newContent = null; // a list of PhpFile
    protected $methodStack; // a temporary stack of methods for the currently new interface
    protected $context;
    protected $dumper;

    public function __construct(Refactored $ctx, PhpPersistence $callable)
    {
        $this->context = $ctx;
        $this->dumper = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeTraverse(array $nodes)
    {
        parent::beforeTraverse($nodes);
        $this->newContent = [];
    }

    /**
     * {@inheritDoc}
     */
    protected function enterClassNode(Class_ $node)
    {
        $this->extractAnnotation($node);
        if ($node->hasAttribute('contractor')) {
            $this->newInterface = reset($node->getAttribute('contractor'));
            $this->methodStack = [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node->getType() === 'Stmt_Class') {
            if ($this->newInterface) {
                $this->newContent[] = $this->buildNewInterface();
            }
            $this->newInterface = false;
        }

        parent::leaveNode($node);
    }

    /**
     * {@inheritDoc}
     */
    protected function enterInterfaceNode(Interface_ $node)
    {

    }

    /**
     * {@inheritDoc}
     */
    protected function enterPublicMethodNode(ClassMethod $node)
    {
        // I filter only good relevant methods (no __construct, __clone, __invoke ...)
        if (!preg_match('#^__.+#', $node->name) && $this->newInterface) {
            $this->enterStandardMethod($node);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterTraverse(array $node)
    {
        $this->writeUpdated($node);
        $this->writeUpdated($this->newContent);
    }

    /**
     * do nothing
     */
    protected function enterTraitNode(Node\Stmt\Trait_ $node)
    {

    }

    /**
     * Build the new PhpFile for the new contract
     *
     * @return \Trismegiste\Mondrian\Parser\PhpFile
     * @throws \RuntimeException If no inside a PhpFile (WAT?)
     */
    protected function buildNewInterface()
    {
        if (!$this->currentPhpFile) {
            throw new \RuntimeException('Currently not in a PhpFile therefore no generation');
        }

        $fqcn = new Node\Name\FullyQualified($this->currentClass);
        array_pop($fqcn->parts);
        $generated[0] = new Node\Stmt\Namespace_(new Name($fqcn->parts));
        $generated[1] = new Interface_($this->newInterface, ['stmts' => $this->methodStack]);

        $dst = dirname($this->currentPhpFile->getRealPath()) . '/' . $this->newInterface . '.php';

        return new \Trismegiste\Mondrian\Parser\PhpFile($dst, $generated, true);
    }

    /**
     * Stacks the method for the new interface
     *
     * @param ClassMethod $node
     */
    protected function enterStandardMethod(ClassMethod $node)
    {
        $abstracted = clone $node;
        $abstracted->type = 0;
        $abstracted->stmts = null;

        $this->methodStack[] = $abstracted;
    }

    /**
     * Write a list of PhpFile
     *
     * @param array $fileList
     */
    protected function writeUpdated(array $fileList)
    {
        foreach ($fileList as $file) {
            if ($file->isModified()) {
                $this->dumper->write($file);
            }
        }
    }
}
