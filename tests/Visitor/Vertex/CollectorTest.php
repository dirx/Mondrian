<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor\Vertex;

use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use Trismegiste\Mondrian\Graph\Graph;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Transform\GraphContext;
use Trismegiste\Mondrian\Transform\ReflectionContext;
use Trismegiste\Mondrian\Transform\Vertex\ImplVertex;
use Trismegiste\Mondrian\Transform\Vertex\MethodVertex;
use Trismegiste\Mondrian\Transform\Vertex\ParamVertex;
use Trismegiste\Mondrian\Visitor\Vertex\Collector;

/**
 * CollectorTest is simple tests for Vertex\Collector visitor
 */
class CollectorTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;
    protected $reflection;
    protected $vertex;
    protected $graph;

    protected function setUp()
    {
        $this->reflection = $this->getMockBuilder(ReflectionContext::class)
            ->getMock();
        $this->vertex = $this->getMockBuilder(GraphContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->graph = $this->getMockBuilder(Graph::class)
            ->getMock();
        $this->visitor = new Collector($this->reflection, $this->vertex, $this->graph);
    }

    public function getTypeNodeSetting()
    {
        $vertexNS = 'Trismegiste\Mondrian\Transform\Vertex\\';
        $fileNode = new PhpFile('dummy', []);
        $nsNode = new Namespace_(new Name('Tubular'));
        $classNode = new Class_('Bells');
        $interfNode = new Interface_('Bells');
        $traitNode = new Trait_('Bells');
        return [
            ['class', 'Tubular\Bells', $vertexNS . 'ClassVertex', [$fileNode, $nsNode, $classNode]],
            ['interface', 'Tubular\Bells', $vertexNS . 'InterfaceVertex', [$fileNode, $nsNode, $interfNode]],
            ['trait', 'Tubular\Bells', $vertexNS . 'TraitVertex', [$fileNode, $nsNode, $traitNode]],
        ];
    }

    /**
     * @dataProvider getTypeNodeSetting
     */
    public function testNoNewClassVertex($type, $fqcn, $graphVertex, array $nodeList)
    {
        $this->vertex
            ->expects($this->once())
            ->method('existsVertex')
            ->with($type, $fqcn)
            ->will($this->returnValue(true));

        $this->graph
            ->expects($this->never())
            ->method('addVertex');

        foreach ($nodeList as $node) {
            $this->visitor->enterNode($node);
        }
    }

    /**
     * @dataProvider getTypeNodeSetting
     */
    public function testNewClassVertex($type, $fqcn, $graphVertex, array $nodeList)
    {
        $this->vertex
            ->expects($this->once())
            ->method('existsVertex')
            ->with($type, $fqcn)
            ->will($this->returnValue(false));

        $this->vertex
            ->expects($this->once())
            ->method('indicesVertex')
            ->with($type, $fqcn);

        $this->graph
            ->expects($this->once())
            ->method('addVertex')
            ->with($this->isInstanceOf($graphVertex));

        foreach ($nodeList as $node) {
            $this->visitor->enterNode($node);
        }
    }

    public function testNewMethodVertexForClass()
    {
        list($type, $fqcn, $graphVertex, $nodeList) = $this->getTypeNodeSetting()[0];
        $method = new ClassMethod('crisis');
        $method->params[] = new Param('incantations');
        $nodeList[] = $method;

        $this->reflection
            ->expects($this->once())
            ->method('getDeclaringClass')
            ->with($fqcn, 'crisis')
            ->will($this->returnValue($fqcn));

        $this->graph
            ->expects($this->exactly(4))
            ->method('addVertex');

        $this->graph
            ->expects($this->at(0))
            ->method('addVertex')
            ->with($this->isInstanceOf($graphVertex));

        $this->graph
            ->expects($this->at(1))
            ->method('addVertex')
            ->with($this->isInstanceOf('Trismegiste\Mondrian\Transform\Vertex\MethodVertex'));

        $this->graph
            ->expects($this->at(2))
            ->method('addVertex')
            ->with($this->isInstanceOf('Trismegiste\Mondrian\Transform\Vertex\ParamVertex'));

        $this->graph
            ->expects($this->at(3))
            ->method('addVertex')
            ->with($this->isInstanceOf('Trismegiste\Mondrian\Transform\Vertex\ImplVertex'));

        foreach ($nodeList as $node) {
            $this->visitor->enterNode($node);
        }
    }

    public function testNewMethodVertexForInterface()
    {
        list($type, $fqcn, $graphVertex, $nodeList) = $this->getTypeNodeSetting()[1];
        $method = new ClassMethod('crisis');
        $method->params[] = new Param('incantations');
        $nodeList[] = $method;

        $this->reflection
            ->expects($this->once())
            ->method('getDeclaringClass')
            ->with($fqcn, 'crisis')
            ->will($this->returnValue($fqcn));

        $this->graph
            ->expects($this->exactly(3))
            ->method('addVertex');

        $this->graph
            ->expects($this->at(0))
            ->method('addVertex')
            ->with($this->isInstanceOf($graphVertex));

        $this->graph
            ->expects($this->at(1))
            ->method('addVertex')
            ->with($this->isInstanceOf(MethodVertex::class));

        $this->graph
            ->expects($this->at(2))
            ->method('addVertex')
            ->with($this->isInstanceOf(ParamVertex::class));

        foreach ($nodeList as $node) {
            $this->visitor->enterNode($node);
        }
    }

    public function testNewImplementationVertexForTrait()
    {
        list($type, $fqcn, $graphVertex, $nodeList) = $this->getTypeNodeSetting()[2];
        $method = new ClassMethod('crisis');
        $method->params[] = new Param('incantations');
        $nodeList[] = $method;

        $this->reflection
            ->expects($this->once())
            ->method('getClassesUsingTraitForDeclaringMethod')
            ->with($fqcn, 'crisis')
            ->will($this->returnValue([]));

        $this->graph
            ->expects($this->exactly(3))
            ->method('addVertex');

        $this->graph
            ->expects($this->at(0))
            ->method('addVertex')
            ->with($this->isInstanceOf($graphVertex));

        $this->graph
            ->expects($this->at(1))
            ->method('addVertex')
            ->with($this->isInstanceOf(ImplVertex::class));

        $this->graph
            ->expects($this->at(2))
            ->method('addVertex')
            ->with($this->isInstanceOf(ParamVertex::class));

        foreach ($nodeList as $node) {
            $this->visitor->enterNode($node);
        }
    }

    public function testCopyPasteImportedMethodFromTrait()
    {
        list($type, $fqcn, $graphVertex, $nodeList) = $this->getTypeNodeSetting()[2];

        $method = new ClassMethod('crisis');
        $method->params[] = new Param('incantations');
        $nodeList[] = $method;

        $this->reflection
            ->expects($this->once())
            ->method('getClassesUsingTraitForDeclaringMethod')
            ->with($fqcn, 'crisis')
            ->will($this->returnValue(['TraitUser1', 'TraitUser2']));

        $this->graph
            ->expects($this->exactly(5))
            ->method('addVertex');

        // the trait vertex
        $this->graph
            ->expects($this->at(0))
            ->method('addVertex')
            ->with($this->isInstanceOf($graphVertex));

        // implementation
        $this->graph
            ->expects($this->at(1))
            ->method('addVertex')
            ->with($this->isInstanceOf(ImplVertex::class));
        $this->graph
            ->expects($this->at(2))
            ->method('addVertex')
            ->with($this->isInstanceOf(ParamVertex::class));

        // first copy-pasted method
        $this->graph
            ->expects($this->at(3))
            ->method('addVertex')
            ->with($this->isInstanceOf(MethodVertex::class));

        // second copy-pasted method
        $this->graph
            ->expects($this->at(4))
            ->method('addVertex')
            ->with($this->isInstanceOf(MethodVertex::class));

        foreach ($nodeList as $node) {
            $this->visitor->enterNode($node);
        }
    }

}
