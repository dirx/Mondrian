<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\Logger;

use Trismegiste\Mondrian\Transform\Logger\GraphLogger;

/**
 * GraphLoggerTest tests the logger of graph building
 */
class GraphLoggerTest extends \PHPUnit_Framework_TestCase
{

    protected $logger;

    protected function setUp()
    {
        $this->logger = new GraphLogger();
    }

    public function testEmpty()
    {
        $this->assertEquals(array('graph' => array('calling' => array())), $this->logger->getDigest());
    }

    public function testAdding()
    {
        $this->logger->logCallTo('A::b', 'C::d');
        $this->assertEquals(array(
            'graph' => array(
                'calling' => array(
                    'A::b' => array(
                        'ignore' => array(
                            'C::d'
                        )
                    )
                )
            )
                ), $this->logger->getDigest());
    }

    public function testDoubleAdding()
    {
        $this->logger->logCallTo('A::b', 'C::d');
        $this->logger->logCallTo('A::b', 'C::d');
        $report = $this->logger->getDigest();
        $this->assertEquals(array('C::d'), $report['graph']['calling']['A::b']['ignore']);
    }

}