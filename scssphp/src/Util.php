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

namespace ScssPhp\ScssPhp;

use ScssPhp\ScssPhp\Base\Range;
use ScssPhp\ScssPhp\Exception\RangeException;
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Util\StringUtil;

/**
 * Utility functions
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @internal
 */
final class Util
{
    /**
     * Returns $string with every line indented $indentation spaces.
     */
    public static function indent(string $string, int $indentation): string
    {
        return implode("\n", array_map(function ($line) use ($indentation) {
            return str_repeat(' ', $indentation) . $line;
        }, explode("\n", $string)));
    }

    /**
     * Asserts that `value` falls within `range` (inclusive), leaving
     * room for slight floating-point errors.
     *
     * @param string       $name  The name of the value. Used in the error message.
     * @param Range        $range Range of values.
     * @param array|Number $value The value to check.
     * @param string       $unit  The unit of the value. Used in error reporting.
     *
     * @return mixed `value` adjusted to fall within range, if it was outside by a floating-point margin.
     *
     * @throws RangeException
     */
    public static function checkRange(string $name, Range $range, $value, string $unit = '')
    {
        $val = $value[1];
        $grace = new Range(-0.00001, 0.00001);

        if (! \is_numeric($val)) {
            throw new RangeException("$name {$val} is not a number.");
        }

        if ($range->includes($val)) {
            return $val;
        }

        if ($grace->includes($val - $range->first)) {
            return $range->first;
        }

        if ($grace->includes($val - $range->last)) {
            return $range->last;
        }

        throw new RangeException("$name {$val} must be between {$range->first} and {$range->last}$unit");
    }

    /**
     * Encode URI component
     *
     * @param string $string
     *
     * @return string
     */
    public static function encodeURIComponent(string $string): string
    {
        $revert = ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'];

        return strtr(rawurlencode($string), $revert);
    }

    /**
     * Returns the variable name (including the leading `$`) from a $span that
     * covers a variable declaration, which includes the variable name as well as
     * the colon and expression following it.
     *
     * This isn't particularly efficient, and should only be used for error
     * messages.
     */
    public static function declarationName(FileSpan $span): string
    {
        $text = $span->getText();
        $pos = strpos($text, ':');

        return StringUtil::trimAsciiRight(substr($text, 0, $pos === false ? null : $pos));
    }

    /**
     * Returns $name without a vendor prefix.
     *
     * If $name has no vendor prefix, it's returned as-is.
     *
     * @param string $name
     *
     * @return string
     */
    public static function unvendor(string $name): string
    {
        $length = \strlen($name);

        if ($length < 2) {
            return $name;
        }

        if ($name[0] !== '-') {
            return $name;
        }

        if ($name[1] === '-') {
            return $name;
        }

        for ($i = 2; $i < $length; $i++) {
            if ($name[$i] === '-') {
                return substr($name, $i + 1);
            }
        }

        return $name;
    }

    /**
     * mb_chr() wrapper
     *
     * @param int $code
     *
     * @return string
     */
    public static function mbChr(int $code): string
    {
        // Use the native implementation if available, but not on PHP 7.2 as mb_chr(0) is buggy there
        if (\PHP_VERSION_ID > 70300 && \function_exists('mb_chr')) {
            return mb_chr($code, 'UTF-8');
        }

        if (0x80 > $code %= 0x200000) {
            $s = \chr($code);
        } elseif (0x800 > $code) {
            $s = \chr(0xC0 | $code >> 6) . \chr(0x80 | $code & 0x3F);
        } elseif (0x10000 > $code) {
            $s = \chr(0xE0 | $code >> 12) . \chr(0x80 | $code >> 6 & 0x3F) . \chr(0x80 | $code & 0x3F);
        } else {
            $s = \chr(0xF0 | $code >> 18) . \chr(0x80 | $code >> 12 & 0x3F)
                . \chr(0x80 | $code >> 6 & 0x3F) . \chr(0x80 | $code & 0x3F);
        }

        return $s;
    }

    /**
     * mb_ord() wrapper
     *
     * @param string $string
     *
     * @return int
     */
    public static function mbOrd(string $string): int
    {
        if (\function_exists('mb_ord')) {
            return mb_ord($string, 'UTF-8');
        }

        if (1 === \strlen($string)) {
            return \ord($string);
        }

        $s = unpack('C*', substr($string, 0, 4));

        if (!$s) {
            return 0;
        }

        $code = $s[1];
        if (0xF0 <= $code) {
            return (($code - 0xF0) << 18) + (($s[2] - 0x80) << 12) + (($s[3] - 0x80) << 6) + $s[4] - 0x80;
        }
        if (0xE0 <= $code) {
            return (($code - 0xE0) << 12) + (($s[2] - 0x80) << 6) + $s[3] - 0x80;
        }
        if (0xC0 <= $code) {
            return (($code - 0xC0) << 6) + $s[2] - 0x80;
        }

        return $code;
    }

    /**
     * mb_strlen() wrapper
     *
     * @param string $string
     * @return int
     */
    public static function mbStrlen(string $string): int
    {
        // Use the native implementation if available.
        if (\function_exists('mb_strlen')) {
            return mb_strlen($string, 'UTF-8');
        }

        if (\function_exists('iconv_strlen')) {
            return (int) @iconv_strlen($string, 'UTF-8');
        }

        throw new \LogicException('Either mbstring (recommended) or iconv is necessary to use Scssphp.');
    }

    /**
     * mb_substr() wrapper
     * @param string $string
     * @param int $start
     * @param null|int $length
     * @return string
     */
    public static function mbSubstr(string $string, int $start, ?int $length = null): string
    {
        // Use the native implementation if available.
        if (\function_exists('mb_substr')) {
            return mb_substr($string, $start, $length, 'UTF-8');
        }

        if (\function_exists('iconv_substr')) {
            if ($start < 0) {
                $start = static::mbStrlen($string) + $start;
                if ($start < 0) {
                    $start = 0;
                }
            }

            if (null === $length) {
                $length = 2147483647;
            } elseif ($length < 0) {
                $length = static::mbStrlen($string) + $length - $start;
                if ($length < 0) {
                    return '';
                }
            }

            return (string)iconv_substr($string, $start, $length, 'UTF-8');
        }

        throw new \LogicException('Either mbstring (recommended) or iconv is necessary to use Scssphp.');
    }

    /**
     * mb_strpos wrapper
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     *
     * @return int|false
     */
    public static function mbStrpos(string $haystack, string $needle, int $offset = 0)
    {
        if (\function_exists('mb_strpos')) {
            return mb_strpos($haystack, $needle, $offset, 'UTF-8');
        }

        if (\function_exists('iconv_strpos')) {
            return iconv_strpos($haystack, $needle, $offset, 'UTF-8');
        }

        throw new \LogicException('Either mbstring (recommended) or iconv is necessary to use Scssphp.');
    }
}
