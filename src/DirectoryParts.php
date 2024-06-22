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

use function count;
use function explode;
use function implode;
use function array_pop;
use function array_filter;

/**
 * Splits a given path into the last directory and then the root directory.
 *
 * @internal
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class DirectoryParts
{
    /**
     * Directory base name (top level name).
     *
     * @var string
     */
    private $baseName;

    /**
     * Directory root name (everything but the top level).
     *
     * @var string
     */
    private $dirName;

    /**
     * Constructs a new DirectoryParts.
     *
     * @param string $path Directory path
     *
     * @return void
     */
    public function __construct(string $path)
    {
        // Break into subfolders.
        $dirParts = array_filter(explode('/', $path));

        // The last folder is what we are searching for.
        $this->baseName = (string) array_pop($dirParts);
        // The rest is where we will query Cloudinary for to get a list of subfolders in that folder.
        $this->dirName = count($dirParts) > 0 ? implode('/', $dirParts) : '';
    }

    /**
     * Directory base name (top level name).
     *
     * @return string Directory basename
     */
    public function baseName(): string
    {
        return $this->baseName;
    }

    /**
     * Directory root name (everything but the top level).
     *
     * @return string Directory name
     */
    public function dirName(): string
    {
        return $this->dirName;
    }
}
