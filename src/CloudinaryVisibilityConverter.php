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

use function implode;

use League\Flysystem\Visibility;
use Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes;
use Brandon14\CloudinaryFlysystem\Contracts\VisibilityConverter;
use Brandon14\CloudinaryFlysystem\Contracts\InvalidVisibilityException;

/**
 * Class to manage converting Flysystem visibilities to Cloudinary compatible visibilities.
 *
 * @noinspection PhpMethodNamingConventionInspection
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class CloudinaryVisibilityConverter implements VisibilityConverter
{
    /**
     * Default upload type for files.
     *
     * @var \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes
     */
    private $defaultForFiles;

    /**
     * Default private upload type.
     *
     * @var \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes
     */
    private $defaultPrivate;

    /**
     * Constructs a new CloudinaryVisibilityConverter.
     *
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes|null $defaultForFiles Default upload type for
     *                                                                                         files
     * @param \Brandon14\CloudinaryFlysystem\Contracts\Enums\UploadTypes|null $defaultPrivate  Default private upload
     *                                                                                         type
     *
     * @return void
     */
    public function __construct(?UploadTypes $defaultForFiles = null, ?UploadTypes $defaultPrivate = null)
    {
        $this->defaultForFiles = $defaultForFiles ?? UploadTypes::UPLOAD();
        $this->defaultPrivate = $defaultPrivate ?? UploadTypes::AUTHENTICATED();
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function visibilityToUploadType(string $visibility): UploadTypes
    {
        if ($visibility === Visibility::PUBLIC) {
            return UploadTypes::UPLOAD();
        }

        if ($visibility === Visibility::PRIVATE) {
            return $this->defaultPrivate;
        }

        throw InvalidVisibilityException::withVisibility($visibility, 'one of [\'public\', \'private\']');
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function uploadTypeToVisibility(UploadTypes $uploadType): string
    {
        if ($uploadType->equals(UploadTypes::UPLOAD())) {
            return Visibility::PUBLIC;
        }

        if ($uploadType->equals(UploadTypes::AUTHENTICATED()) || $uploadType->equals(UploadTypes::PRIVATE())) {
            return Visibility::PRIVATE;
        }

        $provided = $uploadType->getValue();
        $expected = implode(', ', array_values(UploadTypes::toArray()));

        throw InvalidVisibilityException::withVisibility($provided, "one of [{$expected}]");
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function defaultUploadType(): UploadTypes
    {
        return $this->defaultForFiles;
    }

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMethodNamingConventionInspection
     */
    public function defaultVisibility(): string
    {
        return $this->uploadTypeToVisibility($this->defaultForFiles);
    }
}
