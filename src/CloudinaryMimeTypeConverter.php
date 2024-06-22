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

namespace Brandon14\CloudinaryFlysystem;

use function mb_strpos;

use Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter;
use Brandon14\CloudinaryFlysystem\Contracts\Enums\ResourceTypes;

/**
 * {@link \Brandon14\CloudinaryFlysystem\Contracts\MimeTypeConverter} implementation to convert incoming mime types into
 * Cloudinary resource types of 'image', 'video', or 'raw'.
 *
 * @see https://cloudinary.com/documentation/image_transformations#supported_image_formats
 * @see https://cloudinary.com/documentation/video_manipulation_and_delivery#supported_video_formats
 *
 * @noinspection PhpClassNamingConventionInspection
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class CloudinaryMimeTypeConverter implements MimeTypeConverter
{
    /**
     * Array of partial or full mime types that should be considered image resource types by Cloudinary.
     *
     * @var string[]
     */
    private const DEFAULT_IMAGE_TYPES = [
        'image',
        'pdf',
        'postscript',
        'gltf',
        'model/obj',
        'x-photoshop',
        'model/vnd.usdz+zip',
    ];

    /**
     * Array of partial or full mime types that should be considered video resource types by Cloudinary.
     *
     * @var string[]
     */
    private const DEFAULT_VIDEO_TYPES = [
        'video',
        'vnd.apple.mpegurl',
        'dash+xml',
        'mxf',
    ];

    /**
     * Array of partial or full mime types that should be considered audio resource types by Cloudinary.
     *
     * @var string[]
     */
    private const DEFAULT_AUDIO_TYPES = [
        'audio',
    ];

    /**
     * Partial or full mime types considered as image resource types.
     *
     * @var string[]
     */
    private $imageTypes;

    /**
     * Partial or full mime types considered as video resource types.
     *
     * @var string[]
     */
    private $videoTypes;

    /**
     * Partial or full mime types considered as audio resource types.
     *
     * @var string[]
     */
    private $audioTypes;

    /**
     * Constructs a new CloudinaryMimeTypeConverter.
     *
     * @param string[]|null $imageTypes Array of partial or file mime types to be considered image resources
     * @param string[]|null $videoTypes Array of partial or file mime types to be considered video resources
     * @param string[]|null $audioTypes Array of partial or file mime types to be considered audio resources
     *
     * @return void
     */
    public function __construct(?array $imageTypes = null, ?array $videoTypes = null, ?array $audioTypes = null)
    {
        $this->imageTypes = $imageTypes ?? self::DEFAULT_IMAGE_TYPES;
        $this->videoTypes = $videoTypes ?? self::DEFAULT_VIDEO_TYPES;
        $this->audioTypes = $audioTypes ?? self::DEFAULT_AUDIO_TYPES;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection MultipleReturnStatementsInspection
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function mimeTypeToResourceType(?string $mimeType): string
    {
        // Assume raw for no mime type returned.
        if (! $mimeType) {
            return ResourceTypes::RAW()->getValue();
        }

        // Handle image mime types, with special cases for several Cloudinary supported formats that don't contain image
        // in the mime type.
        foreach ($this->imageTypes as $imageType) {
            if (mb_strpos($mimeType, $imageType) !== false) {
                return ResourceTypes::IMAGE()->getValue();
            }
        }

        // Handle video mime types, with special cases for several Cloudinary supported formats that don't contain video
        // in the mime type.
        foreach ($this->videoTypes as $videoType) {
            if (mb_strpos($mimeType, $videoType) !== false) {
                return ResourceTypes::VIDEO()->getValue();
            }
        }

        // Handle audio mime types, with special cases for several Cloudinary supported formats that don't contain audio
        // in the mime type.
        foreach ($this->audioTypes as $audioType) {
            if (mb_strpos($mimeType, $audioType) !== false) {
                return ResourceTypes::AUDIO()->getValue();
            }
        }

        // Default to raw.
        return ResourceTypes::RAW()->getValue();
    }
}
