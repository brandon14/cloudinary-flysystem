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
 * Enum of Cloudinary upload types.
 *
 * @see https://cloudinary.com/documentation/image_upload_api_reference#upload_optional_parameters
 *
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes UPLOAD()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes AUTHENTICATED()
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes PRIVATE()
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class UploadTypes extends Enum
{
    /**
     * Normal upload type.
     *
     * @var string
     */
    private const UPLOAD = 'upload';

    /**
     * Authenticated upload type. This will restrict base resource and derived transformations from public access.
     *
     * @var string
     */
    private const AUTHENTICATED = 'authenticated';

    /**
     * Private upload type. This will restrict base resource from public access.
     *
     * @var string
     */
    private const PRIVATE = 'private';
}
