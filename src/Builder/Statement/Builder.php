<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Builder\Statement;

use PhpParser\Lexer;
use PhpParser\Parser\Multiple;
use PhpParser\Parser\Php5;
use PhpParser\Parser\Php7;
use Trismegiste\Mondrian\Parser\PackageParser;

/**
 * Statement is a builder of statement of set of php files
 */
class Builder implements BuilderInterface
{

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var PackageParser
     */
    protected $fileParser;

    /**
     * @var PackageParser
     */
    protected $packageParser;

    /**
     * {@inheritdoc}
     */
    public function buildLexer()
    {
        $this->lexer = new Lexer();
    }

    /**
     * {@inheritdoc}
     */
    public function buildFileLevel()
    {
        $this->fileParser = new Multiple([
            new Php7($this->lexer),
            new Php5($this->lexer),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPackageLevel()
    {
        $this->packageParser = new PackageParser($this->fileParser);
    }

    /**
     * {@inheritdoc}
     */
    public function getParsed(\Iterator $iter)
    {
        return $this->packageParser->parse($iter);
    }

}
