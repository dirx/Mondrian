<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Trismegiste\Mondrian\Analysis\LiskovSearch;
use Trismegiste\Mondrian\Analysis\UsedCentrality;
use Trismegiste\Mondrian\Graph\Graph;

/**
 * LiskovCommand transforms a bunch of php files into a reduced digraph
 * to the LSP violation to refactor (mandatory before ISP)
 *
 */
class LiskovCommand extends AbstractParse
{

    protected function getSubname()
    {
        return 'liskov';
    }

    protected function getFullDesc()
    {
        return parent::getFullDesc() . ' with LSP violation';
    }

    protected function processGraph(Graph $graph, OutputInterface $output)
    {
        $algo = new LiskovSearch($graph);
        $result = $algo->createReducedGraph();
        $central = new UsedCentrality($result);
        $central->decorate();

        return $result;
    }

}
