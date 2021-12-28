<?php

namespace Pdsinterop\Rdf\Flysystem\Adapter;

use ArgumentCountError;
use EasyRdf_Graph as Graph;
use League\Flysystem\AdapterInterface;
use Pdsinterop\Rdf\Formats;
use Pdsinterop\Rdf\FormatsInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Pdsinterop\Rdf\Flysystem\Adapter\Rdf
 * @covers ::__construct
 * @covers ::<!public>
 */
class RdfTest extends TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    const MOCK_URL = 'mock url';

    private function createAdapter(): Rdf
    {
        $mockAdapter = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $mockGraph = $this->getMockBuilder(Graph::class)->getMock();
        $mockFormats = $this->getMockBuilder(FormatsInterface::class)->getMock();

        return new Rdf($mockAdapter, $mockGraph, $mockFormats, self::MOCK_URL);
    }

    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

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
}
