<?php

/**
 * This file is part of the brandon14/cloudinary-flysystem package.
 *
 * MIT License
 *
 * Copyright (c) 2024 Brandon Clothier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

/** @noinspection EfferentObjectCouplingInspection */
/** @noinspection PhpClassNamingConventionInspection */

declare(strict_types=1);

namespace Brandon14\CloudinaryFlysystem;

use Throwable;

use function trim;
use function count;
use function ltrim;
use function rtrim;
use function implode;
use function tmpfile;
use function in_array;
use function is_array;
use function pathinfo;
use function get_class;
use function is_string;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function strtotime;
use function hash_algos;
use function array_chunk;
use function array_merge;
use function file_exists;
use function str_replace;

use Cloudinary\Cloudinary;
use League\Flysystem\Config;
use Psr\Log\LoggerInterface;

use const PATHINFO_EXTENSION;

use UnexpectedValueException;

use function file_get_contents;

use League\Flysystem\Visibility;
use Cloudinary\Api\BaseApiClient;

use function stream_get_contents;
use function stream_get_meta_data;

use League\Flysystem\PathPrefixer;
use League\Flysystem\FileAttributes;
use Cloudinary\Api\Exception\NotFound;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToProvideChecksum;
use League\Flysystem\UnableToRetrieveMetadata;
use League\MimeTypeDetection\MimeTypeDetector;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\UnableToCheckFileExistence;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\Flysystem\UnableToCheckDirectoryExistence;
use Brandon14\CloudinaryFlysystem\Concerns\HandlesLogging;
use Brandon14\CloudinaryFlysystem\Contracts\Configuration;
use Brandon14\CloudinaryFlysystem\Contracts\Enums\AccessModes;
use Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes;
use Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter;
use Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions;
use Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter;
use Brandon14\CloudinaryFlysystem\Contracts\Enums\ChecksumOptions;
use Cloudinary\Configuration\Configuration as CloudinaryConfiguration;
use Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter as CloudinaryAdapterInterface;

/**
 * {@link \League\Flysystem\Filesystem} V2/V3 adapter for Cloudinary's cloud API service. This adapter also implements
 * the PSR {@link \Psr\Log\LoggerAwareInterface} so if a logger is provided to the adapter (using either the
 * {@link setLogger()} method, or by passing a PSR logger implementation in via the constructor) it will automatically
 * log out events inside the adapter to aid in debugging.
 *
 * @see https://github.com/cloudinary/cloudinary_php
 * @see https://github.com/thephpleague/flysystem
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class CloudinaryAdapter implements CloudinaryAdapterInterface
{
    use HandlesLogging;

    /**
     * Cloudinary instance.
     *
     * @var \Cloudinary\Cloudinary
     */
    private $client;

    /**
     * Adapter configuration options.
     *
     * @var \Brandon14\CloudinaryFlysystem\Contracts\Configuration
     */
    private $configuration;

    /**
     * Cloudinary visibility converter.
     *
     * @noinspection PhpPropertyNamingConventionInspection
     *
     * @var \Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter
     */
    private $visibilityConverter;

    /**
     * Cloudinary mime type to resource type converter instance.
     *
     * @noinspection PhpPropertyNamingConventionInspection
     *
     * @var \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter
     */
    private $mimeTypeConverter;

    /**
     * Flysystem mimetype detector instance.
     *
     * @var \League\MimeTypeDetection\MimeTypeDetector
     */
    private $mimeTypeDetector;

    /**
     * Constructs a new CloudinaryAdapter.
     *
     * @see https://github.com/cloudinary/cloudinary_php
     * @see https://github.com/thephpleague/flysystem
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param \Cloudinary\Cloudinary                                            $client              Cloudinary instance
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Configuration|null       $configuration       Adapter configuration options
     * @param \Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter|null $visibilityConverter Visibility converter
     * @param \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter|null   $mimeTypeConverter   Mime type converter
     * @param \League\MimeTypeDetection\MimeTypeDetector|null                   $mimeTypeDetector    Flysystem mimetype detector
     * @param \Psr\Log\LoggerInterface|null                                     $logger              Optional PSR logger instance
     * @param bool                                                              $logging             Whether to enable logging
     *
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\NoValidLoggerProvidedException
     *
     * @return void
     */
    public function __construct(
        Cloudinary $client,
        ?Configuration $configuration = null,
        ?VisibilityConverter $visibilityConverter = null,
        ?MimeTypeDetector $mimeTypeDetector = null,
        ?MimeTypeConverter $mimeTypeConverter = null,
        ?LoggerInterface $logger = null,
        bool $logging = false
    ) {
        $this->setClient($client)
            ->setConfiguration($configuration)
            ->setVisibilityConverter($visibilityConverter)
            ->setMimeTypeConverter($mimeTypeConverter)
            ->setMimeTypeDetector($mimeTypeDetector);

        CloudinaryConfiguration::instance($this->client->configuration);

        if ($logger !== null) {
            $this->setLogger($logger);
        }

        $this->setLogging($logging);
    }

    /**
     * {@inheritDoc}
     */
    public function setClient(Cloudinary $cloudinary): CloudinaryAdapterInterface
    {
        $this->client = $cloudinary;

        CloudinaryConfiguration::instance($this->client->configuration);
        /** @noinspection DisallowWritingIntoStaticPropertiesInspection */
        BaseApiClient::$userPlatform = 'brandon14/cloudinary-flysystem';

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getClient(): Cloudinary
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfiguration(?Configuration $configuration = null): CloudinaryAdapterInterface
    {
        $this->configuration = $configuration ?? Configuration::default();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function setVisibilityConverter(?VisibilityConverter $visibilityConverter): CloudinaryAdapterInterface
    {
        $this->visibilityConverter = $visibilityConverter ?? new CloudinaryVisibilityConverter();

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function getVisibilityConverter(): VisibilityConverter
    {
        return $this->visibilityConverter;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function setMimeTypeConverter(?MimeTypeConverter $mimeTypeConverter): CloudinaryAdapterInterface
    {
        $this->mimeTypeConverter = $mimeTypeConverter ?? new CloudinaryMimeTypeConverter();

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function getMimeTypeConverter(): MimeTypeConverter
    {
        return $this->mimeTypeConverter;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function setMimeTypeDetector(?MimeTypeDetector $mimeTypeDetector): CloudinaryAdapterInterface
    {
        $this->mimeTypeDetector = $mimeTypeDetector ?? new FinfoMimeTypeDetector();

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function getMimeTypeDetector(): MimeTypeDetector
    {
        return $this->mimeTypeDetector;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @see \Brandon14\CloudinaryFlysystem\Contracts\Enums\ChecksumOptions for Flysystem checksum options
     */
    public function checksum(string $path, Config $config): string
    {
        $algo = $config->get(ChecksumOptions::CHECKSUM_ALGORITHM()->getValue(), 'etag');
        $availableAlgos = hash_algos();
        $availableAlgos[] = 'etag';

        // Guard for supported algos.
        if (! in_array($algo, $availableAlgos, true)) {
            $algos = implode(', ', $availableAlgos);

            $message = "Checksum algorithm [{$algo}' is not supported. Must be one of [{$algos}].";

            $this->critical($message, [
                'available_algos' => $availableAlgos,
                'checksum_algo' => $algo,
                'config' => $config->toArray(),
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw new ChecksumAlgoIsNotSupported($message);
        }

        $this->debug("Getting checksum for [{$path}] with algo [{$algo}].", [
            'available_algos' => $availableAlgos,
            'checksum_algo' => $algo,
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Get file metadata, so we can get the etag or url to calculate checksum.
        try {
            $fileMetadata = $this->getFileMetadata($path, StorageAttributes::ATTRIBUTE_EXTRA_METADATA);
            $fileMetadata = $fileMetadata->extraMetadata();
        } catch (Throwable $exception) {
            $this->critical("Failed to provide checksum for [{$path}] with message [{$exception->getMessage()}].", [
                'available_algos' => $availableAlgos,
                'checksum_algo' => $algo,
                'config' => $config->toArray(),
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw new UnableToProvideChecksum($exception->getMessage(), $path, $exception);
        }

        // Handle etag by getting value from Cloudinary API.
        if ($algo === 'etag') {
            try {
                $etag = $fileMetadata['etag'] ?? '';

                if (! is_string($fileMetadata['etag']) || empty($fileMetadata['etag'])) {
                    throw new UnableToProvideChecksum("Invalid etag found [{$etag}].", $path);
                }

                return (string) $etag;
            } catch (Throwable $exception) {
                $this->critical("Failed to provide checksum for [{$path}] with message [{$exception->getMessage()}].", [
                    'available_algos' => $availableAlgos,
                    'checksum_algo' => $algo,
                    'config' => $config->toArray(),
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'path' => $path,
                ]);

                throw new UnableToProvideChecksum($exception->getMessage(), $path, $exception);
            }
        }

        // For all other algos, we read in the file data and compute the checksum.
        try {
            $url = $fileMetadata['url'] ?? '';

            if (! is_string($url) || $url === '') {
                throw new UnableToProvideChecksum("Invalid URL found [{$url}].", $path);
            }

            $hash = hash_file($algo, $url);

            if ($hash === false || $hash === '') {
                throw new UnableToProvideChecksum("Invalid hash for [{$path}].", $path);
            }

            return $hash;
        } catch (Throwable $exception) {
            $this->critical("Failed to provide checksum for [{$path}] with message [{$exception->getMessage()}].", [
                'available_algos' => $availableAlgos,
                'checksum_algo' => $algo,
                'config' => $config->toArray(),
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw new UnableToProvideChecksum($exception->getMessage(), $path, $exception);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function publicUrl(string $path, Config $config): string
    {
        $this->debug("Getting public URL for [{$path}].", [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'path' => $path,
        ]);

        try {
            $metadata = $this->getFileMetadata($path, StorageAttributes::ATTRIBUTE_EXTRA_METADATA);

            $metadata = $metadata->extraMetadata();

            if (! isset($metadata['url']) || ! is_string($metadata['url'])) {
                throw new UnableToGeneratePublicUrl('URL not found.', $path);
            }

            return (string) $metadata['url'];
        } catch (Throwable $exception) {
            $this->critical("Failed to generate public URL for [{$path}] with message [{$exception->getMessage()}].", [
                'config' => $config->toArray(),
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw UnableToGeneratePublicUrl::dueToError($path, $exception);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @noinspection MultipleReturnStatementsInspection
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function fileExists(string $path): bool
    {
        // Get resource type and normalized public ID from path.
        $resourceType = $this->getResourceType($path);
        $publicId = $this->getPublicId($path, $resourceType);

        $this->debug("Checking if [{$path}] exists.", [
            'method' => __METHOD__,
            'path' => $path,
            'public_id' => $publicId,
            'resource_type' => $resourceType,
        ]);

        // Check to see if the file exists via the admin API.
        try {
            $resource = (array) $this->client->adminApi()->asset($publicId, ['resource_type' => $resourceType]);

            // Make sure we have a URL returned back from the API.
            if (! isset($resource['secure_url'])) {
                return false;
            }

            return true;
        } catch (Throwable $exception) {
            // If we get a NotFound API error, we assume the file doesn't exist and return false.
            if ($exception instanceof NotFound) {
                // Log at debug, since if a file doesn't exist, the API call is expected to throw, so not unusual.
                $this->debug(
                    "File does not exist with message [{$exception->getMessage()}].",
                    [
                        'exception' => $exception,
                        'method' => __METHOD__,
                        'path' => $path,
                        'public_id' => $publicId,
                        'resource_type' => $resourceType,
                    ]
                );

                return false;
            }

            // Otherwise, some exception occurred so log and throw Flysystem exception.
            $this->critical("Failed to check for file existence with message [{$exception->getMessage()}].", [
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
                'public_id' => $publicId,
                'resource_type' => $resourceType,
            ]);

            throw UnableToCheckFileExistence::forLocation($path, $exception);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @noinspection MultipleReturnStatementsInspection
     */
    public function directoryExists(string $path): bool
    {
        $this->debug("Checking if [{$path}] exists.", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Normalize path.
        $path = $this->normalizePath($path, true);

        if ($path === '/') {
            $this->debug('Root directory will always exist on Cloudinary.', [
                'method' => __METHOD__,
                'path' => $path,
            ]);

            // For Cloudinary, the root path will always exist. The subfolders API will only return subfolders in the
            // root directory.
            return true;
        }

        $dirParts = new DirectoryParts($path);

        try {
            $response = (array) $this->client->adminApi()->subFolders($dirParts->dirName());
        } catch (Throwable $exception) {
            // If we get a NotFound API error, we assume the directory doesn't exist and return false.
            if ($exception instanceof NotFound) {
                // Log at debug, since if a directory doesn't exist, the API call is expected to throw, so not unusual.
                $this->debug(
                    "Directory does not exist with message [{$exception->getMessage()}].",
                    [
                        'exception' => $exception,
                        'method' => __METHOD__,
                        'path' => $path,
                    ]
                );

                return false;
            }

            // Otherwise, some exception occurred so log and throw Flysystem exception.
            $this->critical("Failed to check for directory existence with message [{$exception->getMessage()}].", [
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw UnableToCheckDirectoryExistence::forLocation($path, $exception);
        }

        // Make sure we have a list of folders from the folder search.
        if (! isset($response['folders']) || ! is_array($response['folders']) || count($response['folders']) === 0) {
            $this->warning(
                'Failed to get a list of folders from API.',
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            // If we don't get a list of folders back from Cloudinary's API assume the directory does not exist.
            return false;
        }

        // Check each subfolder and see if we find a match.
        foreach ($response['folders'] as $folder) {
            if (isset($folder['name']) && $folder['name'] === $dirParts->baseName()) {
                return true;
            }
        }

        $this->debug(
            "Directory [{$path}] does not exist.",
            [
                'method' => __METHOD__,
                'path' => $path,
            ]
        );

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     *
     * @throws UnexpectedValueException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidVisibilityException
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload
     * @see \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->debug("Writing file [{$path}].", [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Create temp file to write file contents to.
        if (($tempFile = tmpfile()) === false) {
            $message = 'Failed to create temporary file.';

            $this->critical(
                $message,
                [
                    'config' => $config->toArray(),
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToWriteFile::atLocation($path, $message);
        }

        // Write contents.
        if (fwrite($tempFile, $contents) === false) {
            $message = 'Failed to write to temporary file.';

            $this->critical(
                $message,
                [
                    'config' => $config->toArray(),
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToWriteFile::atLocation($path, $message);
        }

        // Upload file.
        $this->writeStream($path, $tempFile, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @noinspection MissingParameterTypeDeclarationInspection
     *
     * @see \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions for Flysystem write options
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload
     *
     * @throws UnexpectedValueException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidVisibilityException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->debug("Writing (stream) file [{$path}].", [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Check if the file exists, because if so, we need to delete the original file and upload the new contents.
        try {
            $fileExists = $this->fileExists($path);
        } catch (Throwable $exception) {
            $message = "Failed to check for existing file [{$path}] with message [{$exception->getMessage()}].";

            $this->critical(
                $message,
                [
                    'config' => $config->toArray(),
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToWriteFile::atLocation($path, $message, $exception);
        }

        if ($fileExists) {
            $this->debug("File currently exists at [{$path}], deleting so we can overwrite with new stream.", [
                'config' => $config->toArray(),
                'method' => __METHOD__,
                'path' => $path,
            ]);

            try {
                $this->delete($path);
            } catch (Throwable $exception) {
                $message = "Failed to delete existing file [{$path}] with message [{$exception->getMessage()}].";

                $this->critical(
                    $message,
                    [
                        'config' => $config->toArray(),
                        'exception' => $exception,
                        'method' => __METHOD__,
                        'path' => $path,
                    ]
                );

                throw UnableToWriteFile::atLocation($path, $message, $exception);
            }
        }

        // Ensure we can read the file contents from the file stream.
        $resource = stream_get_meta_data($contents);

        // Make sure we have a URI for the resource to upload.
        if (! isset($resource['uri'])) {
            $message = 'Failed to get stream URI from resource.';

            $this->critical(
                $message,
                [
                    'config' => $config->toArray(),
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToWriteFile::atLocation($path, $message);
        }

        // Fix for handling php://temp.
        if (mb_strpos($resource['uri'], 'php://') !== false) {
            // Cloudinary's API doesn't seem to like handling streams from php://temp, so for any php:// file resource,
            // we just read the contents into a string and redirect to the normal read method. I know this isn't
            // optimal, but it is a workaround for the odd behavior.
            $this->debug('Handling php:// file by getting contents and calling read().', [
                'config' => $config->toArray(),
                'method' => __METHOD__,
                'path' => $path,
            ]);

            $tempContents = stream_get_contents($contents);

            $this->write($path, $tempContents, $config);

            return;
        }

        $path = (string) $config->get(WriteOptions::PUBLIC_ID()->getValue(), $path);
        $isFile = file_exists($path);
        // Get resource type and upload preset if present.
        $resourceType = (string) $config->get(
            WriteOptions::RESOURCE_TYPE()->getValue(),
            $this->getResourceType($path, $isFile)
        );
        // Get other configuration options.
        $visibility = (string) $config->get(WriteOptions::VISIBILITY()->getValue(), Visibility::PUBLIC);
        $uploadType = $this->visibilityConverter->visibilityToUploadType($visibility)->getValue();
        // Check for specific upload type passed in.
        $uploadType = (string) $config->get(WriteOptions::UPLOAD_TYPE()->getValue(), $uploadType);

        // Get normalized public ID from path and resource type.
        $publicId = $this->getPublicId($path, $resourceType);
        // Get folder from path and use as folder option and asset_folder option.
        $directory = new DirectoryParts($publicId);
        $folder = $directory->dirName();
        // Get base name for file upload. Cloudinary will already prefix the public ID with the folder in which it is
        // uploaded in, so we only need to send the public_id as the basename of the file.
        $publicId = $directory->baseName();

        // Merge in additional options.
        $options = array_merge($this->getWriteOptions($config, $visibility), [
            'public_id' => $publicId,
            'resource_type' => $resourceType,
            // Set upload type from converted visibility.
            'type' => $uploadType,
            'folder' => $folder,
            'asset_folder' => $folder,
        ]);

        // For raw files set the filename override option to handle name correctly.
        if ($resourceType === 'raw') {
            $options['filename_override'] = $publicId;

            $this->debug(
                'Overriding filename for raw file type to handle name correctly.',
                [
                    'config' => $config->toArray(),
                    'method' => __METHOD__,
                    'options' => $options,
                    'path' => $path,
                    'resource_type' => $resourceType,
                ]
            );
        }

        if ($resourceType === 'image') {
            $options = array_merge($options, $this->getImageOnlyWriteOptions($config));

            $this->debug(
                'Adding image-only write options.',
                [
                    'config' => $config->toArray(),
                    'method' => __METHOD__,
                    'options' => $options,
                    'path' => $path,
                    'resource_type' => $resourceType,
                ]
            );
        }

        if ($resourceType === 'video') {
            $options = array_merge($options, $this->getVideoOnlyWriteOptions($config));

            $this->debug(
                'Adding video-only write options.',
                [
                    'config' => $config->toArray(),
                    'method' => __METHOD__,
                    'options' => $options,
                    'path' => $path,
                    'resource_type' => $resourceType,
                ]
            );
        }

        // Attempt to upload file using Cloudinary Upload API.
        try {
            $this->client->uploadApi()->upload($resource['uri'], $options);
        } catch (Throwable $exception) {
            $this->critical(
                "Failed to write file [{$path}] with message [{$exception->getMessage()}].",
                [
                    'config' => $config->toArray(),
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'options' => $options,
                    'path' => $path,
                    'resource_type' => $resourceType,
                    'uri' => $resource['uri'],
                ]
            );

            throw UnableToWriteFile::atLocation($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function read(string $path): string
    {
        $this->debug("Reading [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        try {
            $resource = $this->getResource($path);
        } catch (Throwable $exception) {
            $this->critical("Failed to get resource [{$path}] with message [{$exception->getMessage()}].", [
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw UnableToReadFile::fromLocation($path, $exception->getMessage(), $exception);
        }

        $contents = file_get_contents($resource['secure_url']);

        // Make sure we have file contents.
        if ($contents === false) {
            $message = "Failed to read contents from URI [{$resource['secure_url']}].";

            $this->critical(
                $message,
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToReadFile::fromLocation($path, $message);
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @noinspection MissingReturnTypeInspection
     */
    public function readStream(string $path)
    {
        $this->debug("Reading (stream) [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        $resource = $this->getResource($path);

        $file = fopen($resource['secure_url'], 'rb');

        // Make sure the file is open.
        if ($file === false) {
            $message = "Failed to open file from URI {$resource['secure_url']}";

            $this->critical(
                $message,
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToReadFile::fromLocation($path, $message);
        }

        return $file;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function delete(string $path): void
    {
        $this->debug("Deleting [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Check that the file exists before attempting to delete.
        if (! $this->fileExists($path)) {
            $this->debug("Resource [{$path}] does not exist. Skipping delete.", [
                'method' => __METHOD__,
                'path' => $path,
            ]);

            return;
        }

        // Get normalized path and resource type to delete.
        $resourceType = $this->getResourceType($path);
        $publicId = $this->getPublicId($path, $resourceType);

        $this->deleteResource($path, $publicId, $resourceType);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function deleteDirectory(string $path): void
    {
        $this->debug("Deleting directory [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Normalize directory path.
        $resourceType = $this->getResourceType($path);
        $publicId = $this->getPublicId($path, $resourceType, true);

        $this->debug("Checking for items in [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Get all items in directory and delete them.
        try {
            // We only want to get the files here, as we can delete the base directory, and it will delete all nested
            // folders and this will cut down on API calls.
            $resources = $this->getAllFiles(rtrim($publicId, '/'), true);
        } catch (Throwable $exception) {
            $this->critical("Failed to get resources for [{$path}] with message [{$exception->getMessage()}].", [
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw UnableToDeleteDirectory::atLocation($path, $exception->getMessage(), $exception);
        }

        // Store each public ID for each resource type to delete, we will later chunk these out in groups of 100 and
        // batch delete them.
        /** @noinspection PhpVariableNamingConventionInspection */
        $resourcesToDelete = [
            'image' => [],
            'video' => [],
            'raw' => [],
        ];

        // Get all the resources and nested directories we need to delete.
        foreach ($resources as $resource) {
            $resourcePath = $resource->path();
            $type = $this->getResourceType($resourcePath);
            // Add to cache of resources to delete of a given type.
            /** @noinspection PhpVariableNamingConventionInspection */
            $resourcesToDelete[$type][] = $this->getPublicId($resourcePath, $type);
        }

        // Delete each resource of each type. We have to handle each resource type individually as each different type
        // needs its own API call.
        foreach ($resourcesToDelete as $type => $publicIds) {
            try {
                // This will chunk the public IDs into arrays of 100 each and batch delete them since Cloudinary can
                // support up to 100 deletes ata  time for a given resource type.
                $this->deleteResources($path, $publicIds, $type);
            } catch (Throwable $exception) {
                $this->critical("Failed to delete resources with message [{$exception->getMessage()}].", [
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'path' => $path,
                ]);

                throw UnableToDeleteDirectory::atLocation($path, $exception->getMessage(), $exception);
            }
        }

        // Delete main directory. This will handle deleting any nested directories as long as they are empty, which they
        // will be after the above code removes all assets.
        $this->debug("Deleting directory [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        try {
            $this->client->adminApi()
                ->deleteFolder($publicId);
        } catch (Throwable $exception) {
            $this->critical(
                "Failed to delete directory [{$path}] with message [{$exception->getMessage()}].",
                [
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'path' => $path,
                    'public_id' => $publicId,
                    'resource_type' => $resourceType,
                ]
            );

            throw UnableToDeleteDirectory::atLocation($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->debug("Creating directory [{$path}].", [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'path' => $path,
        ]);

        // Normalize path.
        $path = $this->normalizePath($path, true);

        try {
            $this->client->adminApi()->createFolder($path);
        } catch (Throwable $exception) {
            $this->critical(
                "Failed to create directory [{$path}] with message [{$exception->getMessage()}].",
                [
                    'config' => $config->toArray(),
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToCreateDirectory::atLocation($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        // ! NOTE: We cannot modify the visibility of Cloudinary assets outside initial upload because the PHP
        // Cloudinary SDK does not support the access mode modification API call.
        $this->critical(
            'Visibility modification is unsupported for this adapter.',
            [
                'method' => __METHOD__,
                'path' => $path,
                'visibility' => $visibility,
            ]
        );

        throw UnableToSetVisibility::atLocation($path, __CLASS__.' does not support modifying visibility.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function visibility(string $path): FileAttributes
    {
        $this->debug("Getting visibility for [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        $attributes = $this->getFileMetadata($path, StorageAttributes::ATTRIBUTE_VISIBILITY);

        if ($attributes->visibility() === null) {
            $this->critical(
                "Failed to retrieve visibility for [{$path}].",
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToRetrieveMetadata::visibility($path);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function mimeType(string $path): FileAttributes
    {
        $this->debug("Getting mime-type for [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        $attributes = $this->getFileMetadata($path, StorageAttributes::ATTRIBUTE_MIME_TYPE);

        if ($attributes->mimeType() === null) {
            $this->critical(
                "Failed to retrieve mime-type for [{$path}].",
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function lastModified(string $path): FileAttributes
    {
        $this->debug("Getting last modified for [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        $attributes = $this->getFileMetadata($path, StorageAttributes::ATTRIBUTE_LAST_MODIFIED);

        if ($attributes->lastModified() === null) {
            $this->critical(
                "Failed to retrieve last modified date for [{$path}].",
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToRetrieveMetadata::lastModified($path);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function fileSize(string $path): FileAttributes
    {
        $this->debug("Getting file size for [{$path}].", [
            'method' => __METHOD__,
            'path' => $path,
        ]);

        $attributes = $this->getFileMetadata($path, StorageAttributes::ATTRIBUTE_FILE_SIZE);

        if ($attributes->fileSize() === null) {
            $this->critical(
                "Failed to retrieve file size for [{$path}].",
                [
                    'method' => __METHOD__,
                    'path' => $path,
                ]
            );

            throw UnableToRetrieveMetadata::fileSize($path);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $this->debug("Listing contents for [{$path}].", [
            'deep' => $deep,
            'method' => __METHOD__,
            'path' => $path,
        ]);

        $path = rtrim($this->normalizePath($path, true), '/');

        // Yield out all folders.
        foreach ($this->getAllFolders($path, $deep) as $folder) {
            yield $folder;
        }

        // Yield out all resources.
        foreach ($this->getAllFiles($path, $deep) as $resource) {
            yield $resource;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions for Flysystem write options. Note that only
     * 'invalidate', 'overwrite', 'upload_type', 'to_type', 'context', and 'metadata' are supported for the move/rename
     * call.
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload
     *
     * @throws UnexpectedValueException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidVisibilityException
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->debug("Moving [{$source}] to [{$destination}].", [
            'config' => $config->toArray(),
            'destination' => $destination,
            'method' => __METHOD__,
            'source' => $source,
        ]);

        // Get current file type from metadata, so we can know what the upload type of the source is.
        try {
            $fileMetadata = $this->getFileMetadata($source, StorageAttributes::ATTRIBUTE_EXTRA_METADATA);
        } catch (Throwable $exception) {
            $this->critical("Unable to move file with message [{$exception->getMessage()}].", [
                'config' => $config->toArray(),
                'destination' => $destination,
                'exception' => $exception,
                'method' => __METHOD__,
                'source' => $source,
            ]);

            throw UnableToMoveFile::fromLocationTo($source, $destination, $exception);
        }

        $currentType = $fileMetadata->extraMetadata()['type'] ?? null;
        $resourceType = $this->getResourceType($source);
        $source = $this->getPublicId($source, $resourceType);
        $destination = $this->getPublicId($destination, $resourceType);

        // Get config options.
        $visibility = (string) $config->get(WriteOptions::VISIBILITY()->getValue(), Visibility::PUBLIC);
        $uploadType = $currentType ?? $this->visibilityConverter->visibilityToUploadType($visibility)->getValue();
        $invalidate = (bool) $config->get(WriteOptions::INVALIDATE()->getValue(), true);
        $overwrite = (bool) $config->get(WriteOptions::OVERWRITE()->getValue(), true);
        $toType = $this->visibilityConverter->visibilityToUploadType(
            (string) $config->get(WriteOptions::TO_TYPE()->getValue(), $visibility)
        )->getValue();
        $metadata = (array) $config->get(WriteOptions::METADATA()->getValue(), []);
        $context = (array) $config->get(WriteOptions::CONTEXT()->getValue(), []);

        $options = [
            'invalidate' => $invalidate,
            'resource_type' => $resourceType,
            'overwrite' => $overwrite,
            'to_type' => $toType,
            'type' => $uploadType,
        ];

        // Add metadata param.
        if ($metadata !== []) {
            $options['metadata'] = $metadata;
        }

        // Add context param.
        if ($context !== []) {
            $options['context'] = $context;
        }

        // Move file from one name to another. We use invalidate here to invalidate CDN caching on rename.
        try {
            $response = (array) $this->client->uploadApi()->rename($source, $destination, $options);
        } catch (Throwable $exception) {
            $this->critical(
                "Failed to move [{$source}] to [{$destination}] with exception [{$exception->getMessage()}].",
                [
                    'config' => $config->toArray(),
                    'destination' => $destination,
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'source' => $source,
                ]
            );

            throw UnableToMoveFile::fromLocationTo($source, $destination, $exception);
        }

        // Ensure file was renamed.
        if ($response['public_id'] !== $destination) {
            $this->critical(
                "Failed to move [{$source}] to [{$destination}].",
                [
                    'config' => $config->toArray(),
                    'destination' => $destination,
                    'method' => __METHOD__,
                    'source' => $source,
                ]
            );

            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @see \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions for Flysystem write options
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload
     *
     * @throws UnexpectedValueException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidVisibilityException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->debug("Copying [{$source}] to [{$destination}].", [
            'config' => $config->toArray(),
            'destination' => $destination,
            'method' => __METHOD__,
            'source' => $source,
        ]);

        // Get the resource type for the file.
        $resourceType = $this->getResourceType($source);

        try {
            // Get source from API.
            $resource = $this->readStream($source);
        } catch (Throwable $exception) {
            $this->critical(
                "Failed to copy [{$source}] to [{$destination}] with exception [{$exception->getMessage()}].",
                [
                    'config' => $config->toArray(),
                    'destination' => $destination,
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'source' => $source,
                ]
            );

            throw UnableToCopyFile::fromLocationTo($source, $destination, $exception);
        }

        // Ensure to set resource type for written file.
        $config = $config->extend([WriteOptions::RESOURCE_TYPE()->getValue() => $resourceType]);

        // Copy file from one path to another.
        try {
            $this->writeStream($destination, $resource, $config);
        } catch (Throwable $exception) {
            $this->critical(
                "Failed to copy [{$source}] to [{$destination}] with exception [{$exception->getMessage()}].",
                [
                    'config' => $config->toArray(),
                    'destination' => $destination,
                    'exception' => $exception,
                    'method' => __METHOD__,
                    'source' => $source,
                ]
            );

            throw UnableToCopyFile::fromLocationTo($source, $destination, $exception);
        }
    }

    /**
     * Gets a list of Cloudinary Upload API optional params from a given {@link $config} that pertain to any resources.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param \League\Flysystem\Config $config Flysystem write config
     *
     * @return array Array of options to send with Cloudinary Upload API call
     */
    private function getWriteOptions(Config $config, string $visibility): array
    {
        $options = [];

        // Get additional write options from config.
        $uploadPreset = (string) $config->get(
            WriteOptions::UPLOAD_PRESET()->getValue(),
            $this->configuration->getUploadPreset() ?? ''
        );
        $accessMode = (string) $config->get(
            WriteOptions::ACCESS_MODE()->getValue(),
            $visibility === Visibility::PUBLIC
                ? AccessModes::PUBLIC()->getValue()
                : AccessModes::AUTHENTICATED()->getValue()
        );
        $invalidate = (bool) $config->get(WriteOptions::INVALIDATE()->getValue(), true);
        $overwrite = (bool) $config->get(WriteOptions::OVERWRITE()->getValue(), true);
        /** @noinspection PhpVariableNamingConventionInspection */
        $cloudinaryOptions = (array) $config->get(WriteOptions::CLOUDINARY_OPTIONS()->getValue(), []);
        $metadata = (array) $config->get(WriteOptions::METADATA()->getValue(), []);
        $tags = (array) $config->get(WriteOptions::TAGS()->getValue(), []);
        $context = (array) $config->get(WriteOptions::CONTEXT()->getValue(), []);
        $backup = (bool) $config->get(WriteOptions::BACKUP()->getValue(), false);
        $headers = (array) $config->get(WriteOptions::HEADERS()->getValue(), []);
        $eager = (array) $config->get(WriteOptions::EAGER()->getValue(), []);
        $transformation = (array) $config->get(WriteOptions::TRANSFORMATION()->getValue(), []);
        $accessControl = (array) $config->get(WriteOptions::ACCESS_CONTROL()->getValue(), []);

        // Add access mode param.
        $options['access_mode'] = $accessMode;

        // Add upload preset param.
        if ($uploadPreset !== '') {
            $options['upload_preset'] = $uploadPreset;
        }

        // Add overwrite param.
        if ($overwrite) {
            $options['overwrite'] = true;
        }

        // Add invalidate param.
        if ($invalidate) {
            $options['invalidate'] = true;
        }

        // Add backup param.
        if ($backup) {
            $options['backup'] = true;
        }

        // Add metadata param.
        if ($metadata !== []) {
            $options['metadata'] = $metadata;
        }

        // Add tags param.
        if ($tags !== []) {
            $options['tags'] = $tags;
        }

        // Add context param.
        if ($context !== []) {
            $options['context'] = $context;
        }

        // Add in header param.
        if ($headers !== []) {
            $options['headers'] = $headers;
        }

        // Add in eager param.
        if ($eager !== []) {
            $options['eager'] = $eager;
        }

        // Add in transformation param.
        if ($transformation !== []) {
            $options['transformation'] = $transformation;
        }

        // Add in access control param.
        if ($accessControl !== []) {
            $options['access_control'] = $accessControl;
        }

        // Add merge in additional Cloudinary params.
        if ($cloudinaryOptions !== []) {
            $options = array_merge($options, $cloudinaryOptions);
        }

        $this->debug('Added in Cloudinary upload optional parameters.', [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'options' => $options,
        ]);

        return $options;
    }

    /**
     * Gets a list of Cloudinary Upload API optional params from a given {@link $config} that pertain to images only.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.LongVariable)
     *
     * @param \League\Flysystem\Config $config Flysystem write config
     *
     * @return array Array of options to send with Cloudinary Upload API call
     */
    private function getImageOnlyWriteOptions(Config $config): array
    {
        $options = [];

        // Get additional image only write options from config.
        $phash = (bool) $config->get(WriteOptions::PHASH()->getValue(), true);
        $format = (string) $config->get(WriteOptions::FORMAT()->getValue(), '');
        $autoTagging = $config->get(WriteOptions::AUTO_TAGGING()->getValue(), null);
        $autoTagging = $autoTagging !== null ? (float) $autoTagging : null;
        $categorization = (string) $config->get(WriteOptions::CATEGORIZATION()->getValue(), '');
        $detection = (string) $config->get(WriteOptions::DETECTION()->getValue(), '');
        $ocr = (string) $config->get(WriteOptions::OCR()->getValue(), '');
        $visualSearch = (bool) $config->get(WriteOptions::VISUAL_SEARCH()->getValue(), false);
        /** @noinspection PhpVariableNamingConventionInspection */
        $backgroundRemoval = (string) $config->get(WriteOptions::BACKGROUND_REMOVAL()->getValue(), '');
        /** @noinspection PhpVariableNamingConventionInspection */
        $responsiveBreakpoints = (array) $config->get(WriteOptions::RESPONSIVE_BREAKPOINTS()->getValue(), []);
        $faceCoordinates = (array) $config->get(WriteOptions::FACE_COORDINATES()->getValue(), []);
        $regions = (array) $config->get(WriteOptions::REGIONS()->getValue(), []);
        /** @noinspection PhpVariableNamingConventionInspection */
        $customCoordinates = (array) $config->get(WriteOptions::CUSTOM_COORDINATES()->getValue(), []);

        // Add in phash param.
        if ($phash) {
            $options['phash'] = true;
        }

        // Add in format param.
        if ($format !== '') {
            $options['format'] = $format;
        }

        // Add in auto-tagging param.
        if ($autoTagging !== null && $autoTagging >= 0.0 && $autoTagging < 1.0) {
            $options['auto_tagging'] = $autoTagging;
        }

        // Add in categorization param.
        if ($categorization !== '') {
            $options['categorization'] = $categorization;
        }

        // Add in detection param.
        if ($detection !== '') {
            $options['detection'] = $detection;
        }

        // Add in ocr param.
        if ($ocr !== '') {
            $options['ocr'] = $ocr;
        }

        // Add in visual search param.
        if ($visualSearch) {
            $options['visual_search'] = true;
        }

        // Add in background removal param.
        if ($backgroundRemoval !== '') {
            $options['background_removal'] = $backgroundRemoval;
        }

        // Add in responsive breakpoints param.
        if ($responsiveBreakpoints !== []) {
            $options['responsive_breakpoints'] = $responsiveBreakpoints;
        }

        // Add in face coordinates param.
        if ($faceCoordinates !== []) {
            $options['face_coordinates'] = $faceCoordinates;
        }

        // Add in regions param.
        if ($regions !== []) {
            $options['regions'] = $regions;
        }

        // Add in custom coordinates param.
        if ($customCoordinates !== []) {
            $options['custom_coordinates'] = $customCoordinates;
        }

        $this->debug('Added in Cloudinary upload image-only optional parameters.', [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'options' => $options,
        ]);

        return $options;
    }

    /**
     * Gets a list of Cloudinary Upload API optional params from a given {@link $config} that pertain to videos only.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param \League\Flysystem\Config $config Flysystem write config
     *
     * @return array Array of options to send with Cloudinary Upload API call
     */
    private function getVideoOnlyWriteOptions(Config $config): array
    {
        $options = [];

        // Get additional video only write options from config.
        $autoTagging = $config->get(WriteOptions::AUTO_TAGGING()->getValue(), null);
        $autoTagging = $autoTagging !== null ? (float) $autoTagging : null;
        $categorization = (string) $config->get(WriteOptions::CATEGORIZATION()->getValue(), '');
        $autoChaptering = (bool) $config->get(WriteOptions::AUTO_CHAPTERING()->getValue(), false);
        /** @noinspection PhpVariableNamingConventionInspection */
        $autoTranscription = (bool) $config->get(WriteOptions::AUTO_TRANSCRIPTION()->getValue(), false);

        // Add in auto-tagging param.
        if ($autoTagging !== null && $autoTagging >= 0.0 && $autoTagging < 1.0) {
            $options['auto_tagging'] = $autoTagging;
        }

        // Add in categorization param.
        if ($categorization !== '') {
            $options['categorization'] = $categorization;
        }

        // Add in auto-chaptering param.
        if ($autoChaptering) {
            $options['auto_chaptering'] = true;
        }

        // Add in auto-transcription param.
        if ($autoTranscription) {
            $options['auto_transcription'] = true;
        }

        $this->debug('Added in Cloudinary upload video-only optional parameters.', [
            'config' => $config->toArray(),
            'method' => __METHOD__,
            'options' => $options,
        ]);

        return $options;
    }

    /**
     * Deletes a single resource of a given {@link $publicId} and a given {@link $resourceType}.
     *
     * @param string $path         Base path
     * @param string $publicId     Public ID of resource
     * @param string $resourceType Resource type
     *
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToDeleteFile
     */
    private function deleteResource(string $path, string $publicId, string $resourceType): void
    {
        $this->debug("Attempt to delete resource [{$publicId}] with type 'upload'.", [
            'method' => __METHOD__,
            'path' => $path,
            'public_id' => $publicId,
            'resource_type' => $resourceType,
        ]);

        // Attempt to delete asset using the 'upload' type first.
        $deleted = $this->deleteResourceApiCall($path, $publicId, $resourceType, UploadTypes::UPLOAD());

        // Attempt to delete using the 'authenticated' type next.
        if (! $deleted) {
            $this->debug("Attempt to delete resource [{$publicId}] with type 'authenticated'.", [
                'method' => __METHOD__,
                'path' => $path,
                'public_id' => $publicId,
                'resource_type' => $resourceType,
            ]);

            $deleted = $this->deleteResourceApiCall($path, $publicId, $resourceType, UploadTypes::AUTHENTICATED());
        }

        // Attempt to delete using the 'private' type next.
        if (! $deleted) {
            $this->debug("Attempt to delete resource [{$publicId}] with type 'private'.", [
                'method' => __METHOD__,
                'path' => $path,
                'public_id' => $publicId,
                'resource_type' => $resourceType,
            ]);

            $deleted = $this->deleteResourceApiCall($path, $publicId, $resourceType, UploadTypes::PRIVATE());
        }

        if (! $deleted) {
            // If we still failed, throw an exception since we know the file exists but we could not delete it.
            $this->critical("Failed to delete resource at [{$path}]. Resource not found.", [
                'method' => __METHOD__,
                'path' => $path,
                'public_id' => $publicId,
                'resource_type' => $resourceType,
            ]);

            throw UnableToDeleteFile::atLocation($path);
        }
    }

    /**
     * Makes a call to delete a given resource by its {@link $publicId} for a given {@link $resourceType} and a given
     * {@link $uploadType}.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param string                                                     $path         Base resource path
     * @param string                                                     $publicId     Resource public ID
     * @param string                                                     $resourceType Resource type
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes $uploadType   Upload type
     *
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToDeleteFile
     *
     * @return bool True iff the resource was deleted, false otherwise
     */
    private function deleteResourceApiCall(
        string $path,
        string $publicId,
        string $resourceType,
        UploadTypes $uploadType
    ): bool {
        // Attempt to delete the resource.
        try {
            $response = (array) $this->client->adminApi()->deleteAssets(
                [$publicId],
                [
                    'resource_type' => $resourceType,
                    'invalidate' => true,
                    'type' => $uploadType->getValue(),
                ]
            );
        } catch (Throwable $exception) {
            // If we get an exception, assume resource could not be deleted.
            $this->error("Unable to delete resource [{$publicId}] with message [{$exception->getMessage()}].", [
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
                'public_id' => $publicId,
                'resource_type' => $resourceType,
                'upload_type' => $uploadType->getValue(),
            ]);

            throw UnableToDeleteFile::atLocation($path, $exception->getMessage(), $exception);
        }

        // Check the response to make sure we actually deleted the resource.
        if (! isset($response['deleted'], $response['deleted'][$publicId]) || $response['deleted'][$publicId] !== 'deleted') {
            $message = "Failed to successfully delete [{$publicId}] for upload type [{$uploadType->getValue()}].";

            $this->warning($message, [
                'method' => __METHOD__,
                'path' => $path,
                'public_id' => $publicId,
                'resource_type' => $resourceType,
                'upload_type' => $uploadType->getValue(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Method to get each folder from Cloudinary's API for a given {@link $path}.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path Path to search in
     * @param bool   $deep Whether to recurse into nested folders
     *
     * @return iterable<\League\Flysystem\DirectoryAttributes> Iterable of Flysystem directories
     */
    private function getAllFolders(string $path, bool $deep = false): iterable
    {
        // ? We need to fetch folders via the Admin API to ensure we don't miss any empty folders as they will not be
        // processed by getting all the resources above alone. I would like to make this more efficient eventually, but
        // I am not sure as to the best process to only search in directories we need to.
        try {
            // Get folders using the Cloudinary Admin API.
            foreach ($this->getFolders($path, $deep) as $folder) {
                yield $folder;
            }
        } catch (Throwable $exception) {
            if ($exception instanceof NotFound) {
                // If we get a not found exception here, that means the directory does not exist. We want to debug
                // log and return an empty array since there are no contents in this folder.
                $this->debug('Resource not found.', [
                    'deep' => $deep,
                    'method' => __METHOD__,
                    'path' => $path,
                ]);

                return;
            }
            // Log and throw exception for any other API errors.
            $this->critical("Failed to get Cloudinary folders with message [{$exception->getMessage()}].", [
                'deep' => $deep,
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw UnableToListContents::atLocation($path, $deep, $exception);
        }
    }

    /**
     * Method to get each file from Cloudinary's API for a given {@link $path}.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path Path to search in
     * @param bool   $deep Whether to recurse into nested folders
     *
     * @return iterable<\League\Flysystem\FileAttributes> Iterable of Flysystem files
     */
    private function getAllFiles(string $path, bool $deep = false): iterable
    {
        try {
            // Get files using the Cloudinary Search API.
            foreach ($this->getFiles($path, $deep) as $resource) {
                yield $resource;
            }
        } catch (Throwable $exception) {
            if ($exception instanceof NotFound) {
                // If we get a not found exception here, that means the directory does not exist. We want to debug
                // log and continue since there are no content in this folder.
                $this->debug('Resource(s) not found.', [
                    'deep' => $deep,
                    'method' => __METHOD__,
                    'path' => $path,
                ]);

                return;
            }

            // Log and throw exception for all other errors.
            $this->critical("Failed to get Cloudinary resources with message [{$exception->getMessage()}].", [
                'deep' => $deep,
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
            ]);

            throw UnableToListContents::atLocation($path, $deep, $exception);
        }
    }

    /**
     * Takes an array of {@link $publicIds} of a given {@link $resourceType} and chunks them into arrays of 100 and
     * makes a call to batch delete them via the Cloudinary Admin API.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param string[] $publicIds    Array of public IDs
     * @param string   $resourceType Resource type
     *
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToDeleteFile
     */
    private function deleteResources(string $path, array $publicIds, string $resourceType): void
    {
        // Get 100 public IDs at a time since this is the limit we can send to the Cloudinary API.
        $chunkedIds = array_chunk($publicIds, 100);

        // For each chunk of up to 100, batch delete the resources.
        foreach ($chunkedIds as $ids) {
            // Try to delete them with the normal 'upload' type first.
            $failedDeletes = $this->deleteResourcesApiCall($path, $ids, $resourceType, UploadTypes::UPLOAD());

            // No failed deletes, continue with next chunk.
            if ($failedDeletes === null) {
                continue;
            }

            // If we have any failed deletes, try to delete them using the 'authenticated' upload type.
            $failedDeletes = $this->deleteResourcesApiCall(
                $path,
                $failedDeletes,
                $resourceType,
                UploadTypes::AUTHENTICATED()
            );

            // No failed deletes, continue with next chunk.
            if ($failedDeletes === null) {
                continue;
            }

            // If we have any failed deletes, try to delete them using the 'private' upload type.
            $failedDeletes = $this->deleteResourcesApiCall(
                $path,
                $failedDeletes,
                $resourceType,
                UploadTypes::PRIVATE()
            );

            // If we have any failed deletes after trying all 3 upload types, then we must throw an exception since we
            // were unable to delete all the resources.
            if ($failedDeletes !== null) {
                $failedIds = implode(', ', $failedDeletes);
                $message = "Failed to delete [{$failedIds}] for upload type [{$resourceType}].";

                $this->error($message, [
                    'failed_deletes' => $failedDeletes,
                    'method' => __METHOD__,
                    'path' => $path,
                    'public_ids' => $ids,
                    'resource_type' => $resourceType,
                ]);

                throw new UnableToDeleteFile($message);
            }
        }
    }

    /**
     * Performs a delete on a batch of provided {@link $chunkedPublicIds}. Will ensure all IDs were deleted, and return
     * an array of any failed deletes. Will return null iff all passed in IDs were deleted.
     *
     * @noinspection MultipleReturnStatementsInspection
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param string                                                     $path             Base path
     * @param array                                                      $chunkedPublicIds Array of public IDs
     * @param string                                                     $resourceType     Resource type
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes $uploadType       Upload type
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return string[]|null Array of failed to delete public IDs, or null if everything was deleted
     */
    private function deleteResourcesApiCall(
        string $path,
        array $chunkedPublicIds,
        string $resourceType,
        UploadTypes $uploadType
    ): ?array {
        try {
            // Try to delete using the 'upload' type first.
            $response = (array) $this->client->adminApi()->deleteAssets(
                $chunkedPublicIds,
                [
                    'resource_type' => $resourceType,
                    'invalidate' => true,
                    'type' => $uploadType->getValue(),
                ]
            );
        } catch (Throwable $exception) {
            $this->error("Unable to delete files with message [{$exception->getMessage()}].", [
                'exception' => $exception,
                'method' => __METHOD__,
                'path' => $path,
                'public_ids' => $chunkedPublicIds,
                'resource_type' => $resourceType,
                'upload_type' => $uploadType->getValue(),
            ]);

            // If we failed, return the entire set since none were deleted.
            return $chunkedPublicIds;
        }

        // Ensure we got a proper response back, so we can check for failed deletes.
        if (! isset($response['deleted']) || ! is_array($response['deleted'])) {
            $this->warning('Received an invalid API response trying to delete batch of resources.', [
                'method' => __METHOD__,
                'path' => $path,
                'public_ids' => $chunkedPublicIds,
                'resource_type' => $resourceType,
                'response' => $response,
                'upload_type' => $uploadType->getValue(),
            ]);

            // Invalid response, no resources were deleted.
            return $chunkedPublicIds;
        }

        $failedDeletes = [];

        // Get any failed deletes from initial delete call. We will want to try them as 'authenticated' upload type.
        foreach ($response['deleted'] as $publicId => $status) {
            if ($status === 'not_found') {
                $failedDeletes[] = $publicId;
            }
        }

        // If we have no failed deletes, return null, otherwise return array of failed deletes public IDs.
        if (count($failedDeletes) > 0) {
            $failedIds = implode(', ', $failedDeletes);

            $this->warning("Failed to delete resources [{$failedIds}].", [
                'failed_deletes' => $failedDeletes,
                'method' => __METHOD__,
                'path' => $path,
                'public_ids' => $chunkedPublicIds,
                'resource_type' => $resourceType,
                'response' => $response,
                'upload_type' => $uploadType->getValue(),
            ]);

            return $failedDeletes;
        }

        return null;
    }

    /**
     * Method to get a list of all folders starting at a given {@link $path} from the Cloudinary API.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path Normalized directory path
     * @param bool   $deep Whether to expand recursively or not
     *
     * @throws Throwable
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToListContents
     *
     * @return iterable<\League\Flysystem\DirectoryAttributes> Iterable of Flysystem directories
     */
    private function getFolders(string $path, bool $deep = false): iterable
    {
        $response = null;

        do {
            $this->debug(
                'Fetching folders from Cloudinary Admin API.',
                [
                    'deep' => $deep,
                    'method' => __METHOD__,
                    'next_cursor' => $response['next_cursor'] ?? null,
                    'path' => $path,
                ]
            );

            $response = (array) $this->client->adminApi()->subFolders($path, [
                'next_cursor' => $response['next_cursor'] ?? null,
                // Max limit for Cloudinary API.
                'max_results' => 500,
            ]);

            if (! isset($response['folders']) || ! is_array($response['folders'])) {
                $this->warning('No folders found.', [
                    'deep' => $deep,
                    'method' => __METHOD__,
                    'path' => $path,
                    'response' => $response,
                ]);

                return;
            }

            // Yield each directory after processing the directories
            foreach ($this->processDirectoryResponse($response, $path, $deep) as $directory) {
                yield $directory;
            }
        } while (isset($response['next_cursor']));
    }

    /**
     * Process a chunk of subfolders returned from the Cloudinary subFolders method.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param array  $response Cloudinary API response
     * @param string $path     Normalized directory path
     * @param bool   $deep     Whether to expand recursively or not
     *
     * @throws Throwable
     *
     * @return iterable<\League\Flysystem\DirectoryAttributes> Iterable of Flysystem directories
     */
    private function processDirectoryResponse(array $response, string $path, bool $deep = false): iterable
    {
        // Process each folder returned and add them to the directory cache if not already in there.
        foreach ($response['folders'] as $folder) {
            if (! isset($folder['path']) || ! is_string($folder['path'])) {
                $this->warning('Folder does not contain a valid path.', [
                    'deep' => $deep,
                    'folder' => $folder,
                    'method' => __METHOD__,
                    'path' => $path,
                ]);

                continue;
            }

            // Yield out directory.
            yield $this->mapDirectory($folder['path']);

            // If we want to recurse into subfolders, we take each folder we get back and fetch subfolders from
            // that directory using the API.
            if ($deep) {
                $this->debug("Recusring into folder [{$folder['path']}].", [
                    'deep' => $deep,
                    'method' => __METHOD__,
                    'path' => $path,
                ]);

                // Yield out each recursive subfolder.
                foreach ($this->getFolders($folder['path']) as $dir) {
                    yield $dir;
                }
            }
        }
    }

    /**
     * Get all files for a given {@link $path} using Cloudinary's Search API.
     *
     * https://cloudinary.com/documentation/admin_api#search_for_resources
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path Normalized directory path
     * @param bool   $deep Whether to expand recursively or not
     *
     * @throws Throwable
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToListContents
     *
     * @return array<\League\Flysystem\StorageAttributes> Iterable of resources data
     */
    private function getFiles(string $path, bool $deep = false): iterable
    {
        $response = null;

        // Get all available resources (video, image and raw files).
        $searchQuery = '(resource_type:image OR resource_type:video OR resource_type:raw)';

        // If we are not searching in the root directory, add the path to the public_id search query.
        if ($path !== '') {
            $searchQuery = "{$searchQuery} AND public_id={$path}/*";
        }

        // Get paginated resources for a given directory and a given type.
        do {
            $this->debug(
                'Fetching resources from Cloudinary Admin API.',
                [
                    'deep' => $deep,
                    'method' => __METHOD__,
                    'next_cursor' => $response['next_cursor'] ?? null,
                    'path' => $path,
                    'search_query' => $searchQuery,
                ]
            );

            $search = $this->client->searchApi()
                ->expression($searchQuery)
                // Include any available extra data.
                ->withField('context')
                ->withField('tags')
                ->withField('metadata')
                ->withField('image_metadata')
                ->withField('image_analysis')
                ->withField('quality_analysis')
                ->withField('accessibility_analysis')
                ->sortBy('public_id', 'asc')
                // Max limit for Cloudinary API.
                ->maxResults(500);

            if (isset($response['next_cursor'])) {
                $search->nextCursor($response['next_cursor']);
            }

            // Execute Search API query to get back list of files.
            $response = (array) $search->execute();

            // Ensure we got resources back, otherwise skip yielding out any resources.
            if (! isset($response['resources']) || ! is_array($response['resources'])) {
                $this->warning(
                    'No resources found.',
                    [
                        'deep' => $deep,
                        'method' => __METHOD__,
                        'path' => $path,
                        'response' => $response,
                        'search_query' => $searchQuery,
                    ]
                );

                continue;
            }

            // Yield each file after processing the chunk of resources.
            foreach ($this->processResourceResponse($response, $path, $deep) as $resource) {
                yield $resource;
            }
        } while (isset($response['next_cursor']));
    }

    /**
     * Process a chunk of resources returned from the Cloudinary search API.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param array  $response Cloudinary API response
     * @param string $path     Normalized directory path
     * @param bool   $deep     Whether to expand recursively or not
     *
     * @return iterable<\League\Flysystem\FileAttributes> Iterable of Flysystem files
     */
    private function processResourceResponse(array $response, string $path, bool $deep = false): iterable
    {
        // Loop over each resource and transform into a StorageAttributes instance.
        foreach ($response['resources'] as $resource) {
            // Invalid resource entry.
            if (! isset($resource['public_id'])) {
                $this->debug(
                    'Invalid resource with no public_id set, skipping resource.',
                    [
                        'deep' => $deep,
                        'method' => __METHOD__,
                        'path' => $path,
                        'resource' => $resource,
                    ]
                );

                continue;
            }

            // If we don't want nested items, we need to check for slashes in the public ID after the normalized path
            // to see if there are nested items.
            if (! $deep) {
                // Remove the normalized path from the resource public ID, so we can check if the item is nested in a
                // folder.
                $resourcePath = ltrim(str_replace($path, '', $resource['public_id']), '/');

                if (mb_strpos($resourcePath, '/') !== false) {
                    $this->debug(
                        "Deep not set, skipping nested file [{$resource['public_id']}]",
                        [
                            'deep' => $deep,
                            'method' => __METHOD__,
                            'path' => $path,
                        ]
                    );

                    continue;
                }
            }

            // For raw files, there is no format data, and extension is part of the public_id.
            $filename = isset($resource['resource_type'], $resource['format']) && $resource['resource_type'] !== 'raw'
                ? "{$resource['public_id']}.{$resource['format']}"
                : $resource['public_id'];

            // Yield out the file.
            yield $this->mapFileMetadata($resource, $filename);
        }
    }

    /**
     * Gets the Cloudinary resource_type from a given file.
     *
     * @noinspection MultipleReturnStatementsInspection
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path   File/directory path
     * @param bool   $isFile If file is local or not
     *
     * @return string Cloudinary resource type
     */
    private function getResourceType(string $path, bool $isFile = false): string
    {
        // Get mimetype from file.
        $mimeType = $isFile
            ? $this->mimeTypeDetector->detectMimeTypeFromFile($path)
            : $this->mimeTypeDetector->detectMimeTypeFromPath($path);

        return $this->mimeTypeConverter->mimeTypeToResourceType($mimeType);
    }

    /**
     * Get Cloudinary resource at a given {@link $path}.
     *
     * @see https://cloudinary.com/documentation/admin_api#get_the_details_of_a_single_resource
     *
     * @param string $path File/directory path
     *
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToReadFile
     *
     * @return array Array of resource data from Cloudinary API
     */
    private function getResource(string $path): array
    {
        // Get resource type and normalized public ID from path.
        $resourceType = $this->getResourceType($path);
        $publicId = $this->getPublicId($path, $resourceType);

        try {
            return (array) $this->client->adminApi()
                ->asset(
                    $publicId,
                    [
                        'resource_type' => $resourceType,
                        'colors' => true,
                        'media_metadata' => true,
                        'exif' => true,
                        'image_metadata' => true,
                        'faces' => true,
                        'quality_analysis' => true,
                        'accessibility_analysis' => true,
                        'phash' => true,
                        'coordinates' => true,
                        'pages' => true,
                        'versions' => true,
                        'related' => true,
                    ]
                );
        } catch (Throwable $throwable) {
            $this->error(
                "Failed to get resource with message [{$throwable->getMessage()}].",
                [
                    'exception' => $throwable,
                    'method' => __METHOD__,
                    'path' => $path,
                    'public_id' => $publicId,
                    'resource_type' => $resourceType,
                ]
            );

            throw UnableToReadFile::fromLocation($path, $throwable->getMessage(), $throwable);
        }
    }

    /**
     * Get {@link \League\Flysystem\FileAttributes} for a given {@link $path} and {@link $type}.
     *
     * @param string $path File/directory path
     * @param string $type Metadata type (i.e. mimetype, filesize, etc).
     *
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \League\Flysystem\UnableToReadFile
     * @throws \League\Flysystem\UnableToRetrieveMetadata
     *
     * @return \League\Flysystem\FileAttributes File attributes
     */
    private function getFileMetadata(string $path, string $type): FileAttributes
    {
        try {
            $resource = $this->getResource($path);

            $file = $this->mapFileMetadata($resource, $path);

            if ($file->mimeType() === null) {
                throw UnableToRetrieveMetadata::create($path, $type, 'Unknown mimetype detected.');
            }

            return $file;
        } catch (Throwable $throwable) {
            $this->error(
                "Failed to get resource metadata with message [{$throwable->getMessage()}].",
                [
                    'path' => $path,
                    'method' => __METHOD__,
                    'exception' => $throwable,
                ]
            );

            throw UnableToRetrieveMetadata::create($path, $type, $throwable->getMessage(), $throwable);
        }
    }

    /**
     * Map Cloudinary file data to {@link \League\Flysystem\FileAttributes}.
     *
     * @param array  $data Cloudinary resource data
     * @param string $path Resource path (filename, dirname)
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return \League\Flysystem\FileAttributes File attributes
     */
    private function mapFileMetadata(array $data, string $path): FileAttributes
    {
        // Get required data for Flysystem FileAttributes.
        $mimeType = $this->mimeTypeDetector->detectMimeTypeFromPath($path);
        $lastModified = strtotime($data['created_at']);
        $fileSize = $data['bytes'];
        $visibility = isset($data['type'])
            ? $this->visibilityConverter->uploadTypeToVisibility(UploadTypes::from($data['type']))
            : $this->visibilityConverter->defaultVisibility();

        return new FileAttributes(
            $this->getPrefixer()->stripPrefix($path),
            $fileSize,
            $visibility,
            $lastModified,
            $mimeType,
            array_merge(
                // Get required metadata.
                [
                    'public_id' => $data['public_id'] ?? '',
                    'url' => $data['url'] ?? '',
                    'secure_url' => $data['secure_url'] ?? '',
                    'asset_id' => $data['asset_id'] ?? '',
                    'resource_type' => $data['resource_type'] ?? '',
                    'format' => $data['format'] ?? '',
                    'type' => $data['type'] ?? '',
                    'filename' => $data['filename'] ?? '',
                    'etag' => $data['etag'] ?? '',
                    'bytes' => $data['bytes'] ?? '',
                ],
                $this->extractMetadata($data)
            )
        );
    }

    /**
     * Map Cloudinary directory data to {@link \League\Flysystem\DirectoryAttributes}.
     *
     * @param string $path Directory path
     *
     * @return \League\Flysystem\DirectoryAttributes Directory attributes
     */
    private function mapDirectory(string $path): DirectoryAttributes
    {
        $path = $this->getPrefixer()->stripPrefix($path);

        if (mb_substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        // Cloudinary only provides the path for folders, so we cannot get any additional attributes.
        return new DirectoryAttributes($path);
    }

    /**
     * Get extra metadata from Cloudinary response.
     *
     * @param array $metadata Initial response metadata
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return array Array of extra metadata
     */
    private function extractMetadata(array $metadata): array
    {
        $extracted = [];

        // For each defined extra metadata field, check for it and add to list of extra details.
        foreach ($this->configuration->getExtraMetadataFields() as $field) {
            if (isset($metadata[$field]) && $metadata[$field] !== '') {
                $this->debug(
                    "Setting metadata for field [{$field}].",
                    [
                        'field' => $field,
                        'method' => __METHOD__,
                        'value' => $metadata[$field],
                    ]
                );

                $extracted[$field] = $metadata[$field];
            }
        }

        return $extracted;
    }

    /**
     * Normalizes a path string by replacing \'s with /'s and trimming any extra (beginning or ending) /'s. Will add
     * an ending / if {@link $isDir} is true.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path  File/directory path
     * @param bool   $isDir Whether path is to a directory or a file
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return string Normalized path string
     */
    private function normalizePath(string $path, bool $isDir = false): string
    {
        $this->debug(
            "Normalizing path. Original path [{$path}].",
            [
                'is_dir' => $isDir,
                'method' => __METHOD__,
                'original_path' => $path,
            ]
        );

        // Replace \'s with /'s and trim any beginning or ending /'s.
        $path = trim(
            str_replace('\\', '/', $this->getPrefixer()->prefixPath($path)),
            '\\/'
        );

        // If it's a directory, add a trailing forward slash.
        if ($isDir) {
            $path = "{$path}/";
        }

        $this->debug(
            "Normalized path [{$path}].",
            [
                'is_dir' => $isDir,
                'method' => __METHOD__,
                'normalized_path' => $path,
            ]
        );

        return $path;
    }

    /**
     * Gets a normalized public ID string from a given path and resource type.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $path         File/directory path
     * @param string $resourceType Cloudinary resource type
     * @param bool   $isDir        Whether path is to a directory or a file
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return string Normalized public ID string
     */
    private function getPublicId(string $path, string $resourceType, bool $isDir = false): string
    {
        $path = $this->normalizePath($path, $isDir);
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
        $publicId = $fileExtension ? mb_substr($path, 0, -(mb_strlen($fileExtension) + 1)) : $path;

        // If it is a directory, just return the normalized path.
        if ($isDir) {
            $this->debug(
                'Handling public ID for directory.',
                [
                    'is_dir' => $isDir,
                    'method' => __METHOD__,
                    'path' => $path,
                    'public_id' => $publicId,
                    'resource_type' => $resourceType,
                ]
            );

            return $publicId;
        }

        // For raw files (non-image and non-video), Cloudinary needs file extension added to the public ID.
        if ($resourceType === 'raw') {
            $this->debug(
                'Adding file extension for raw type file.',
                [
                    'is_dir' => $isDir,
                    'method' => __METHOD__,
                    'path' => $path,
                    'public_id' => $publicId,
                    'resource_type' => $resourceType,
                ]
            );

            $publicId = $fileExtension ? "{$publicId}.{$fileExtension}" : $publicId;
        }

        return $publicId;
    }

    /**
     * Gets a Flysystem PathPrefixer based on the config prefix option.
     *
     * @return \League\Flysystem\PathPrefixer Prefixer
     */
    private function getPrefixer(): PathPrefixer
    {
        return new PathPrefixer($this->configuration->getPrefix());
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    protected function getLoggingContext(): array
    {
        return [
            'class' => __CLASS__,
            'logger' => $this->logger === null ? null : get_class($this->logger),
            'logging' => $this->logging,
            'configuration' => $this->configuration->toArray(),
            'mime_type_detector' => get_class($this->mimeTypeDetector),
            'visibility_converter' => get_class($this->visibilityConverter),
            'mime_type_converter' => get_class($this->mimeTypeConverter),
            'client_version' => $this->client::VERSION,
            'client_configuration' => $this->client->configuration->toString(),
        ];
    }
}
