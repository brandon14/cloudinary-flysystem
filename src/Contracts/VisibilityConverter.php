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

use Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes;

/**
 * Interface to define a Flysystem visibility converter adapted to handle Cloudinary visibility/access management.
 *
 * @noinspection PhpClassNamingConventionInspection
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface VisibilityConverter
{
    /**
     * Converts a Flysystem compatible visibility string into a Cloudinary upload type.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidVisibilityException
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes Upload type converted from {@link $visibility}
     */
    public function visibilityToUploadType(string $visibility): UploadTypes;

    /**
     * Converts a Cloudinary upload type into a Flysystem compatible visibility string.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes $uploadType Cloudinary upload type
     *
     * @throws \Brandon14\CloudinaryFlysystem\Contracts\InvalidAccessModeException
     *
     * @return string Visibility converted from {@link $uploadType}
     */
    public function uploadTypeToVisibility(UploadTypes $uploadType): string;

    /**
     * Gets the default Cloudinary upload type.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes Default upload type
     */
    public function defaultUploadType(): UploadTypes;

    /**
     * Gets the default Flysystem visibility.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return string Default visibility
     */
    public function defaultVisibility(): string;
}
