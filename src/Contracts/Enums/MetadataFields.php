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

namespace Brandon14\CloudinaryFlysystem\Contracts\Enums;

use MyCLabs\Enum\Enum;

/**
 * Enum for the various optional metadata fields that can optionally be included when fetching resources from the
 * Cloudinary API.
 *
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields FOLDER()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ASSET_FOLDER()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields WIDTH()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields HEIGHT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ACCESS_MODE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields TAGS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields IMAGE_METADATA()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ASSET_ID()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields VERSION()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields PHASH()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields QUALITY_ANALYSIS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields QUALITY_SCORE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ACCESSIBILITY_ANALYSIS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields MEDIA_METADATA()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields FACES()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields COLORS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields NEXT_CURSOR()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields EMBEDDED_IMAGES()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ILLUSTRATION_SCORE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields RELATED_ASSETS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields SEMI_TRANSPARENT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields GRAYSCALE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields PREDOMINANT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields USAGE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ORIGINAL_FILENAME()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields PIXELS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields PAGES()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ASPECT_RATIO()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields CREATED_AT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields UPLOADED_AT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields STATUS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields ACCESS_CONTROL()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields CREATED_BY()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields UPLOADED_BY()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\MetadataFields CINEMAGRAPH_ANALYSIS()
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class MetadataFields extends Enum
{
    /**
     * Cloudinary metadata field for folder information.
     *
     * @var string
     */
    private const FOLDER = 'folder';

    /**
     * Cloudinary metadata field for asset folder information.
     *
     * @var string
     */
    private const ASSET_FOLDER = 'asset_folder';

    /**
     * Cloudinary metadata field for resource width.
     *
     * @var string
     */
    private const WIDTH = 'width';

    /**
     * Cloudinary metadata field for resource height.
     *
     * @var string
     */
    private const HEIGHT = 'height';

    /**
     * Cloudinary metadata field for access mode information.
     *
     * @var string
     */
    private const ACCESS_MODE = 'access_mode';

    /**
     * Cloudinary metadata field for tag information.
     *
     * @var string
     */
    private const TAGS = 'tags';

    /**
     * Cloudinary metadata field for image metadata information.
     *
     * @var string
     */
    private const IMAGE_METADATA = 'image_metadata';

    /**
     * Cloudinary metadata field for asset ID.
     *
     * @var string
     */
    private const ASSET_ID = 'asset_id';

    /**
     * Cloudinary metadata field for resource version.
     *
     * @var string
     */
    private const VERSION = 'version';

    /**
     * Cloudinary metadata field for perceptual hash information.
     *
     * @var string
     */
    private const PHASH = 'phash';

    /**
     * Cloudinary metadata field for quality analysis information.
     *
     * @var string
     */
    private const QUALITY_ANALYSIS = 'quality_analysis';

    /**
     * Cloudinary metadata field for quality score information.
     *
     * @var string
     */
    private const QUALITY_SCORE = 'quality_score';

    /**
     * For accessibility analysis information.
     *
     * @var string
     */
    private const ACCESSIBILITY_ANALYSIS = 'accessibility_analysis';

    /**
     * Cloudinary metadata field for media metadata information.
     *
     * @var string
     */
    private const MEDIA_METADATA = 'media_metadata';

    /**
     * Cloudinary metadata field for faces information.
     *
     * @var string
     */
    private const FACES = 'faces';

    /**
     * Cloudinary metadata field for colors information.
     *
     * @var string
     */
    private const COLORS = 'colors';

    /**
     * Cloudinary metadata field for next cursor.
     *
     * @var string
     */
    private const NEXT_CURSOR = 'next_cursor';

    /**
     * Cloudinary metadata field for embedded images information.
     *
     * @var string
     */
    private const EMBEDDED_IMAGES = 'embedded_images';

    /**
     * Cloudinary metadata field for illustration score information.
     *
     * @var string
     */
    private const ILLUSTRATION_SCORE = 'illustration_score';

    /**
     * Cloudinary metadata field for related asset information.
     *
     * @var string
     */
    private const RELATED_ASSETS = 'related_assets';

    /**
     * Cloudinary metadata field for semi-transparent information.
     *
     * @var string
     */
    private const SEMI_TRANSPARENT = 'semi_transparent';

    /**
     * Cloudinary metadata field for grayscale information.
     *
     * @var string
     */
    private const GRAYSCALE = 'grayscale';

    /**
     * Cloudinary metadata field for predominant information.
     *
     * @var string
     */
    private const PREDOMINANT = 'predominant';

    /**
     * Cloudinary metadata field for usage information.
     *
     * @var string
     */
    private const USAGE = 'usage';

    /**
     * Cloudinary metadata field for original filename information.
     *
     * @var string
     */
    private const ORIGINAL_FILENAME = 'original_filename';

    /**
     * Cloudinary metadata field for pixels information.
     *
     * @var string
     */
    private const PIXELS = 'pixels';

    /**
     * Cloudinary metadata field for pages information.
     *
     * @var string
     */
    private const PAGES = 'pages';

    /**
     * Cloudinary metadata field for aspect ratio information.
     *
     * @var string
     */
    private const ASPECT_RATIO = 'aspect_ratio';

    /**
     * Cloudinary metadata field for created at information.
     *
     * @var string
     */
    private const CREATED_AT = 'created_at';

    /**
     * Cloudinary metadata field for uploaded at information.
     *
     * @var string
     */
    private const UPLOADED_AT = 'uploaded_at';

    /**
     * Cloudinary metadata field for status information.
     *
     * @var string
     */
    private const STATUS = 'status';

    /**
     * Cloudinary metadata field for access control information.
     *
     * @var string
     */
    private const ACCESS_CONTROL = 'access_control';

    /**
     * Cloudinary metadata field for created by information.
     *
     * @var string
     */
    private const CREATED_BY = 'created_by';

    /**
     * Cloudinary metadata field for uploaded by information.
     *
     * @var string
     */
    private const UPLOADED_BY = 'uploaded_by';

    /**
     * Cloudinary metadata field for cinemagraph analysis information.
     *
     * @var string
     */
    private const CINEMAGRAPH_ANALYSIS = 'cinemagraph_analysis';
}
