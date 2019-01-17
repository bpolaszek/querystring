<?php

namespace BenTools\QueryString;

use IteratorAggregate;
use Traversable;

final class Pairs implements IteratorAggregate
{
    /**
     * @var string
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
     * @var null|string
     */
    private $separator;

    /**
     * Pairs constructor.
     */
    public function __construct(
        string $queryString,
        bool $decodeKeys = false,
        bool $decodeValues = false,
        string $separator = null
    ) {

        $this->queryString = $queryString;
        $this->decodeKeys = $decodeKeys;
        $this->decodeValues = $decodeValues;
        $this->separator = $separator;
    }

    /**
     * @param string $queryString
     * @return Pairs
     */
    public function withQueryString(string $queryString): self
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
     * @param null|string $separator
     * @return Pairs
     */
    public function withSeparator(?string $separator): self
    {
        $clone = clone $this;
        $clone->separator = $separator;
        return $clone;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        $separator = $this->separator ?? ini_get('arg_separator.input');

        if ('' === $separator) {
            throw new \RuntimeException("A separator cannot be blank.");
        }

        $pairs = explode($separator, $this->queryString);

        foreach ($pairs as $pair) {
            $keyValue = explode('=', $pair);
            $key = $keyValue[0];
            $value = $keyValue[1] ?? null;

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
