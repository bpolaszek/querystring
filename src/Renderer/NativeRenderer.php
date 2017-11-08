<?php

namespace BenTools\QueryString\Renderer;

use BenTools\QueryString\QueryString;

final class NativeRenderer implements QueryStringRendererInterface
{
    /**
     * @var int
     */
    private $encoding;

    /**
     * @var string|null
     */
    private $separator;

    /**
     * NativeRenderer constructor.
     * @param int $encoding
     */
    protected function __construct(int $encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @param int $encoding
     * @return NativeRenderer
     * @throws \InvalidArgumentException
     */
    public static function factory(int $encoding = self::DEFAULT_ENCODING): self
    {
        self::validateEncoding($encoding);

        return new self($encoding);
    }

    /**
     * @return int
     */
    public function getEncoding(): int
    {
        return $this->encoding;
    }

    /**
     * @param int $encoding
     * @return NativeRenderer
     */
    public function withEncoding(int $encoding): QueryStringRendererInterface
    {
        self::validateEncoding($encoding);

        return new self($encoding);
    }

    /**
     * @return null|string
     */
    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    /**
     * @param null|string $separator
     * @return QueryString
     */
    public function withSeparator(?string $separator): QueryStringRendererInterface
    {
        $clone = clone $this;
        $clone->separator = $separator;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function render(QueryString $queryString): string
    {
        return http_build_query(
            $queryString->getParams(),
            null,
            $this->separator ?? ini_get('arg_separator.output'),
            $this->encoding
        );
    }

    /**
     * @param int $encoding
     * @throws \InvalidArgumentException
     */
    private static function validateEncoding(int $encoding): void
    {
        if (!in_array($encoding, [PHP_QUERY_RFC1738, PHP_QUERY_RFC3986])) {
            throw new \InvalidArgumentException("Invalid encoding.");
        }
    }
}
