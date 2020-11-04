<?php declare(strict_types=1);

namespace Pdsinterop\Rdf\Flysystem\Plugin;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Plugin\AbstractPlugin;
use Pdsinterop\Rdf\Flysystem\Adapter\Rdf;
use Pdsinterop\Rdf\Formats;

class AsMime extends AbstractPlugin
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /** @var Formats */
    private $formats;

    //////////////////////////// GETTERS AND SETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return 'asMime';
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function __construct(Formats $formats)
    {
        $this->formats = $formats;
    }

    public function handle(string $mime) : FilesystemInterface
    {
        $filesystem = $this->filesystem;

        $adapter = $filesystem->getAdapter();


        if ($adapter instanceof Rdf) {
            $format = $this->formats->getFormatForMime($mime);
            $adapter->setFormat($format);
        }

        return $filesystem;
    }
}
