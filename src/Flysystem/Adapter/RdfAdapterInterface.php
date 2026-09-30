<?php

namespace Pdsinterop\Rdf\Flysystem\Adapter;

use League\Flysystem\FilesystemAdapter;

/**
 * Filesystem adapter to convert RDF files to and from a default format
 */
interface RdfAdapterInterface extends FilesystemAdapter
{
    public function getFormat(): string;

    public function setFormat(string $format): void;
}
