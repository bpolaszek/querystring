<?php

namespace BenTools\QueryString\Renderer;

use BenTools\QueryString\QueryString;

interface QueryStringRendererInterface
{
    public const DEFAULT_ENCODING = PHP_QUERY_RFC3986;

    /**
     * @return int
     */
    public function getEncoding(): int;

    /**
     * Returns a new instance with the given encoding.
     * Allowed values are constants PHP_QUERY_RFC1738 and PHP_QUERY_RFC3986.
     * An \InvalidArgumentException MUST be thrown otherwise.
     *
     * @param int $encoding
     * @return NativeRenderer
     */
    public function withEncoding(int $encoding): QueryStringRendererInterface;

    /**
     * @return null|string
     */
    public function getSeparator(): ?string;

    /**
     * Returns a new instance with the given separator.
     * Set to null to use php default (ini_get('arg_separator.output')).
     *
     * @param null|string $separator
     * @return QueryString
     */
    public function withSeparator(?string $separator): QueryStringRendererInterface;

    /**
     * Returns the string representation of the query string.
     *
     * @param QueryString $queryString
     * @return string
     */
    public function render(QueryString $queryString): string;
}
