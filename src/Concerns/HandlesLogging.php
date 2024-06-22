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

namespace Brandon14\CloudinaryFlysystem\Concerns;

use Throwable;

use function compact;

use Psr\Log\LogLevel;
use DateTimeImmutable;
use DateTimeInterface;

use function array_merge;

use Psr\Log\LoggerInterface;

use function date_create_immutable;

use Brandon14\CloudinaryFlysystem\Contracts\NoValidLoggerProvidedException;

/**
 * Trait to consolidate PSR logging concerns for a class. Should be used with the {@link \Psr\Log\LoggerAwareInterface}
 * contract.
 *
 * @internal
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
trait HandlesLogging
{
    /**
     * Whether logging is enabled.
     *
     * @var bool
     */
    private $logging = false;

    /**
     * PSR compliant logging interface.
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * {@inheritDoc}
     *
     * @returns $this
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogging(bool $logging): self
    {
        if ($logging === true && $this->logger === null) {
            throw new NoValidLoggerProvidedException('No PSR compliant logger provided. Cannot enable logging.');
        }

        $this->logging = $logging;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogging(): bool
    {
        return $this->logging;
    }

    /**
     * {@inheritDoc}
     */
    public function enableLogging(): self
    {
        return $this->setLogging(true);
    }

    /**
     * {@inheritDoc}
     *
     * @return $this
     */
    public function disableLogging(): self
    {
        return $this->setLogging(false);
    }

    /**
     * {@inheritDoc}
     */
    public function isLoggingEnabled(): bool
    {
        return $this->logging === true;
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::DEBUG} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function debug(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::DEBUG, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::INFO} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function info(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::INFO, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::NOTICE} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function notice(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::NOTICE, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::WARNING} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function warning(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::WARNING, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::ERROR} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function error(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::ERROR, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::CRITICAL} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function critical(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::CRITICAL, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::ALERT} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function alert(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::ALERT, $context);
    }

    /**
     * Helper method to log at the {@link \Psr\Log\LogLevel::EMERGENCY} level.
     *
     * @param string $message Log message
     * @param array  $context Optional log context array
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function emergency(string $message, array $context = []): void
    {
        $this->log($message, LogLevel::EMERGENCY, $context);
    }

    /**
     * Write out to PSR-3 logger instance if one was provided and the set level permits the desired
     * {@link $level} to be logged out.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @param string $message Log message
     * @param string $level   Log level
     * @param array  $context Logging context
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    private function log(
        string $message,
        string $level = LogLevel::ERROR,
        array $context = []
    ): void {
        // Exit if no logger or logging is disabled.
        if (! $this->logging || $this->logger === null) {
            return;
        }

        // This should never happen.
        // @codeCoverageIgnoreStart
        try {
            $timestamp = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
        } catch (Throwable $exception) {
            $timestamp = date_create_immutable()->format(DateTimeInterface::ATOM);
        }
        // @codeCoverageIgnoreEnd

        // Log message.
        $this->logger->log(
            $level,
            $message,
            array_merge($this->getLoggingContext(), compact('timestamp'), $context)
        );
    }

    /**
     * Get the classes optional array of context to log out. Can be overridden to set class
     * wide context.
     *
     * @noinspection PhpMethodNamingConventionInspection
     *
     * @return array Array of logging context
     */
    protected function getLoggingContext(): array
    {
        return [];
    }
}
