<?php

namespace Pdsinterop\Rdf\Flysystem\Adapter;

use ArgumentCountError;
use EasyRdf_Graph as Graph;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Pdsinterop\Rdf\Enum\Format;
use Pdsinterop\Rdf\Flysystem\Exception;
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

    private const MOCK_CONTENTS = 'mock contents';
    private const MOCK_CONTENTS_RDF = "@prefix rdfs: <> .\n</> rdfs:comment '' .";
    private const MOCK_MIME = 'mock mime';
    private const MOCK_PATH = 'mock path';
    private const MOCK_URL = 'mock url';

    /** @var AdapterInterface|MockObject */
    private $mockAdapter;
    /** @var Formats|MockObject */
    private $mockFormats;
    /** @var Graph|MockObject */
    private $mockGraph;

    private function createAdapter(): Rdf
    {
        $this->mockAdapter = $this->mockAdapter ?? $this->getMockBuilder(AdapterInterface::class)->getMock();
        $this->mockGraph = $this->getMockBuilder(Graph::class)->getMock();
        $this->mockFormats = $this->getMockBuilder(FormatsInterface::class)->getMock();

        return new Rdf($this->mockAdapter, $this->mockGraph, $this->mockFormats, self::MOCK_URL);
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
     * @covers ::getSize
     * @covers ::deleteDir
     * @covers ::getMetadata
     * @covers ::getTimestamp
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
     * @uses \Pdsinterop\Rdf\Flysystem\Adapter\Rdf::getMimeType
     *
     * @dataProvider provideProxyMethods
     */
    public function testRdfAdapterShouldReturnInnerAdapterResultWhenProxyMethodsAreCalled($method, $parameters): void
    {
        $expected = self::MOCK_CONTENTS;

        $adapterMethod = $method;

        if ($method === 'read' || $method === 'readStream') {
            $adapterMethod = 'read';
            $expected = ['contents' => $expected];
        } elseif ($method === 'getMimetype') {
            $expected = ['mimetype' => self::MOCK_MIME];
        }

        $adapter = $this->createAdapter();

        if ($method === 'getMetadata' || $method === 'read' || $method === 'readStream') {
            $this->mockAdapter
                ->method('read')
                ->willReturn(['contents' => self::MOCK_CONTENTS])
            ;
        }

        $this->mockAdapter->expects($this->once())
            ->method($adapterMethod)
            ->willReturn($expected)
        ;

        $actual = $adapter->{$method}(...$parameters);

        $this->assertSame($expected, $actual);
    }

    //////////////////////////// TESTS WITH FORMATTING \\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @covers ::setFormat
     *
     * @uses \Pdsinterop\Rdf\Enum\Format
     * @uses \Pdsinterop\Rdf\Flysystem\Exception
     *
     * @dataProvider provideUnsupportedFormats
     */
    public function testRdfAdapterShouldComplainWhenAskedToSetUnsupportedFormat($format): void
    {
        $adapter = $this->createAdapter();
        $message = vsprintf($adapter::ERROR_UNSUPPORTED_FORMAT, [$format]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $adapter->setFormat($format);
    }

    /**
     * @covers ::getFormat
     * @covers ::setFormat
     *
     * @uses \Pdsinterop\Rdf\Enum\Format
     *
     * @dataProvider provideSupportedFormats
     */
    public function testRdfAdapterShouldSetFormatWhenAskedToSetSupportedFormat($expected): void
    {
        $adapter = $this->createAdapter();

        $adapter->setFormat($expected);

        $actual = $adapter->getFormat();

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::getMimeType
     * @covers ::getSize
     * @covers ::has
     * @covers ::read
     * @covers ::readStream
     *
     * @uses \Pdsinterop\Rdf\Enum\Format
     * @uses \Pdsinterop\Rdf\Flysystem\Adapter\Rdf::getMetadata
     * @uses \Pdsinterop\Rdf\Flysystem\Adapter\Rdf::setFormat
     * @uses \Pdsinterop\Rdf\Formats
     *
     * @dataProvider provideConvertingMethods
     */
    public function testRdfAdapterShouldCallInnerAdapterAndGraphWhenNonProxyMethodsAreCalledWithFormat($method): void
    {
        $formats = [
            Format::JSON_LD,
            Format::N_TRIPLES,
            Format::NOTATION_3,
            Format::RDF_XML,
            Format::TURTLE,
        ];

        $formatCount = count($formats);

        $adapterMethod = $method;

        $adapter = $this->createAdapter();

        if ($method === 'readStream') {
            /*/ The `readStream` method is currently just a proxy for `read` /*/
            $adapterMethod = 'read';
        }

        $this->mockAdapter->method('read')
            ->willReturn(['contents' => self::MOCK_CONTENTS_RDF])
        ;

        $this->mockGraph->method('serialise')
            ->willReturn('[]')
        ;

        $this->mockFormats->method('getMimeForFormat')
            ->willReturn(self::MOCK_MIME)
        ;

        if ($method === 'getMimeType' || $method === 'getSize' || $method === 'has') {
            /*/ These inner adapter method should *never* be called when working with converted (meta)data /*/
            $this->mockAdapter->expects($this->never())
                ->method($adapterMethod)
            ;

            $this->mockAdapter
                ->method('getMetadata')
                ->willReturn([])
            ;
        } elseif ($method === 'read' || $method === 'readStream') {
            $this->mockAdapter->expects($this->exactly($formatCount))
                ->method($adapterMethod)
            ;
        } elseif ($method === 'getMetadata') {
            $this->mockAdapter->expects($this->exactly($formatCount))
                ->method($adapterMethod)
                ->willReturn([])
            ;
        } else {
            $this->fail('Do not know how to test for ' . $method);
        }

        $expected = [
            'contents' => '[]',
            'mimetype' => self::MOCK_MIME,
            'path' => 'mock path',
            'size' => 2,
            'type' => 'file',
        ];

        if ($method === 'getMimeType') {
            /*/ Mimetype does not require metadata or read to function.
                Hence, it only returns one value.
            /*/
            $expected = ['mimetype' => self::MOCK_MIME];
        }

        foreach ($formats as $format) {
            $adapter->setFormat($format);

            $actual = $adapter->{$method}(self::MOCK_PATH);

            $this->assertEquals($expected, $actual);
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
            'getMimetype' => ['getMimetype', [$mockPath]],
            'getSize' => ['getSize', [$mockPath]],
            'getVisibility' => ['getVisibility', [$mockPath]],
            'getTimestamp' => ['getTimestamp', [$mockPath]],
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

    public function provideConvertingMethods(): array
    {
        return [
            'getMetadata' => ['getMetadata'],
            'getSize' => ['getSize'],
            'has' => ['has'],
            'getMimeType' => ['getMimeType'],
            'read' => ['read'],
            'readStream' => ['readStream'],
        ];
    }

    public function provideSupportedFormats(): array
    {
        return [
            'string: empty' => [''],
            Format::JSON_LD => [Format::JSON_LD],
            Format::N_TRIPLES => [Format::N_TRIPLES],
            Format::NOTATION_3 => [Format::NOTATION_3],
            Format::RDF_XML => [Format::RDF_XML],
            Format::TURTLE => [Format::TURTLE],
        ];
    }

    public function provideUnsupportedFormats(): array
    {
        return [
            'mock format' => ['mock format'],
            Format::UNKNOWN => [Format::UNKNOWN],
        ];
    }
}
