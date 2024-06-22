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

namespace Brandon14\CloudinaryFlysystem\Tests\Unit;

use Dotenv\Dotenv;
use Cloudinary\Cloudinary;
use PHPUnit\Framework\TestCase;
use Brandon14\CloudinaryFlysystem\CloudinaryAdapter;
use Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubClient;
use Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter as CloudinaryAdapterInterface;

/**
 * Unit tests for the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} Flysystem adapter.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class CloudinaryAdapterUnitTest extends TestCase
{
    /**
     * Get the stubbed {@link \Cloudinary\Cloudinary} client for mocked calls.
     *
     * @return \Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubClient Stubbed client
     */
    private function getCloudinaryStub(): CloudinaryStubClient
    {
        // Get environment config for Cloudinary unit tests.
        // ? TODO: getenv() is not working here for some reason.
        $url = $_ENV['CLOUDINARY_URL'] ?? null;

        // Load using dotenv if it's not present.
        if (! $url) {
            $dotenv = Dotenv::createImmutable(__DIR__.'/../../');
            $dotenv->load();

            $url = $_ENV['CLOUDINARY_URL'];
        }

        return new CloudinaryStubClient(new Cloudinary($url));
    }

    /**
     * Get the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} instance for unit testing.
     *
     * @return \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter Flysystem adapter
     */
    private function getAdapter(): CloudinaryAdapterInterface
    {
        return new CloudinaryAdapter($this->getCloudinaryStub());
    }

    /**
     * Test that true is true.
     */
    public function test_true_is_true(): void
    {
        $this::assertTrue(true);
    }

    // ! TODO: Add in unit tests.
}
