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
 * Enum for the checksum options provided by the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} for
 * the method {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter::checksum()}.
 *
 * @method static \Brandon14\CloudinaryFlysystem\Contracts\Enums\ChecksumOptions CHECKSUM_ALGORITHM()
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class ChecksumOptions extends Enum
{
    /**
     * Flysystem {@link \League\Flysystem\Config} option for Checksum algorithm to use. Should be an available hash
     * algorithm or 'etag'.
     *
     * @var string
     */
    private const CHECKSUM_ALGORITHM = 'checksum_algo';
}
