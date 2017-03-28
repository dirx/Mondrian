<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

/**
 * PublicCollector is an abstract node collector for public "things" of types
 *
 */
abstract class PublicCollector extends FqcnHelper
{

    protected $currentClass = false;
    protected $currentMethod = false;

    /**
     * Visits a class node
     *
     * @param Class_ $node
     */
    abstract protected function enterClassNode(Class_ $node);

    /**
     * Visits an interface node
     *
     * @param Interface_ $node
     */
    abstract protected function enterInterfaceNode(Interface_ $node);

    /**
     * Visits an trait node
     *
     * @param Trait_ $node
     */
    abstract protected function enterTraitNode(Trait_ $node);

    /**
     * Visits a public method node
     *
     * @param ClassMethod $node
     */
    abstract protected function enterPublicMethodNode(ClassMethod $node);

    /**
     * {@inheritDoc}
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        switch ($node->getType()) {

            case 'Stmt_Class' :
                $this->currentClass = $this->getNamespacedName($node);
                $this->enterClassNode($node);
                break;

            case 'Stmt_Interface' :
                $this->currentClass = $this->getNamespacedName($node);
                $this->enterInterfaceNode($node);
                break;

            case 'Stmt_Trait' :
                $this->currentClass = $this->getNamespacedName($node);
                $this->enterTraitNode($node);
                break;

            case 'Stmt_ClassMethod' :
                if ($node->isPublic()) {
                    $this->currentMethod = $node->name;
                    $this->enterPublicMethodNode($node);
                }
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node)
    {
        switch ($node->getType()) {

            case 'Stmt_Class':
            case 'Stmt_Interface':
            case 'Stmt_Trait':
                $this->currentClass = false;
                break;

            case 'Stmt_ClassMethod' :
                $this->currentMethod = false;
                break;

            case 'PhpFile' :
                if ($this->currentPhpFile->isModified()) {
                    return $this->currentPhpFile;
                }
                break;
        }
    }

    /**
     * the vertex name for a MethodVertex
     *
     * @return string
     */
    protected function getCurrentMethodIndex()
    {
        return $this->currentClass . '::' . $this->currentMethod;
    }

    /**
     * Extracts annotations in the comment of a statement and injects them in
     * attribute of the node
     *
     * @param Stmt $node
     */
    protected function extractAnnotation(Stmt $node)
    {
        if ($node->hasAttribute('comments')) {
            $compil = [];
            foreach ($node->getAttribute('comments') as $comm) {
                preg_match_all('#^.*@mondrian\s+([\w]+)\s+([^\s]+)\s*$#m', $comm->getReformattedText(), $match);
                foreach ($match[0] as $idx => $matchedOccur) {
                    $compil[$match[1][$idx]][] = $match[2][$idx];
                }
            }
            // if there are annotations, we add them to the node
            foreach ($compil as $attr => $lst) {
                $node->setAttribute($attr, $lst);
            }
        }
    }
}
