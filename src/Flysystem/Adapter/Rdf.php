<?php declare(strict_types=1);

namespace Pdsinterop\Rdf\Flysystem\Adapter;

use EasyRdf_Exception;
use EasyRdf_Graph as Graph;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Pdsinterop\Rdf\Enum\Format;
use Pdsinterop\Rdf\Flysystem\Exception;
use Pdsinterop\Rdf\FormatsInterface;
use ML\JsonLD;
/**
 * Filesystem adapter to convert RDF files to and from a default format
 */
class Rdf implements AdapterInterface
{
    private const ERROR_COULD_NOT_CONVERT = 'Could not convert file "%s" to format "%s": %s';

    /** @var AdapterInterface */
    private $adapter;
    /** @var string */
    private $format = '';
    /** @var FormatsInterface */
    private $formats;
    /** @var Graph */
    private $graph;
    /** @var string */
    private $url;

    /**
     * Retrieve a new / clean RDF Graph object
     *
     * @return Graph
     */
    private function getGraph(): Graph
    {
        return clone $this->graph;
    }

    final public function setFormat(string $format) : void
    {
        if (($format != "") && (Format::has($format) === false)) {
            throw new Exception('Given format "' . $format . '" is not supported');
        }

        $this->format = $format;
    }
    final public function getFormat() {
		return $this->format;
	}

    final public function __construct(AdapterInterface $adapter, Graph $graph, FormatsInterface $formats, string $url)
    {
        $this->adapter = $adapter;
        $this->formats = $formats;
        $this->graph = $graph;
        $this->url = $url;
    }

    // =========================================================================

    final public function write($path, $contents, Config $config)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function writeStream($path, $resource, Config $config)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function update($path, $contents, Config $config)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function updateStream($path, $resource, Config $config)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function rename($path, $newpath)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function copy($path, $newpath)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function delete($path)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function deleteDir($dirname)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function createDir($dirname, Config $config)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function setVisibility($path, $visibility)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function has($path)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function read($path)
    {
        $format = $this->resetFormat();
        return $format !== ''
            ? [
                'type' => 'file',
                'path' => $path,
                'contents' => $this->convertedContents($path, $format),
            ]
            : call_user_func_array([$this->adapter, __FUNCTION__], func_get_args())
        ;
    }

    final public function readStream($path)
    {
        // @TODO: Change to stream?
        return $this->read($path);
    }

    final public function listContents($directory = '', $recursive = false)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function getMetadata($path)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function getSize($path)
    {
        // @TODO: For convert request, get contents, convert and count size
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function getMimeType($path)
    {
        $format = $this->resetFormat();
        $extension = $this->getExtension($path);
        $possibleFormat = $this->formats->getFormatForExtension($extension);

        $mimeType = $this->adapter->getMimetype($path);

        if ($format !== '') {
            $mimeType['mimetype'] = $this->formats->getMimeForFormat($format);
        } elseif ($possibleFormat !== '' && $mimeType['mimetype'] === 'text/plain') {
            $mimeType['mimetype'] = $possibleFormat;
        }

        return $mimeType;
    }

    final public function getTimestamp($path)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    final public function getVisibility($path)
    {
        return call_user_func_array([$this->adapter, __FUNCTION__], func_get_args());
    }

    // =========================================================================

    private function convertedContents($path, $format)
    {
        $originalExtension = $this->getExtension($path);
        $originalContents = $this->getOriginalContents($path);
        $originalFormat = $this->formats->getFormatForExtension($originalExtension);

		if ($originalFormat == $format) {
			return $originalContents;
		}

		try {
			switch($originalFormat) {
				case "jsonld":
                    $graph = $this->getGraph();
					// FIXME: parsing json gives warnings, so we're suppressing those for now.
					$graph->parse($originalContents, "jsonld", $this->url);
					switch ($format) {
						default:
							$contents = $graph->serialise($format);
							// FIXME: we should not remove the xsd namespace, but couldn't find a way yet to prevent the serialiser from adding them. xsd namespace;
							$contents = preg_replace("/\^\^xsd:string /", "", $contents);
							$contents = str_replace("@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .\n", "", $contents);
						break;
					}
				break;
				default:
                    $graph = $this->getGraph();
					// FIXME: parsing json gives warnings, so we're suppressing those for now.
					@$graph->parse($originalContents, "guess", $this->url); // FIXME: guessing here helps pass another test, but we really should provide a correct format.
					switch ($format) {
						case "jsonld":
							// We need to get the expanded version of the json-ld, but easyRdf doesn't provide an option for that, so we call this directly.
							$contents = $graph->serialise($format);
							$jsonDoc = \ML\JsonLD\JsonLD::expand($contents);
							$contents = \ML\JsonLD\JsonLD::toString($jsonDoc);
						break;
						default:
							$contents = $graph->serialise($format);
						break;
					}
				break;
			}
		} catch (EasyRdf_Exception $exception) {
            throw Exception::create(self::ERROR_COULD_NOT_CONVERT, [
				'file' => $path,
				'format' => $format,
				'error' => $exception->getMessage(),
			], $exception);
		}

        return $contents;
    }

    private function getExtension(string $path) : string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    private function getOriginalContents($path)
    {
        $converted = $this->adapter->read($path);

        return $converted['contents'];
    }

    private function resetFormat() : string
    {
        $format = $this->format;

        $this->format = '';

        return $format;
    }
}
