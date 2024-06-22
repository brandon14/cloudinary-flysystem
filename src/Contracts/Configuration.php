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

declare(strict_types=1);

namespace Brandon14\CloudinaryFlysystem\Contracts;

use Stringable;
use Serializable;

use function implode;

use JsonSerializable;

use function is_string;
use function serialize;
use function array_walk;
use function unserialize;

use ReturnTypeWillChange;

use const JSON_ERROR_NONE;

use function array_values;

use League\Flysystem\Config;

use function json_last_error;

use Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields;

/**
 * Configuration options for the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} implementation. This
 * allows for configuration of extra metadata fields to pull from the Cloudinary API, a base folder prefix for the
 * adapter, and the upload preset to use when uploading assets.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class Configuration implements Stringable, JsonSerializable, Serializable
{
    /**
     * Path prefix.
     *
     * @var string
     */
    private $prefix;

    /**
     * Adapter-wide Cloudinary upload preset string. WIll be overwritten if passed in as a config option
     * on a method.
     *
     * @var string|null
     */
    private $uploadPreset;

    /**
     * Extra metadata fields to include.
     *
     * @noinspection PhpPropertyNamingConventionInspection
     *
     * @var string[]
     */
    private $extraMetadataFields = [];

    /**
     * Constructs a new Configuration instance.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string      $prefix              Path prefix, defaults to empty string (root of container)
     * @param string|null $uploadPreset        Global Cloudinary upload preset string, null implies account default
     * @param string[]    $extraMetadataFields Extra metadata fields to include
     *
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidConfigurationOption
     *
     * @return void
     */
    public function __construct(
        string $prefix = '',
        ?string $uploadPreset = null,
        array $extraMetadataFields = []
    ) {
        $this->setPrefix($prefix)
            ->setUploadPreset($uploadPreset)
            ->setExtraMetadataFields(
                $extraMetadataFields === []
                    // Default to the default list of extra metadata fields.
                    ? array_values(MetadataFields::toArray())
                    : $extraMetadataFields
            );
    }

    /**
     * Gets a new {@link \Brandon14\CloudinaryFlysystem\Configuration} instance using the default values.
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\Configuration Configuration
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Gets the configured folder prefix.
     *
     * @return string Folder prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Sets the folder prefix. Set to empty string to use the root folder.
     *
     * @param string $prefix Folder prefix
     *
     * @return $this
     */
    public function setPrefix(string $prefix = ''): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Gets the configured Cloudinary upload preset.
     *
     * @return string|null Upload preset
     */
    public function getUploadPreset(): ?string
    {
        return $this->uploadPreset;
    }

    /**
     * Sets the Cloudinary upload preset for the adapter to use. Set to null to not send an upload preset with the
     * upload requests. Null implies using the account default.
     *
     * @param string|null $uploadPreset Upload preset
     *
     * @return $this
     */
    public function setUploadPreset(?string $uploadPreset = null): self
    {
        $this->uploadPreset = $uploadPreset;

        return $this;
    }

    /**
     * Get the configured extra metadata fields to fetch from the Cloudinary API when getting resources.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return string[] Metadata fields
     */
    public function getExtraMetadataFields(): array
    {
        return $this->extraMetadataFields;
    }

    /**
     * Set the extra metadata fields to fetch from the Cloudinary API. Must be an array of string, with the values being
     * a valid {@link \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields} enum value.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param string[] $extraMetadataFields Metadata fields
     *
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidConfigurationOption
     *
     * @return $this
     */
    public function setExtraMetadataFields(array $extraMetadataFields = []): self
    {
        // Check for empty metadata fields first. We can simply set it to empty and return.
        if ($extraMetadataFields === []) {
            $this->extraMetadataFields = [];

            return $this;
        }

        // Validate extra metadata fields to pull from response.
        array_walk($extraMetadataFields, static function ($value) {
            if (! is_string($value) || $value === '' || ! MetadataFields::isValid($value)) {
                $validFields = implode(',', array_values(MetadataFields::toArray()));

                throw new InvalidConfigurationOption("Invalid metadata field [{$value}]. Must be string and one of [{$validFields}].");
            }
        });

        $this->extraMetadataFields = $extraMetadataFields;

        return $this;
    }

    /**
     * Get the array representation of the adapter configuration.
     *
     * @return array{
     *     prefix: string,
     *     upload_preset: string,
     *     extra_metadata_fields: string[],
     * } Array of configuration
     */
    public function toArray(): array
    {
        return [
            'prefix' => $this->getPrefix(),
            'upload_preset' => $this->getUploadPreset(),
            'extra_metadata_fields' => $this->getExtraMetadataFields(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    /**
     * Returns the serialization data for class.
     *
     * @return array{
     *     prefix: string,
     *     upload_preset: string,
     *     extra_metadata_fields: string[],
     *  } Class serialization representation
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data): void
    {
        $data = unserialize($data, ['allowed_classes' => false]);

        $this->setPrefix($data['prefix'])
            ->setUploadPreset($data['upload_preset'])
            ->setExtraMetadataFields($data['extra_metadata_fields']);
    }

    /**
     * Sets up class members after being unserialized.
     *
     * @param array{
     *     prefix: string,
     *     upload_preset: string,
     *     extra_metadata_fields: string[],
     *  } $data Unserialized data
     */
    public function __unserialize(array $data): void
    {
        $this->setPrefix($data['prefix'])
            ->setUploadPreset($data['upload_preset'])
            ->setExtraMetadataFields($data['extra_metadata_fields']);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $json = json_encode($this->toArray());

        if (! is_string($json) || json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        return $json;
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
