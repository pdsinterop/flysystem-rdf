<?php

namespace Pdsinterop\Rdf\Flysystem\Adapter;

use ArgumentCountError;
use EasyRdf_Graph as Graph;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Pdsinterop\Rdf\Enum\Format;
use Pdsinterop\Rdf\Formats;
use Pdsinterop\Rdf\FormatsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Pdsinterop\Rdf\Flysystem\Adapter\Rdf
 * @covers ::__construct
 * @covers ::<!public>
 */
class RdfTest extends TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private const MOCK_URL = 'mock url';
    private const MOCK_CONTENTS = 'mock contents';
    private const MOCK_PATH = 'mock path';

    /** @var AdapterInterface|MockObject */
    private $mockAdapter;
    /** @var Formats|MockObject */
    private $mockFormats;

    private function createAdapter(): Rdf
    {
        $this->createMockAdapter();
        $mockGraph = $this->getMockBuilder(Graph::class)->getMock();
        $this->mockFormats = $this->getMockBuilder(FormatsInterface::class)->getMock();

        return new Rdf($this->mockAdapter, $mockGraph, $this->mockFormats, self::MOCK_URL);
    }

    ////////////////////////// TESTS WITHOUT FORMATTING \\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @covers ::__construct
     */
    public function testRdfAdapterShouldComplainWhenInstantiatedWithoutAdapter(): void
    {
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('0 passed');

        new Rdf();
    }

    /**
     * @covers ::__construct
     */
    public function testRdfAdapterShouldComplainWhenInstantiatedWithoutGraph(): void
    {
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('1 passed');

        $mockAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock()
        ;

        new Rdf($mockAdapter);
    }

    /**
     * @covers ::__construct
     */
    public function testRdfAdapterShouldComplainWhenInstantiatedWithoutFormats(): void
    {
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('2 passed');

        $mockAdapter = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $mockGraph = $this->getMockBuilder(Graph::class)->getMock();

        new Rdf($mockAdapter, $mockGraph);
    }

    /**
     * @covers ::__construct
     */
    public function testRdfAdapterShouldComplainWhenInstantiatedWithoutUrl(): void
    {
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('3 passed');

        $mockAdapter = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $mockGraph = $this->getMockBuilder(Graph::class)->getMock();
        $mockFormats = $this->getMockBuilder(Formats::class)->getMock();

        new Rdf($mockAdapter, $mockGraph, $mockFormats);
    }

    /**
     * @covers ::__construct
     */
    public function testRdfAdapterShouldBeInstantiatedWhenGivenExpectedDependencies(): void
    {
        $this->assertInstanceOf(Rdf::class, $this->createAdapter());
    }

    /**
     * @covers ::copy
     * @covers ::createDir
     * @covers ::delete
     * @covers ::deleteDir
     * @covers ::getMetadata
     * @covers ::getVisibility
     * @covers ::listContents
     * @covers ::read
     * @covers ::readStream
     * @covers ::rename
     * @covers ::setVisibility
     * @covers ::update
     * @covers ::updateStream
     * @covers ::write
     * @covers ::writeStream
     *
     * @dataProvider provideProxyMethods
     */
    public function testRdfAdapterShouldCallInnerAdapterWhenProxyMethodsAreCalled($method, $parameters): void
    {
        $adapterMethod = $method;

        if ($method === 'readStream') {
            $adapterMethod = 'read';
        }

        $adapter = $this->createAdapter();

        $this->mockAdapter->expects($this->once())
            ->method($adapterMethod)
        ;

        $adapter->{$method}(...$parameters);
    }

    //////////////////////////// TESTS WITH FORMATTING \\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @covers ::copy
     * @covers ::createDir
     * @covers ::delete
     * @covers ::deleteDir
     * @covers ::getMetadata
     * @covers ::getVisibility
     * @covers ::listContents
     * @covers ::read
     * @covers ::readStream
     * @covers ::rename
     * @covers ::setVisibility
     * @covers ::update
     * @covers ::updateStream
     * @covers ::write
     * @covers ::writeStream
     *
     * @uses \Pdsinterop\Rdf\Enum\Format
     * @uses \Pdsinterop\Rdf\Flysystem\Adapter\Rdf::setFormat
     * @uses \Pdsinterop\Rdf\Formats
     *
     * @dataProvider provideProxyMethods
     */
    public function testRdfAdapterShouldCallInnerAdapterAndGraphWhenProxyMethodsAreCalledWithFormat($method, $parameters): void
    {
        $this->mockAdapter = $this->getMockBuilder(AdapterInterface::class)->getMock();

        $formats = [
            Format::TURTLE,
            Format::RDF_XML,
            Format::NOTATION_3,
            Format::N_TRIPLES,
            Format::JSON_LD,
        ];

        $formatCount = count($formats);

        $adapterMethod = $method;

        if ($method === 'readStream') {
            $adapterMethod = 'read';
        }

        if ($method === 'read' || $method === 'readStream') {
            $this->mockAdapter
                ->method('read')
                ->willReturn(['contents'=> self::MOCK_CONTENTS])
            ;
        }

        $this->mockAdapter->expects($this->exactly($formatCount))
            ->method($adapterMethod)
        ;

        foreach ($formats as $format) {
            $adapter = $this->createAdapter();
            $adapter->setFormat($format);

            $this->mockFormats->method('getFormatForExtension')
                ->willReturn($format)
            ;

            $adapter->{$method}(...$parameters);
        }
    }

    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    public function provideProxyMethods(): array
    {
        $mockConfig = $this->getMockBuilder(Config::class)->getMock();
        $mockContents = self::MOCK_CONTENTS;
        $mockPath = self::MOCK_PATH;
        $mockResource = fopen('php://temp', 'rb');

        return [
            'copy' => ['copy', [$mockPath, $mockPath]],
            'createDir' => ['createDir', [$mockPath, $mockConfig]],
            'delete' => ['delete', [$mockPath]],
            'deleteDir' => ['deleteDir', [$mockPath]],
            'getMetadata' => ['getMetadata', [$mockPath]],
            'getVisibility' => ['getVisibility', [$mockPath]],
            'listContents' => ['listContents', []],
            'read' => ['read', [$mockPath]],
            'readStream' => ['readStream', [$mockPath]],
            'rename' => ['rename', [$mockPath, $mockPath]],
            'setVisibility' => ['setVisibility', [$mockPath, 'mock visibility']],
            'update' => ['update', [$mockPath, $mockContents, $mockConfig]],
            'updateStream' => ['updateStream', [$mockPath, $mockResource, $mockConfig]],
            'write' => ['write', [$mockPath, $mockContents, $mockConfig]],
            'writeStream' => ['writeStream', [$mockPath, $mockResource, $mockConfig]],
        ];
    }

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private function createMockAdapter(): void
    {
        $this->mockAdapter = $this->mockAdapter ?? $this->getMockBuilder(AdapterInterface::class)->getMock();
    }
}
