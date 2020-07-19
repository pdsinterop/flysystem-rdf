# Flysystem RDF Converter Plugin

[![PDS Interop][pdsinterop-shield]][pdsinterop-site]
[![Project stage: Development][project-stage-badge: Development]][project-stage-page]
[![License][license-shield]][license-link]
[![Latest Version][version-shield]][version-link]
[![standard-readme compliant][standard-readme-shield]][standard-readme-link]
![Maintained][maintained-shield]

_Flysystem plugin to transform RDF data between various serialization formats._

When using RDF, you will seen notice there are several different popular 
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

To use this package, instantiate the plugin and add it to a Flysystem filesystem.

The function `readRdf` can then be called to get RDF files in a specific format:

```php
<?php declare(strict_types=1);

$adapter = new League\Flysystem\Adapter\Local('/path/to/files/');
$filesystem = new League\Flysystem\Filesystem($adapter);
$graph = new \EasyRdf_Graph();
$plugin = new \Pdsinterop\Rdf\Flysystem\Plugin\ReadRdf($graph);

$filesystem->addPlugin($plugin);

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
