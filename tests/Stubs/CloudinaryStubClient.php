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

namespace Brandon14\CloudinaryFlysystem\Tests\Stubs;

use Cloudinary\Cloudinary;

/**
 * Class to wrap actual {@link \Cloudinary\Cloudinary} SDK calls so that we can control when
 * it throws exceptions, etc.
 *
 * @internal
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class CloudinaryStubClient extends Cloudinary
{
    /**
     * Stubbed Admin API.
     *
     * @var \Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubAdminApi
     */
    private $adminStub;

    /**
     * Stubbed Upload API.
     *
     * @var \Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubUploadApi
     */
    private $uploadStub;

    /**
     * Stubbed Search API.
     *
     * @var \Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubSearchApi
     */
    private $searchStub;

    /**
     * Constructs a new StubCloudinaryClass.
     *
     * @param \Cloudinary\Cloudinary $client Actual Cloudinary client
     *
     * @return void
     */
    public function __construct(Cloudinary $client)
    {
        parent::__construct($client->configuration);

        // Set up stubs.
        $this->adminStub = new CloudinaryStubAdminApi($client->configuration);
        $this->uploadStub = new CloudinaryStubUploadApi($client->configuration);
        $this->searchStub = new CloudinaryStubSearchApi($client->configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function adminApi()
    {
        return $this->adminStub;
    }

    /**
     * {@inheritdoc}
     */
    public function uploadApi()
    {
        return $this->uploadStub;
    }

    /**
     * {@inheritdoc}
     */
    public function searchApi()
    {
        return $this->searchStub;
    }
}
