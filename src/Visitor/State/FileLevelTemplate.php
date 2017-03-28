<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

/**
 * FileLevelTemplate is Template Method DP for a FileLevel state
 */
abstract class FileLevelTemplate extends AbstractState
{
    /**
     * @var null|Name Current namespace
     */
    protected $namespace;

    /**
     * @var array Currently defined namespace and class aliases
     */
    protected $aliases;

    final public function enter(Node $node)
    {
        switch ($node->getType()) {

            case 'Stmt_Namespace' :
                $this->namespace = $node->name;
                $this->aliases = [];
                // @todo : with multiple namespaces in one file : does this bug ?
                // leave() shouldn't reset these values ?
                break;

            case 'Stmt_UseUse' :
                if (isset($this->aliases[$node->alias]) && $this->aliases[$node->alias] !== $node->name) {
                    throw new Error(
                        sprintf(
                            'Cannot use "%s" as "%s" because the name is already in use as %s',
                            $node->name,
                            $node->alias,
                            $this->aliases[$node->alias]
                        ),
                        $node->getLine()
                    );
                }
                $this->aliases[$node->alias] = $node->name;
                break;

            case 'Stmt_Class':
                $this->context->pushState('class', $node);
                $this->enterClassNode($node);
                break;

            case 'Stmt_Trait':
                $this->context->pushState('trait', $node);
                $this->enterTraitNode($node);
                break;

            case 'Stmt_Interface':
                $this->context->pushState('interface', $node);
                $this->enterInterfaceNode($node);
                break;
        }
    }

    public function getName()
    {
        return 'file';
    }

    /**
     * Enters in a class node
     */
    abstract protected function enterClassNode(Class_ $node);

    /**
     * Enters in a trait node
     */
    abstract protected function enterTraitNode(Trait_ $node);

    /**
     * Enters in an interface node
     */
    abstract protected function enterInterfaceNode(Interface_ $node);

    /**
     * resolve the Name with current namespace and alias
     *
     * @param Name $src
     *
     * @return Name|FullyQualified
     */
    public function resolveClassName(Node\Name $src)
    {
        $name = clone $src;
        // don't resolve special class names
        if (in_array((string)$name, ['self', 'parent', 'static'])) {
            return $name;
        }

        // fully qualified names are already resolved
        if ($name->isFullyQualified()) {
            return $name;
        }

        // resolve aliases (for non-relative names)
        if (!$name->isRelative() && isset($this->aliases[$name->getFirst()])) {
            // if no alias exists prepend current namespace
            return FullyQualified::concat($this->aliases[$name->getFirst()], $name->slice(1));
        } elseif (null !== $this->namespace) {
            return FullyQualified::concat($this->namespace, $src);
        }

        return $name;
    }

    /**
     * Helper : get the FQCN of the given $node->name
     *
     * @param Node $node
     *
     * @return string
     */
    public function getNamespacedName(Node $node)
    {
        if (null !== $this->namespace) {
            $namespacedName = FullyQualified::concat($this->namespace, $node->name);
        } else {
            $namespacedName = $node->name;
        }

        return (string)$namespacedName;
    }
}
