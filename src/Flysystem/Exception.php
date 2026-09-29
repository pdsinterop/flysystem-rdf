<?php

namespace Pdsinterop\Rdf\Flysystem;

class Exception extends \Exception implements \League\Flysystem\FilesystemException
{
    public static function create(string $error, array $context, ?\Exception $previous = null): Exception
    {
        return new static(vsprintf($error, $context), 0, $previous);
    }
}
