<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform;

use Trismegiste\Mondrian\Graph\Vertex;
use Trismegiste\Mondrian\Transform\GraphContext;

/**
 * GraphContextTest tests for vertex mapping Context
 */
class GraphContextTest extends \PHPUnit_Framework_TestCase
{

    protected $context;

    protected function setUp()
    {
        $this->context = new GraphContext(['calling' => []], $this->getLoggerMock());
    }

    public function getVertexMock()
    {
        foreach (['class', 'interface', 'impl', 'method', 'param', 'trait'] as $pool) {
            $v[] = [$pool, $this->getMockBuilder('Trismegiste\Mondrian\Graph\Vertex')
                ->disableOriginalConstructor()
                ->getMock()];
        }

        return $v;
    }

    protected function getLoggerMock()
    {
        return $this->createMock('Trismegiste\Mondrian\Transform\Logger\LoggerInterface');
    }

    /**
     * @dataProvider getVertexMock
     */
    public function testEmpty($pool, Vertex $v)
    {
        $this->assertNull($this->context->findVertex($pool, 'Unknown'));
    }

    /**
     * @dataProvider getVertexMock
     */
    public function testFindVertex($pool, Vertex $v)
    {
        $this->context->indicesVertex($pool, 'idx', $v);
        $this->assertEquals($v, $this->context->findVertex($pool, 'idx'));
    }

    /**
     * @dataProvider getVertexMock
     */
    public function testExistsVertex($pool, Vertex $v)
    {
        $this->assertFalse($this->context->existsVertex($pool, 'idx'));
        $this->context->indicesVertex($pool, 'idx', $v);
        $this->assertTrue($this->context->existsVertex($pool, 'idx'));
        $this->assertEquals($v, $this->context->findVertex($pool, 'idx'));
    }

    public function testFindMethodByName()
    {
        $v = $this->getMockBuilder('Trismegiste\Mondrian\Graph\Vertex')
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $v->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Some::getter'));

        $this->context->indicesVertex('method', 'unused', $v);
        $this->assertEquals(['unused' => $v], $this->context->findAllMethodSameName('getter'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadConfig()
    {
        new GraphContext([], $this->getLoggerMock());
    }

    public function testGoodConfig()
    {
        $ctx = new GraphContext([
            'calling' => [
                'A::b' => ['ignore' => ['C::d']],
            ],
        ], $this->getLoggerMock());
        $this->assertEquals(['C::d'], $ctx->getExcludedCall('A', 'b'));
    }

}
