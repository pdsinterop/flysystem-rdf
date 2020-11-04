# Flysystem RDF Converter Plugin

[![PDS Interop][pdsinterop-shield]][pdsinterop-site]
[![Project stage: Development][project-stage-badge: Development]][project-stage-page]
[![License][license-shield]][license-link]
[![Latest Version][version-shield]][version-link]
[![standard-readme compliant][standard-readme-shield]][standard-readme-link]
![Maintained][maintained-shield]

_Flysystem plugin to transform RDF data between various serialization formats._

When using RDF, you will notice there are several different popular 
serialization formats. Instead of having to store data in multiple formats, it
is easier to store the data in one format and convert it to others as needed.

This project contains a plugin to be used with Flysystem to do just that.

For the conversion EasyRDF is used. Any formats supported by EasyRDF are 
available from this plugin. 

Currently supported formats are:

- JSON-LD
- N-triples
- Notation 3 / N3
- RDF/XML
- Turtle

## Table of Contents

<!-- toc -->

- [Background](#background)
- [Installation](#installation)
- [Usage](#usage)
    - [Adapter](#adapter)
    - [Plugin](#plugin)
- [Contribute](#contribute)
- [License](#license)

<!-- tocstop -->

## Background

This project is part of the PHP stack of projects by PDS Interop. It is used by
both the Solid-Nextcloud app and the standalone PHP Solid server.

As the functionality seemed useful for other projects, it was implemented as a
separate package.

## Installation

The advised install method is through composer:

```
composer require pdsinterop/flysystem-rdf
```

PHP version 7.1 and higher is supported. The [`mbstring`](https://www.php.net/manual/en/book.mbstring.php)
extension needs to be enabled in order for this package to work.

## Usage

This package offers features to read files from a Filesystem that have been
stored in one RDF format as another format.

These features are provided by a plugin and an adapter.

The plugin is best suited for light-weight scenarios where all you want to do is
convert a file to another format.

The adapter is best suited for full-featured usage of Flysystem, as it also 
handles calls to `has` and `getMimeType` correctly (which the plugin does not) 

### Adapter

To use the adapter, instantiate it, add it to a Flysystem filesystem and add the
helper plugin.

```php
<?php

// Create Formats objects
$formats = new \Pdsinterop\Rdf\Formats();

// Use an adapter of your choice
$adapter = new League\Flysystem\Adapter\Local('/path/to/files/');

// Create the RDF Adapter
$rdfAdapter = new \Pdsinterop\Rdf\Flysystem\Adapter\Rdf(
    $adapter,
    new \EasyRdf_Graph(),
    $formats,
    'server'
);

// Create Flysystem as usual, adding the RDF Adapter
$filesystem = new League\Flysystem\Filesystem($adapter);

// Add the `AsMime` plugin to convert contents based on a provided MIME type, 
$filesystem->addPlugin(new \Pdsinterop\Rdf\Flysystem\Plugin\AsMime($formats));

// Read the contents of a file in the format it was stored in
$content = $filesystem->read('/foaf.rdf');

// Read the contents of a file in another format from what was stored in
$convertedContents = $filesystem
    ->asMime('text/turtle')
    ->read('/foaf.rdf');

// Get the MIME type of the format the requested mime-type would return
// This is especially useful for RDF formats that can be requested with several
// different MIME types.
$convertedMimeType = $filesystem
    ->asMime('text/turtle')
    ->getMimetype('/foaf.rdf');

// This also works for `has`
$hasConvertedContents = $filesystem
    ->asMime('text/turtle')
    ->has('/foaf.rdf');

```

### Plugin

To use the plugin, instantiate it and add it to a Flysystem filesystem.

The function `readRdf` can then be called to get RDF files in a specific format:

```php
<?php

// Create Flysystem as usual, adding an adapter of your choice
$adapter = new League\Flysystem\Adapter\Local('/path/to/files/');
$filesystem = new League\Flysystem\Filesystem($adapter);

// create and add the RdF Plugin
$plugin = new \Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf(new \EasyRdf_Graph());
$filesystem->addPlugin($plugin);

// Read the contents of a RDF file in another format from what was stored in
$content = $filesystem->readRdf('/foaf.rdf', \Pdsinterop\Rdf\Enum\Format::TURTLE);
```

## Contribute

Questions or feedback can be given by [opening an issue on GitHub](https://github.com/pdsinterop/flysystem-rdf/issues).

All PDS Interop projects are open source and community-friendly. 
Any contribution is welcome!
For more details read the [contribution guidelines](contributing.md).

All PDS Interop projects adhere to [the Code Manifesto](http://codemanifesto.com)
as its [code-of-conduct](CODE_OF_CONDUCT.md). Contributors are expected to abide by its terms.

There is [a list of all contributors on GitHub][contributors-page].

For a list of changes see the [CHANGELOG](CHANGELOG.md) or the GitHub releases page.

## License

All code created by PDS Interop is licensed under the [MIT License][license-link].

[contributors-page]:  https://github.com/pdsinterop/flysystem-rdf/contributors
[license-link]: ./LICENSE
[license-shield]: https://img.shields.io/github/license/pdsinterop/flysystem-rdf.svg
[maintained-shield]: https://img.shields.io/maintenance/yes/2020
[pdsinterop-shield]: https://img.shields.io/badge/-PDS%20Interop-gray.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii01IC01IDExMCAxMTAiIGZpbGw9IiNGRkYiIHN0cm9rZS13aWR0aD0iMCI+CiAgICA8cGF0aCBkPSJNLTEgNTJoMTdhMzcuNSAzNC41IDAgMDAyNS41IDMxLjE1di0xMy43NWEyMC43NSAyMSAwIDAxOC41LTQwLjI1IDIwLjc1IDIxIDAgMDE4LjUgNDAuMjV2MTMuNzVhMzcgMzQuNSAwIDAwMjUuNS0zMS4xNWgxN2EyMiAyMS4xNSAwIDAxLTEwMiAweiIvPgogICAgPHBhdGggZD0iTSAxMDEgNDhhMi43NyAyLjY3IDAgMDAtMTAyIDBoIDE3YTIuOTcgMi44IDAgMDE2OCAweiIvPgo8L3N2Zz4K
[pdsinterop-site]: https://pdsinterop.org/
[project-stage-badge: Development]: https://img.shields.io/badge/Project%20Stage-Development-yellowgreen.svg
[project-stage-page]: https://blog.pother.ca/project-stages/
[standard-readme-link]: https://github.com/RichardLitt/standard-readme
[standard-readme-shield]: https://img.shields.io/badge/readme%20style-standard-brightgreen.svg
[version-link]: https://packagist.org/packages/pdsinterop/flysystem-rdf
[version-shield]: https://img.shields.io/github/v/release/pdsinterop/flysystem-rdf?sort=semver
