<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Trismegiste\Mondrian\Parser\PhpPersistence;

/**
 * NewInstanceRefactor is a generator of method for each new instance
 */
class NewInstanceRefactor extends PublicCollector
{

    protected $currentMethodRelevant = false;
    protected $factoryMethodStack;
    protected $dumper;
    protected $currentClassStmts;

    /**
     * The ctor needs a service for persistence of modified files
     *
     * @param PhpPersistence $callable
     */
    public function __construct(PhpPersistence $callable)
    {
        $this->dumper = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if (($node->getType() == 'Expr_New') && $this->currentMethodRelevant) {
            return $this->enterNewInstance($node);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        switch ($node->getType()) {

            case 'Stmt_Stmt':
                $this->currentMethodRelevant = false;
                break;

            case 'Stmt_Class':
                // generate
                foreach ($this->factoryMethodStack as $name => $calling) {
                    $factory = new Stmt\ClassMethod($name);
                    $factory->type = Stmt\Class_::MODIFIER_PROTECTED;
                    $factory->params = $this->getProcessedArgument($calling->args);
                    $class = $calling->getAttribute('classShortcut');

                    $factory->stmts = [
                        new Stmt\Return_(
                            new Expr\New_(new Name($class), $factory->params)
                        ),
                    ];

                    $this->currentClassStmts[] = $factory;
                }
                break;
        }

        return parent::leaveNode($node);
    }

    private function getProcessedArgument(array $args)
    {
        $param = [];
        foreach ($args as $idx => $argument) {
            if ($argument->value->getType() === 'Expr_Expr') {
                $paramName = $argument->value->name;
            } else {
                $paramName = 'param' . $idx;
            }
            $newParam = new Param($paramName);
            $param[$idx] = $newParam;
        }

        return $param;
    }

    /**
     * Enter in a new instance statement (only process "hard-coded" classname)
     *
     * @param Expr\New_ $node
     *
     * @return Expr\MethodCall|null
     */
    protected function enterNewInstance(Expr\New_ $node)
    {
        if ($node->class instanceof Name) {
            $classShortcut = (string)$node->class;
            $methodName = 'create' . str_replace('\\', '_', $classShortcut) . count($node->args);
            $calling = new Expr\MethodCall(new Expr\Variable('this'), $methodName);
            $calling->args = $node->args;
            $calling->setAttribute('classShortcut', $classShortcut);
            $this->factoryMethodStack[$methodName] = $calling;
            $this->currentPhpFile->modified();

            return $calling;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function enterClassNode(Stmt\Class_ $node)
    {
        $this->factoryMethodStack = [];
        // to prevent cloning in Traverser (workaround) :
        $this->currentClassStmts = &$node->stmts;
    }

    /**
     * {@inheritdoc}
     */
    protected function enterInterfaceNode(Stmt\Interface_ $node)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function enterPublicMethodNode(Stmt\ClassMethod $node)
    {
        // only refactor a method if it contains more than 1 statements (would be pointless otherwise, IMO)
        $this->currentMethodRelevant = count($node->stmts) > 1;
    }

    /**
     * Writes modified files
     *
     * @param array $nodes
     */
    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $file) {
            if ($file->isModified()) {
                $this->dumper->write($file);
            }
        }
    }

    protected function enterTraitNode(Stmt\Trait_ $node)
    {
        // @todo creating a new protected factory for a trait makes sense
    }

}

