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

namespace ScssPhp\ScssPhp\Ast\Sass\Expression;

use ScssPhp\ScssPhp\Ast\Sass\Expression;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Value\SassColor;
use ScssPhp\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A color literal.
 *
 * @internal
 */
final class ColorExpression implements Expression
{
    /**
     * @var SassColor
     * @readonly
     */
    private $value;

    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    public function __construct(SassColor $value, FileSpan $span)
    {
        $this->value = $value;
        $this->span = $span;
    }

    public function getValue(): SassColor
    {
        return $this->value;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitColorExpression($this);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
