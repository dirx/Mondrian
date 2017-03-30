<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Transform\Logger;

/**
 * GraphLogger logs the transform of source code to a graph
 */
class GraphLogger implements LoggerInterface
{

    protected $stack = [];

    /**
     * {@inheritdoc}
     */
    public function logCallTo($callee, $called)
    {
        $this->stack[$callee][$called] = true;
    }

    protected function getCallingDigest()
    {
        $report = [];
        ksort($this->stack);
        foreach ($this->stack as $callee => $calledLst) {
            $calledLst = array_keys($calledLst);
            sort($calledLst);
            $report[$callee] = ['ignore' => $calledLst];
        }

        return ['calling' => $report];
    }

    /**
     * Get the yml config
     *
     * @return string the yaml-formatted full report
     */
    public function getDigest()
    {
        return ['graph' => $this->getCallingDigest()];
    }

}
