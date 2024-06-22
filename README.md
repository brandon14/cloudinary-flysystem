<!-- markdownlint-disable MD033 -->
<p align="center">
  <a href="https://packagist.org/packages/brandon14/cloudinary-flysystem" target="_blank"><img alt="Packagist PHP Version" src="https://img.shields.io/packagist/dependency-v/brandon14/cloudinary-flysystem/php?style=for-the-badge&cacheSeconds=3600"></a>
</p>
<p align="center">
  <a href="https://github.com/brandon14/cloudinary-flysystem/actions/workflows/run-tests.yml" target="_blank"><img alt="GitHub Workflow Status (with event)" src="https://img.shields.io/github/actions/workflow/status/brandon14/cloudinary-flysystem/run-tests.yml?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://codeclimate.com/github/brandon14/cloudinary-flysystem/maintainability" target="_blank"><img alt="Code Climate maintainability" src="https://img.shields.io/codeclimate/maintainability-percentage/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://codecov.io/gh/brandon14/cloudinary-flysystem" target="_blank"><img alt="Codecov" src="https://img.shields.io/codecov/c/github/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/cloudinary-flysystem/blob/main/LICENSE" target="_blank"><img alt="GitHub" src="https://img.shields.io/github/license/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
</p>
<p align="center">
  <a href="https://github.com/brandon14/cloudinary-flysystem/issues" target="_blank"><img alt="GitHub issues" src="https://img.shields.io/github/issues/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/cloudinary-flysystem/issues?q=is%3Aissue+is%3Aclosed" target="_blank"><img alt="GitHub closed issues" src="https://img.shields.io/github/issues-closed/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/cloudinary-flysystem/pulls" target="_blank"><img alt="GitHub pull requests" src="https://img.shields.io/github/issues-pr/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/cloudinary-flysystem/pulls?q=is%3Apr+is%3Aclosed" target="_blank"><img alt="GitHub closed pull requests" src="https://img.shields.io/github/issues-pr-closed/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
</p>
<p align="center">
  <a href="https://github.com/brandon14/cloudinary-flysystem/releases" target="_blank"><img alt="GitHub release (with filter)" src="https://img.shields.io/github/v/release/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/cloudinary-flysystem/commits/main" target="_blank"><img alt="GitHub commit activity (branch)" src="https://img.shields.io/github/commit-activity/m/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
  <a href="https://github.com/brandon14/cloudinary-flysystem/commits/main" target="_blank"><img alt="GitHub last commit (by committer)" src="https://img.shields.io/github/last-commit/brandon14/cloudinary-flysystem?style=for-the-badge&cacheSeconds=3600">
  </a>
</p>
<!-- markdownlint-enable MD033 -->

# brandon14/cloudinary-flysystem

## Source code for [brandon14/cloudinary-flysystem](https://github.com/brandon14/cloudinary-flysystem)

## Table of Contents

1. [Requirements](https://github.com/brandon14/cloudinary-flysystem#requirements)
2. [Purpose](https://github.com/brandon14/cloudinary-flysystem#purpose)
3. [Installation](https://github.com/brandon14/cloudinary-flysystem#installation)
4. [Usage](https://github.com/brandon14/cloudinary-flysystem#usage)
5. [Standards](https://github.com/brandon14/cloudinary-flysystem#standards)
6. [Coverage](https://github.com/brandon14/cloudinary-flysystem#coverage)
7. [Documentation](https://github.com/brandon14/cloudinary-flysystem#documentation)
8. [Contributing](https://github.com/brandon14/cloudinary-flysystem#contributing)
9. [Versioning](https://github.com/brandon14/cloudinary-flysystem#versioning)
10. [Security Vulnerabilities](https://github.com/brandon14/cloudinary-flysystem#security-vulnerabilities)

## Requirements

| Dependency                                                                            | Version                                        |
|---------------------------------------------------------------------------------------|------------------------------------------------|
| [PHP](https://secure.php.net/)                                                        | ^7.2.5 &#124;&#124; ^8.0                       |
| [cloudinary/cloudinary_php](https://packagist.org/packages/cloudinary/cloudinary_php) | ^2.0.0                                         |
| [league/flysystem](https://packagist.org/packages/league/flysystem)                   | ^2.0.0 &#124;&#124; ^3.0.0                     |
| [psr/log](https://packagist.org/packages/psr/log)                                     | ^1.0.0 &#124;&#124; ^2.0.0 &#124;&#124; ^3.0.0 |
| [ext-json](https://pecl.php.net/package/JSON)                                         | *                                              |

## Beta Package Note

This is a beta release package and should not be considered stable for production
environments.

## Purpose

[Cloudinary](https://cloudinary.com/) is a cloud-based image and video management
service. They handle image optimization and transformations via their robust API.
This package adapts the Cloudinary API to a [Flysystem Adapter](https://flysystem.thephpleague.com/docs/)
to allow for a seamless experience in fetching and uploading resources from your PHP
application to Cloudinary's services.

If you are wanting to use this in your [Laravel](https://laravel.com/) application I
have provided a package to seamlessly integrate this package into your Laravel 9 and
up application [here](https://github.com/brandon14/cloudinary-flysystem-laravel).

## Installation

```bash
composer require brandon14/cloudinary-flysystem
```

## Usage

This Flysystem adapter provides robust configuration options and seamless integration
with any PSR-3 compatible logging system. In order to get started, first you need to
configure the adapter by creating a Cloudinary SDK instance.

```php
<?php

use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary();
```

You can configure this SDK instance however you wish according to their documentation
found [here](https://cloudinary.com/documentation/php_integration).

Next, you can optionally choose to configure the Flysystem adapter.

```php
use Brandon14\CloudinaryFlysystem\Contracts\Configuration;

// Using default configuration options.
$config = new Configuration();
```

This configuration object allows you to configure the behavior of the adapter
including setting an adapter-wide folder prefix, an upload preset to use, whether to
fetch raw resources (i.e. text files, etc.) alongside image and video resources,
configuration of which extra metadata fields to fetch from Cloudinary's API for the
resources, and configuring how to map Flysystem's visibility options to Cloudinary's
resource visibility.

Once you have everything configured how like, you can then create the Flysystem
adapter.

```php
use Cloudinary\Cloudinary;
use League\Flysystem\Filesystem;use Brandon14\CloudinaryFlysystem\CloudinaryAdapter;
use Brandon14\CloudinaryFlysystem\Contracts\Configuration;

// Create Cloudinary SDK instance and configure to your liking.
$cloudinary = new Cloudinary();
$config = new Configuration();
// Set any configuration options.

// Create the Flysystem adapter.
$adapter = new CloudinaryAdapter($client, $config);
// Create Flysystem filesystem.
$filesystem = new Filesystem($adapter);
// Use Filesystem as needed.
$filesystem->listContents('/');
```

I will eventually fully document this project but for now, the configuration class is
pretty well documented in the code for reference to the options available. Also, the
adapter is configured to be able to tune the mime-type detection, using Flysystem's
`league/mime-type-detection` library.

This adapter implements the PSR-3 `LoggerAwareInterface` so that if you want to
provide a compatible logger (i.e. `monolog`), you can do so. It logs out at debug
level most information occurring inside the adapter, and exceptions caught and thrown
will be logged out at either error (for exception caught in private methods that
might not necessarily lead to an exception being thrown from one of the public 
methods) or critical (for exceptions that will make it out of the adapter through the
public methods).

```php
use Monolog\Logger;use Cloudinary\Cloudinary;
use League\Flysystem\Filesystem;use Brandon14\CloudinaryFlysystem\CloudinaryAdapter;
use Brandon14\CloudinaryFlysystem\Contracts\Configuration;

// Create Cloudinary SDK instance and configure to your liking.
$cloudinary = new Cloudinary();
$config = new Configuration();
// Set any configuration options.

// Create PSR-3 logger.
$logger = new Logger('cloudinary');

// Create the Flysystem adapter.
$adapter = new CloudinaryAdapter($client, $config);
$adapter->setLogger($logger);

// Enable logging.
$adapter->enableLogging();
```

**NOTE**: Cloudinary does not support writing empty files, so if you get an exception
trying to write an empty file, this is the reason. If you see in the tests, I override
the default Flysystem test case for writing an empty file to expect an exception.

**NOTE**: If you have issues uploading file types, please open an issue. Cloudinary
may handle some files as image/media files instead of raw files. I ran into an issue
with PDF files, where it treats them as an image, so I had to add a case in 
determining the upload resource type for PDF files to upload it as an image. It will
still be accessible as a PDF file, just that Cloudinary treats it as an image 
resource. There may be other file types that are handled this way, so if you run into
any please open an issue, so we can resolve them as I can't feasibly test every single
file type.

I have gone through the supported media formats from Cloudinary found
[here](https://cloudinary.com/documentation/image_transformations#supported_image_formats) and [here](https://cloudinary.com/documentation/video_manipulation_and_delivery#supported_video_formats)
and included as many as I could. The default mime type detector that Flysystem ships
with doesn't include mappings for some of the supported audio and video formats. As
long as the mime type contains image it will be handled, provided you supply a
MimeTypeDetector that can handle those file types.

Image Types Not Supported By Mime Type Detector:

| Format                            | Extensions |
|-----------------------------------|------------|
| BW (Browzwear file)               | .bw        |
| DNG (Digital Negative)            | .dng       |
| FBX (Filmbox)                     | .fbx       |
| FLIF (Free Lossless Image Format) | .flif      |
| InDesign                          | .indd      |
| PLY                               | .ply       |
| U3MA (Fabric file)                | .u3ma      |
| Raw image files                   | .arw, .cr2 |

Video Types Not Supported By Mime Type Detector:

| Format                  | Extensions                   |
|-------------------------|------------------------------|
| MPEG-2 Transport Stream | .m2ts (others are supported) |

Cloudinary also states to upload audio via the video resource type, and I haven't had
a chance to test this very thoroughly, so it may be possible that issues will arise
when uploading audio files.

If you know the official mime types of these files, you can either make an issue with
the mime type and I can add it so that it handles it correctly when using a
MimeTypeDetector that supports it, or you can open up a PR with the fixes. For
reference, the MimeTypeDetector is used in the method `getResourceType()` where it
gets the mime type from the detector, then uses the CloudinaryMimeTypeConverter
instance to transform it into a Cloudinary resource type of 'image', 'video' or
'raw'. The default converter can be modified with new mime types, or you can
provide a customized implementation of the converter to the adapter.

**NOTE**: Cloudinary's API will currently create a new file with the same name
whenever you overwrite a file that already exists with a different visibility than it
originally had. My solution to this is to check for a file existing before writing
the file, and if it exists, delete it so the file can essentially be overwritten with
the new file.

### Upload Presets

TODO: Add in notes about how to properly handle upload presets.

## Standards

We strive to meet the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style for PHP projects, and enforce our
coding standard via the [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) linting tool. Our ruleset can be
found in the `.php-cs-fixer.dist.php` file.

## Coverage

The latest code coverage information can be found via [Codecov](https://codecov.io/gh/brandon14/cloudinary-flysystem). We
strive to maintain 100% coverage across the entire Flysystem adapter, so if you are
[contributing](https://github.com/brandon14/cloudinary-flysystem#contributing), please make sure to include tests for new
code added.

## Documentation

Documentation to this project can be found [here](https://brandon14.github.io/cloudinary-flysystem/).

## Contributing

Got something you want to add? Found a bug or otherwise bad code? Feel free to submit pull
requests to add in new features, fix bugs, or clean things up. Just be sure to follow the
[Code of Conduct](https://github.com/brandon14/cloudinary-flysystem/blob/master/.github/CODE_OF_CONDUCT.md)
and [Contributing Guide](https://github.com/brandon14/cloudinary-flysystem/blob/master/.github/CONTRIBUTING.md),
and we encourage creating clean and well described pull requests if possible.

If you notice an issues with the library or want to suggest new features, feel free to create issues appropriately using
the [issue tracker](https://github.com/brandon14/cloudinary-flysystem/issues).

In order to run the tests, it is recommended that you sign up for a Cloudinary account (it's a free service), and use that
account to run the full integration tests. In order to do that, you will need to copy `.env.example` to `.env` and fill
in the variables using the details in your account. The integration tests will use random prefixed directories and clean
everything up before and after the tests.

## Versioning

php-licenses-generator uses [semantic versioning](https://semver.org/) that looks like `MAJOR.MINOR.PATCH`.

Major version changes will include backwards-incompatible changes and may require refactoring of projects using it.
Minor version changes will include backwards-compatible new features and changes and will not break existing usages.
Patch version changes will include backwards-compatible bug and security fixes, and should be updated as soon as
possible.

## Security Vulnerabilities

If you discover a vulnerability within this package, please email Brandon Clothier via
[brandon14125@gmail.com](mailto:brandon14125@gmail.com). All security vulnerabilities will be promptly
addressed.

This code is released under the MIT license.

Copyright &copy; 2024 Brandon Clothier

[![X (formerly Twitter) Follow](https://img.shields.io/twitter/follow/inhal3exh4le?style=for-the-badge&logo=twitter&cacheSeconds=3600)](https://twitter.com/intent/follow?screen_name=inhal3exh4le)
