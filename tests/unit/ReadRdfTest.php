<?php

namespace Pdsinterop\Rdf\Flysystem\Plugin;

use ArgumentCountError;
use EasyRdf_Graph;
use Error;
use League\Flysystem\FilesystemInterface;
use Pdsinterop\Rdf\Enum\Format;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * @coversDefaultClass \Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf
 * @covers ::__construct
 * @covers ::<!public>
 */
class ReadRdfTest extends TestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private const MOCK_CONTENTS = 'mock contents';
    private const MOCK_FORMAT = 'mock format';
    private const MOCK_PATH = 'mock path';
    private const MOCK_URL = 'mock url';

    ////////////////////////////// CUSTOM ASSERTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    private static function assertPropertyEquals($object, string $property, $expected) : void
    {
        $reflector = new ReflectionObject($object);

        /** @noinspection PhpUnhandledExceptionInspection */
        $attribute = $reflector->getProperty($property);
        $attribute->setAccessible(true);

        $actual = $attribute->getValue($object);

        self::assertSame($expected, $actual);
    }

    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @covers ::__construct
     */
    public function testRdfPluginShouldComplainWhenInstantiatedWithoutGraph() : void
    {
        $this->expectException(ArgumentCountError::class);

        /** @noinspection PhpParamsInspection */
        new ReadRdf();
    }

    /**
     * @covers ::__construct
     */
    public function testRdfPluginShouldReceiveEasyRdfGraphWhenInstantiated() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $actual = new ReadRdf($mockGraph);

        self::assertInstanceOf(ReadRdf::class, $actual);
    }

    /**
     * @covers ::setFilesystem
     */
    public function testRdfPluginShouldComplainWhenSetFilesystemCalledWithoutFilesystem() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $this->expectException(ArgumentCountError::class);

        /** @noinspection PhpParamsInspection */
        $plugin->setFilesystem();
    }

    /**
     * @covers ::setFilesystem
     */
    public function testRdfPluginShouldContainFilesystemWhenFilesystemGiven() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $expected = $this->getMockFilesystem();

        $plugin->setFilesystem($expected);

        self::assertPropertyEquals($plugin, 'filesystem', $expected);
    }

    /**
     * @covers ::getMethod
     */
    public function testRdfPluginShouldReturnExpectedMethodNameWhenAskedForMethod() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $actual = $plugin->getMethod();
        $expected = 'readRdf';

        self::assertEquals($expected, $actual);
    }

    public function testRdfPluginShouldComplainWhenHandleCalledWithoutPath() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('Too few arguments to function Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf::handle(), 0 passed');

        /** @noinspection PhpParamsInspection PhpUnhandledExceptionInspection */
        $plugin->handle();
    }

    public function testRdfPluginShouldComplainWhenHandleCalledWithoutFormat() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('Too few arguments to function Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf::handle(), 1 passed');

        /** @noinspection PhpParamsInspection PhpUnhandledExceptionInspection */
        $plugin->handle('');
    }

    public function testRdfPluginShouldComplainWhenHandleCalledWithoutUrl() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('Too few arguments to function Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf::handle(), 2 passed');

        /** @noinspection PhpParamsInspection PhpUnhandledExceptionInspection */
        $plugin->handle('', '');
    }

    /**
     * @covers ::handle
     */
    public function testRdfPluginShouldComplainWhenHandleCalledWithoutFilesystem() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();

        $plugin = new ReadRdf($mockGraph);

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Call to a member function read() on null');

        /** @noinspection PhpUnhandledExceptionInspection */
        $plugin->handle('', '', '');
    }

    /**
     * @covers ::handle
     * @covers ::setFilesystem
     */
    public function testRdfPluginShouldSerialiseFileContentsWhenHandleCalledWithPathAndFormatAndUrl() : void
    {
        $mockGraph = $this->getMockEasyRdfGraph();
        $mockFilesystem = $this->getMockFilesystem(self::MOCK_PATH, self::MOCK_CONTENTS);

        $mockGraph->method('parse')
            ->with(self::MOCK_CONTENTS, Format::UNKNOWN, self::MOCK_URL)
        ;

        $mockGraph->method('serialise')
            ->with(self::MOCK_FORMAT)
            ->willReturn(self::MOCK_CONTENTS)
        ;

        $plugin = new ReadRdf($mockGraph);
        $plugin->setFilesystem($mockFilesystem);

        /** @noinspection PhpUnhandledExceptionInspection */
        $actual = $plugin->handle(self::MOCK_PATH, self::MOCK_FORMAT, self::MOCK_URL);
        $expected = self::MOCK_CONTENTS;

        self::assertEquals($expected, $actual);
    }

    // @TODO: Test for when $filesystem->read($path) returns false
    // @TODO: Test for when $converter->parse(...) throws EasyRdf_Exception
    // @TODO: Test for when $converter->parse(...) output is non-scalar

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @return EasyRdf_Graph | MockObject
     */
    private function getMockEasyRdfGraph() : EasyRdf_Graph
    {
        return $this->getMockBuilder(EasyRdf_Graph::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $path
     * @param string $fileContents
     *
     * @return FilesystemInterface | MockObject
     */
    private function getMockFilesystem(string $path = '', string $fileContents = '') : FilesystemInterface
    {
        $mockFilesystem = $this->getMockBuilder(FilesystemInterface::class)
            ->getMock();

        $mockFilesystem->method('read')
            ->with($path)
            ->willReturn($fileContents);

        return $mockFilesystem;
    }
}
