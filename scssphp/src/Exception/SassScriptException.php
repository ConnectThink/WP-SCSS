<?php

namespace ScssPhp\ScssPhp\Exception;

/**
 * An exception thrown by SassScript.
 *
 * This class does not implement SassException on purpose, as it should
 * never be returned to the outside code. The compilation will catch it
 * and replace it with a SassException reporting the location of the
 * error.
 */
final class SassScriptException extends \Exception
{
    /**
     * Creates a SassScriptException with support for an argument name.
     *
     * This helper ensures a consistent handling of argument names in the
     * error message, without duplicating it.
     *
     * @param string          $message
     * @param string|null     $name     The argument name, without $
     * @param \Throwable|null $previous
     *
     * @return SassScriptException
     */
    public static function forArgument(string $message, ?string $name = null, ?\Throwable $previous = null): SassScriptException
    {
        $varDisplay = !\is_null($name) ? "\${$name}: " : '';

        return new self($varDisplay . $message, 0, $previous);
    }
}
