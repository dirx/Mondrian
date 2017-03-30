<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;

/**
 * FqcnHelper is an helper for resolving FQCN for Class/Interface/Param
 */
class FqcnHelper extends NodeVisitorAbstract
{

    /**
     * @var null|Name Current namespace
     */
    protected $namespace;

    /**
     * @var array Currently defined namespace and class aliases
     */
    protected $aliases;

    /**
     * current file
     */
    protected $currentPhpFile = false;

    public function beforeTraverse(array $nodes)
    {
        // if the visitor is used without PhpFile nodes
        $this->namespace = null;
        $this->aliases = [];
    }

    public function enterNode(Node $node)
    {

        switch ($node->getType()) {

            case 'PhpFile' :
                $this->currentPhpFile = $node;
                // resetting the tracking of namespace and alias if we enter in a new file
                $this->namespace = null;
                $this->aliases = [];
                break;

            case 'Stmt_Namespace' :
                $this->namespace = $node->name;
                $this->aliases = [];
                break;

            case 'Stmt_UseUse' :
                if (isset($this->aliases[$node->alias])) {
                    throw new Error(
                        sprintf(
                            'Cannot use "%s" as "%s" because the name is already in use', $node->name, $node->alias
                        ), $node->getLine()
                    );
                }
                $this->aliases[$node->alias] = $node->name;
                break;
        }
    }

    /**
     * resolve the Name with current namespace and alias
     *
     * @param Name $src
     *
     * @return Name|FullyQualified
     */
    protected function resolveClassName(Name $src)
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
    protected function getNamespacedName(Node $node)
    {
        if (null !== $this->namespace) {
            $namespacedName = FullyQualified::concat($this->namespace, $node->name);
        } else {
            $namespacedName = $node->name;
        }

        return (string)$namespacedName;
    }

}
