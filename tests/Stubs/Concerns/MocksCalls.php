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

namespace Brandon14\CloudinaryFlysystem\Tests\Stubs\Concerns;

use Exception;
use Throwable;

use function time;
use function array_shift;
use function array_key_exists;

use Cloudinary\Api\ApiResponse;
use GuzzleHttp\Exception\TransferException;

/**
 * Trait to set up mock exceptions and responses on the Cloudinary API stubs for testing.
 *
 * @internal
 *
 * @author Brandon Clothier <brclothier@trollandtoad.com>
 */
trait MocksCalls
{
    /**
     * Array of 'callName' => Exception to stub in to be thrown when the command is run.
     *
     * @var array<string, array<int, Exception>>
     */
    protected $stagedExceptions = [];

    /**
     * Array of 'callName' => array responses to stub in to be returned when the command is run.
     *
     * @var array<string, array<int, array{
     *     response: array,
     *     headers: array,
     * }>>
     */
    protected $stagedResults = [];

    /**
     * Stages an exception to be thrown when a command is called.
     *
     * @param string         $commandName Name of the API command
     * @param Throwable|null $exception   Exception to be thrown. Defaults to a
     *                                    {@link \GuzzleHttp\Exception\TransferException}
     *
     * @return \Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubAdminApi|\Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubSearchApi|\Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubUploadApi
     */
    public function throwExceptionWhenExecutingCommand(string $commandName, ?Throwable $exception = null): self
    {
        $this->stagedExceptions[$commandName][] = $exception ?? new TransferException();

        return $this;
    }

    /**
     * Stages an array of response data to be returned when a command is called.
     *
     * @param string $commandName Name of the API command
     * @param array{
     *     response: array,
     *     headers: array,
     * }             $result      API response data
     *
     * @return \Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubAdminApi|\Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubSearchApi|\Brandon14\CloudinaryFlysystem\Tests\Stubs\CloudinaryStubUploadApi
     */
    public function stageResultForCommand(string $commandName, array $result): self
    {
        $this->stagedResults[$commandName][] = $result;

        return $this;
    }

    /**
     * Method to get the staged result for the execution of a command.
     *
     * @param string $commandName Name of API command
     *
     * @throws Throwable
     *
     * @return \Cloudinary\Api\ApiResponse|null API response
     */
    private function getStagedResult(string $commandName): ?ApiResponse
    {
        // Check for staged exceptions on the mock command.
        if (array_key_exists($commandName, $this->stagedExceptions)) {
            // Throw the first exception staged for this command.
            throw array_shift($this->stagedExceptions[$commandName]);
        }

        // Check for staged responses on the mocked command.
        if (array_key_exists($commandName, $this->stagedResults)) {
            // Get the first response staged for this command.
            $result = array_shift($this->stagedResults[$commandName]);
            $response = $result['response'] ?? [];
            $headers = $result['headers'] ?? [];

            // Check for required headers on an ApiResponse class.
            if (! isset($headers['x-featureratelimit-reset'])) {
                /** @noinspection UnsupportedStringOffsetOperationsInspection */
                $headers['x-featureratelimit-reset'][] = time();
            }

            if (! isset($headers['x-featureratelimit-limit'])) {
                /** @noinspection UnsupportedStringOffsetOperationsInspection */
                $headers['x-featureratelimit-limit'][] = 0;
            }

            if (! isset($headers['x-featureratelimit-remaining'])) {
                /** @noinspection UnsupportedStringOffsetOperationsInspection */
                $headers['x-featureratelimit-remaining'][] = 999;
            }

            return new ApiResponse($response, $headers);
        }

        return null;
    }
}
