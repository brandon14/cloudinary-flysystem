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

namespace Brandon14\CloudinaryFlysystem\Tests\Integration;

use Generator;
use Throwable;

use const STDOUT;

use Dotenv\Dotenv;

use function sleep;
use function fwrite;
use function bin2hex;

use Cloudinary\Cloudinary;

use function random_bytes;

use League\Flysystem\Config;

use function iterator_to_array;
use function reset_function_mocks;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToSetVisibility;
use Brandon14\CloudinaryFlysystem\CloudinaryAdapter;
use Brandon14\CloudinaryFlysystem\Contracts\Configuration;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;

/**
 * Integration tests for the {@link \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter} library. It uses the
 * Flysystem adapter test utilities to perform integration tests.
 *
 * @author Brandon Clothier <brclothier@trollandtoad.com>
 */
final class CloudinaryAdapterIntegrationTest extends FilesystemAdapterTestCase
{
    /**
     * Whether the assets uploaded should be cleaned up after testing.
     *
     * @var bool
     */
    private static $shouldCleanUp = true;

    /**
     * Base adapter prefix.
     *
     * @var string
     */
    private static $baseAdapterPrefix = 'ci';

    /**
     * Adapter prefix.
     *
     * @var array|false|string
     */
    private static $adapterPrefix = 'ci';

    /**
     * Cloudinary client.
     *
     * @var \Cloudinary\Cloudinary|null
     */
    private static $client;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set prefix for random folder for each test.
        self::$adapterPrefix = self::$baseAdapterPrefix.'/'.bin2hex(random_bytes(32));
    }

    /**
     * Get tested Flysystem adapter.
     */
    public function adapter(): FilesystemAdapter
    {
        /** @var \Brandon14\CloudinaryFlysystem\CloudinaryAdapter $adapter */
        $adapter = parent::adapter();

        // Set our prefix each time the adapter is obtained to make sure it is prefixed for each individual test.
        $adapter->getConfiguration()->setPrefix(self::$adapterPrefix);

        return $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (! self::$shouldCleanUp) {
            return;
        }

        // Clean up all uploaded resources after test suite has run. Just delete the entire ci directory to clean up
        // all uploaded test files. It will recursively delete everything inside the subfolders.
        /** @var \Brandon14\CloudinaryFlysystem\Contracts\CloudinaryAdapter $adapter */
        $adapter = self::createFilesystemAdapter();
        // Set prefix to the base ci folder.
        $adapter->getConfiguration()->setPrefix(self::$baseAdapterPrefix);
        try {
            $adapter->deleteDirectory('');
        } catch (Throwable $exception) {
            fwrite(
                STDOUT,
                "Unable to clear storage after unit tests with message [{$exception->getMessage()}].".PHP_EOL
            );
        }
    }

    /**
     * Clean up storage after running tests. We override this to not perform any cleanup since we will clean up
     * everything once the tests are done.
     */
    public function clearStorage(): void
    {
        reset_function_mocks();
    }

    /**
     * Get the actual Cloudinary client instance.
     *
     * @return \Cloudinary\Cloudinary Cloudinary instance
     */
    private static function cloudinaryClient(): Cloudinary
    {
        if (self::$client instanceof Cloudinary) {
            return self::$client;
        }

        // Get environment config for Cloudinary integration tests.
        // ? TODO: getenv() is not working here for some reason.
        $url = $_ENV['CLOUDINARY_URL'] ?? null;

        // Load using dotenv if it's not present.
        if (! $url) {
            $dotenv = Dotenv::createImmutable(__DIR__.'/../../');
            $dotenv->load();

            $url = $_ENV['CLOUDINARY_URL'];
        }

        return self::$client = new Cloudinary($url);
    }

    /**
     * Get tested Flysystem adapter.
     *
     * @return \League\Flysystem\FilesystemAdapter Flysystem adapter
     */
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $configuration = new Configuration(self::$adapterPrefix);

        return new CloudinaryAdapter(self::cloudinaryClient(), $configuration);
    }

    /**
     * Override default behavior test since Cloudinary does not support altering visibility via their APIs.
     */
    public function setting_visibility(): void
    {
        // Expect exception to be thrown since we don't support setting visibility.
        $this->expectException(UnableToSetVisibility::class);

        parent::setting_visibility();
    }

    /**
     * Override default behavior since Cloudinary does not support writing empty files via their APIs.
     */
    public function writing_a_file_with_an_empty_stream(): void
    {
        $this->expectException(UnableToWriteFile::class);

        parent::writing_a_file_with_an_empty_stream();
    }

    /**
     * Override default behavior to give some time between writing the files and reading them since we are hitting the
     * live API.
     */
    public function listing_a_toplevel_directory(): void
    {
        $this->givenWeHaveAnExistingFile('path1.txt');
        $this->givenWeHaveAnExistingFile('path2.txt');

        sleep(1);

        $this->runScenario(function () {
            $contents = iterator_to_array($this->adapter()->listContents('', true));

            self::assertCount(2, $contents);
        });
    }

    /**
     * Override default behavior to give some time between writing the files and reading them since we are hitting the
     * live API.
     */
    public function listing_contents_shallow(): void
    {
        $this->runScenario(function () {
            $this->givenWeHaveAnExistingFile('some/0-path.txt', 'contents');
            $this->givenWeHaveAnExistingFile('some/1-nested/path.txt', 'contents');

            sleep(1);

            $listing = $this->adapter()->listContents('some', false);
            /** @var StorageAttributes[] $items */
            $items = iterator_to_array($listing);

            self::assertInstanceOf(Generator::class, $listing);
            self::assertContainsOnlyInstancesOf(StorageAttributes::class, $items);

            self::assertCount(2, $items, $this->formatIncorrectListingCount($items));

            // Order of entries is not guaranteed
            [$fileIndex, $directoryIndex] = $items[0]->isFile() ? [0, 1] : [1, 0];

            self::assertSame('some/0-path.txt', $items[$fileIndex]->path());
            self::assertSame('some/1-nested', $items[$directoryIndex]->path());
            self::assertTrue($items[$fileIndex]->isFile());
            self::assertTrue($items[$directoryIndex]->isDir());
        });
    }

    /**
     * Override default behavior to give some time between writing the files and reading them since we are hitting the
     * live API.
     */
    public function listing_contents_recursive(): void
    {
        $this->runScenario(function () {
            $adapter = $this->adapter();

            $adapter->createDirectory('path', new Config());
            $adapter->write('path/file.txt', 'string', new Config());

            sleep(1);

            $listing = $adapter->listContents('', true);
            /** @var StorageAttributes[] $items */
            $items = iterator_to_array($listing);

            self::assertCount(2, $items, $this->formatIncorrectListingCount($items));
        });
    }

    // ! TODO: Add more integration test cases to cover specific Cloudinary cases.
}
