<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Logger;

/**
 * A logger that prints to a PHP stream (for instance stderr)
 *
 * TODO implement LocationAwareLoggerInterface once the compiler is migrated to actually provide the location
 */
final class StreamLogger implements LoggerInterface
{
    private $stream;
    private $closeOnDestruct;

    /**
     * @param resource $stream          A stream resource
     * @param bool     $closeOnDestruct If true, takes ownership of the stream and close it on destruct to avoid leaks.
     */
    public function __construct($stream, bool $closeOnDestruct = false)
    {
        $this->stream = $stream;
        $this->closeOnDestruct = $closeOnDestruct;
    }

    /**
     * @internal
     */
    public function __destruct()
    {
        if ($this->closeOnDestruct) {
            fclose($this->stream);
        }
    }

    /**
     * @inheritDoc
     */
    public function warn(string $message, bool $deprecation = false)
    {
        $prefix = ($deprecation ? 'DEPRECATION ' : '') . 'WARNING: ';

        fwrite($this->stream, $prefix . $message . "\n\n");
    }

    /**
     * @inheritDoc
     */
    public function debug(string $message)
    {
        fwrite($this->stream, $message . "\n");
    }
}
