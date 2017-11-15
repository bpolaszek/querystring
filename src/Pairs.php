<?php

namespace BenTools\QueryString;

use IteratorAggregate;
use Traversable;

final class Pairs implements IteratorAggregate
{
    /**
     * @var QueryString
     */
    private $queryString;

    /**
     * @var bool
     */
    private $decodeKeys;

    /**
     * @var bool
     */
    private $decodeValues;

    /**
     * Pairs constructor.
     */
    public function __construct(
        QueryString $queryString,
        bool $decodeKeys = false,
        bool $decodeValues = false
    ) {

        $this->queryString = $queryString;
        $this->decodeKeys = $decodeKeys;
        $this->decodeValues = $decodeValues;
    }

    /**
     * @param QueryString $queryString
     * @return Pairs
     */
    public function withQueryString(QueryString $queryString): self
    {
        $clone = clone $this;
        $clone->queryString = $queryString;
        return $clone;
    }

    /**
     * @param bool $decodeKeys
     * @return Pairs
     */
    public function withDecodeKeys(bool $decodeKeys): self
    {
        $clone = clone $this;
        $clone->decodeKeys = $decodeKeys;
        return $clone;
    }

    /**
     * @param bool $decodeValues
     * @return Pairs
     */
    public function withDecodeValues(bool $decodeValues): self
    {
        $clone = clone $this;
        $clone->decodeValues = $decodeValues;
        return $clone;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        $separator = $this->queryString->getRenderer()->getSeparator() ?? ini_get('arg_separator.input');
        $pairs = explode($separator, (string) $this->queryString);
        foreach ($pairs as $pair) {
            list($key, $value) = explode('=', $pair);

            if (true === $this->decodeKeys) {
                $key = urldecode($key);
            }

            if (true === $this->decodeValues) {
                $value = urldecode($value);
            }

            yield $key => $value;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->queryString;
    }
}
