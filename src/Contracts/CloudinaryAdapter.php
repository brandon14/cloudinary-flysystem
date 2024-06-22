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

/** @noinspection PhpClassNamingConventionInspection */

declare(strict_types=1);

namespace Brandon14\CloudinaryFlysystem\Contracts;

use Cloudinary\Cloudinary;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\FilesystemAdapter;
use League\MimeTypeDetection\MimeTypeDetector;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

/**
 * {@link \League\Flysystem\Filesystem} V2/V3 adapter for Cloudinary's cloud API service. This adapter also implements
 * the PSR {@link \Psr\Log\LoggerAwareInterface} so if a logger is provided to the adapter (using either the
 * {@link setLogger()} method, or by passing a PSR logger implementation in via the constructor) it will automatically
 * log out events inside the adapter to aid in debugging.
 *
 * @see https://github.com/cloudinary/cloudinary_php
 * @see https://github.com/thephpleague/flysystem
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface CloudinaryAdapter extends FilesystemAdapter, PublicUrlGenerator, ChecksumProvider, LoggerAwareInterface
{
    /**
     * Set Cloudinary SDK instance.
     *
     * @see https://github.com/cloudinary/cloudinary_php
     *
     * @param \Cloudinary\Cloudinary $cloudinary Cloudinary instance
     */
    public function setClient(Cloudinary $cloudinary): self;

    /**
     * Get configured {@link \Cloudinary\Cloudinary} client instance.
     *
     * @return \Cloudinary\Cloudinary Cloudinary client
     */
    public function getClient(): Cloudinary;

    /**
     * Sets the adapter configuration options. Setting this to null uses the default configuration options.
     *
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Configuration|null $configuration Configuration options
     */
    public function setConfiguration(?Configuration $configuration = null): self;

    /**
     * Gets the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} configuration instance.
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\Configuration Configuration options
     */
    public function getConfiguration(): Configuration;

    /**
     * Sets the {@link \Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter} instance. Null will use the default
     * visibility converter {@link \Brandon14\CloudinaryFlysystem\CloudinaryVisibilityConverter} bundled with the
     * adapter.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param \Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter|null $visibilityConverter Visibility
     *                                                                                               converter
     *
     * @return $this
     */
    public function setVisibilityConverter(?VisibilityConverter $visibilityConverter): self;

    /**
     * Gets the {@link \Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter} instance.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter Visibility converter
     */
    public function getVisibilityConverter(): VisibilityConverter;

    /**
     * Sets the {@link \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter} instance for the adapter.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter|null $mimeTypeConverter Mime type converter
     *
     * @return $this
     */
    public function setMimeTypeConverter(?MimeTypeConverter $mimeTypeConverter): self;

    /**
     * Gets the {@link \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter} instance.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter Mime Type converter
     */
    public function getMimeTypeConverter(): MimeTypeConverter;

    /**
     * Set whether the adapter should log out events. If enabling with no valid PSR logger instance provided, it will
     * throw an exception.
     *
     * @param bool $logging whether to enable logging or not
     *
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\NoValidLoggerProvidedException
     *
     * @return $this
     */
    public function setLogging(bool $logging);

    /**
     * Gets the adapter set logging flag. If true, logging is enabled, if false, logging is disabled.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @return bool True iff logging is enabled, false otherwise
     */
    public function getLogging(): bool;

    /**
     * Enables logging for the adapter.
     *
     * @return $this
     */
    public function enableLogging();

    /**
     * Disables logging for the adapter.
     *
     * @return $this
     */
    public function disableLogging();

    /**
     * Returns whether logging is enabled for the adapter.
     *
     * @return bool True iff logging is enabled, false otherwise
     */
    public function isLoggingEnabled(): bool;

    /**
     * Set Flysystem mimetype detector. Defaults to {@link \League\MimeTypeDetection\FinfoMimeTypeDetector}.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param \League\MimeTypeDetection\MimeTypeDetector|null $mimeTypeDetector Mimetype detector
     */
    public function setMimeTypeDetector(?MimeTypeDetector $mimeTypeDetector): self;

    /**
     * Sets the adapters {@link \League\MimeTypeDetection\MimeTypeDetector} instance. This is used by the adapter to
     * attempt mime type detection.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return \League\MimeTypeDetection\MimeTypeDetector Mime type detector instance
     */
    public function getMimeTypeDetector(): MimeTypeDetector;

    /**
     * Get PSR logger instance.
     *
     * @return \Psr\Log\LoggerInterface|null Logger instance
     */
    public function getLogger(): ?LoggerInterface;
}
