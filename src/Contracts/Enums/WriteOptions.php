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
use League\Flysystem\Config;

/**
 * Enum for the various write options for the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} for the
 * method {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter::write()} and
 * {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter::writeStream}, as well as the methods
 * {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter::copy()} and
 * {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter::move()}.
 *
 * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
 *
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions RESOURCE_TYPE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions PUBLIC_ID()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions UPLOAD_TYPE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions ACCESS_MODE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions ACCESS_CONTROL()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions TO_TYPE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions UPLOAD_PRESET()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions VISIBILITY()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions INVALIDATE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions OVERWRITE()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions PHASH()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions METADATA()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions TAGS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions CONTEXT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions BACKUP()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions RESPONSIVE_BREAKPOINTS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions AUTO_TAGGING()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions CATEGORIZATION()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions DETECTION()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions AUTO_CHAPTERING()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions AUTO_TRANSCRIPTION()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions OCR()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions VISUAL_SEARCH()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions EAGER()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions TRANSFORMATION()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions FORMAT()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions CUSTOM_COORDINATES()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions REGIONS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions FACE_COORDINATES()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions BACKGROUND_REMOVAL()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions HEADERS()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions CLOUDINARY_OPTIONS()
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class WriteOptions extends Enum
{
    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary resource type.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const RESOURCE_TYPE = 'resource_type';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary public ID.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const PUBLIC_ID = 'public_id';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary upload API type.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const UPLOAD_TYPE = 'upload_type';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary access mode option.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const ACCESS_MODE = 'access_mode';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary access control configuration. Should be an array
     * of access types. Can be a 'token' based access type. Can also be an 'anonymous' access type with an optional
     * 'start' and 'end' date.
     *
     * For example:
     *
     * ```php
     * $accessControls = [
     *     ['access_type' => 'token'],
     *     ['access_type' => 'anonymous', 'start' => '2022-12-15T12:00Z', 'end' => '2023-01-20T12:00Z'],
     * ];
     *
     * $config = new Config(['access_control' => $accessControls]);
     * ```
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     * @see https://cloudinary.com/documentation/control_access_to_media#access_controlled_media_assets
     *
     * @var string
     */
    private const ACCESS_CONTROL = 'access_control';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to upload API type.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const TO_TYPE = 'to_type';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary upload preset.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const UPLOAD_PRESET = 'upload_preset';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for visibility.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const VISIBILITY = Config::OPTION_VISIBILITY;

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to specify invalidating CDN cache for uploaded
     * resource.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const INVALIDATE = 'invalidate';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to specify overwriting file.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const OVERWRITE = 'overwrite';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary pHash. Relevant for images only.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const PHASH = 'phash';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary metadata options. Should be a map of
     * metadata_name => metadata_value.
     * **NOTE**: These must exist as structural metadata options for the account in
     * question otherwise the call will fail.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     * @see https://cloudinary.com/documentation/dam_admin_structured_metadata
     *
     * @var string
     */
    private const METADATA = 'metadata';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary tag options. Should be a map of
     * tag_name => tag_value.
     *
     * @noinspection PhpConstantNamingConventionInspection
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const TAGS = 'tags';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary context options. Should be a map of
     * context_name => context_value.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const CONTEXT = 'context';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to back up the uploaded asset. Should be a bool.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const BACKUP = 'backup';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to create responsive breakpoints automatically.
     * Should be an array of responsive breakpoint options outlined in the Cloudinary Upload API optional parameters.
     * Relevant for images only.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_examples
     *
     * @var string
     */
    private const RESPONSIVE_BREAKPOINTS = 'responsive_breakpoints';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary automatically assign tags to a resource
     * according to the detected objects or categories with a confidence score higher than the specified value. Should
     * be a float between 0.0 and 1.0. Use with
     * {@link \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions::DETECTION()} parameter for Cloudinary AI
     * Analysis or Amazon Rekognition Celebrity Detection.
     * Use with {@link \Brandon14\CloudinaryFlysystem\Contracts\Enums\WriteOptions::CATEGORIZATION()} parameter for
     * Google Automatic Video Tagging, Google Auto Tagging, Imagga Auto Tagging or Amazon Rekognition Auto Tagging.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const AUTO_TAGGING = 'auto_tagging';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to run categorization add-ons on the resource.
     * Should be one of 'google_tagging', 'google_video_tagging', 'imagga_tagging', and/or 'aws_rek_tagging'.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const CATEGORIZATION = 'categorization';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to invoke the relevant add-on to return a list
     * of detected content. Should be one of '<content-aware-model>_[<version>] (e.g. coco_v2)', 'captioning',
     * 'adv_face', 'aws_rek_face'.
     * **NOTE**: Some of these add-ons may require subscriptions with Cloudinary in order to
     * function, otherwise the API call may fail.
     *
     * @var string
     */
    private const DETECTION = 'detection';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to trigger automatic generation of video
     * chapters. Relevant only for videos.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const AUTO_CHAPTERING = 'auto_chaptering';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to trigger automatic video transcription.
     * Relevant for videos only.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const AUTO_TRANSCRIPTION = 'auto_transcription';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to extract text elements. Should be set as
     * 'adv_ocr'. Relevant only for images.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const OCR = 'ocr';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to index the resource for use with visual
     * searches. Relevant only for images. Should be a bool true or false.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const VISUAL_SEARCH = 'visual_search';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to run a list of eager transformations on the
     * resource. This accepts either a single transformation or a list of transformation. See examples for usage.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_examples
     *
     * @var string
     */
    private const EAGER = 'eager';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to run a list of transformations to run on the
     * resource. Should be a map of transformation parameters, or an array of maps of transformation parameters for
     * chained transformations.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_examples
     *
     * @var string
     */
    private const TRANSFORMATION = 'transformation';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to convert the uploaded asset to a different
     * format. For example 'jpg'.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const FORMAT = 'format';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to set the coordinates of a region contained in
     * the resource that can be used for cropping or adding layers using the 'custom' gravity mode. The region is
     * specified by the X & Y coordinates of the top left corner and the width & height of the region. Should be an
     * array, such as '[85, 120, 220, 310]'. Relevant for images only.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const CUSTOM_COORDINATES = 'custom_coordinates';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to set the coordinates of one or more named
     * regions in the resource. Each region is specified by a name and an array of at least two X,Y coordinate pairs,
     * such as '{'name1': [[1, 2], [3, 4]], 'name2': [[5, 6], [7, 8], [9, 10]]}'. Relevant for images only.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const REGIONS = 'regions';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to set the coordinates of faces contained in an
     * uploaded asset. Each face is specified by the  X & Y coordinates of the top left corner and width & height of the
     * face, such as '[[10, 20, 150, 130], [213, 345, 82, 61]]'. Relevant only for images.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const FACE_COORDINATES = 'face_coordinates';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for Cloudinary to automatically remove the background of an
     * image using an add-on. Set to 'cloudinary-ai' or 'pixelz'. Relevant for images only. This is an asynchronous
     * command.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const BACKGROUND_REMOVAL = 'background_removal';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for optional Cloudinary resource headers to be delivered with
     * the resource. Should be a string HTTP header or an array of HTTP headers.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const HEADERS = 'headers';

    /**
     * Flysystem {@link \League\Flysystem\Config} option for optional Cloudinary options. Should be an array of
     * 'option_name' => 'option_value' as defined by Cloudinary's Upload API optional parameters.
     *
     * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
     *
     * @var string
     */
    private const CLOUDINARY_OPTIONS = 'cloudinary_options';
}
