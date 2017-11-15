<?php

namespace BenTools\QueryString\Renderer;

trait QueryStringRendererTrait
{
    /**
     * @var int
     */
    protected $encoding;

    /**
     * @var string|null
     */
    protected $separator;

    /**
     * @return int
     */
    public function getEncoding(): int
    {
        return $this->encoding;
    }

    /**
     * @param int $encoding
     * @return QueryStringRendererInterface
     * @throws \InvalidArgumentException
     */
    public function withEncoding(int $encoding): QueryStringRendererInterface
    {
        self::validateEncoding($encoding);

        $clone = clone $this;
        $clone->encoding = $encoding;
        return $clone;
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
     * @return QueryStringRendererInterface
     */
    public function withSeparator(?string $separator): QueryStringRendererInterface
    {
        $clone = clone $this;
        $clone->separator = $separator;

        return $clone;
    }

    /**
     * @param int $encoding
     * @throws \InvalidArgumentException
     */
    protected static function validateEncoding(int $encoding): void
    {
        if (!in_array($encoding, [PHP_QUERY_RFC1738, PHP_QUERY_RFC3986])) {
            throw new \InvalidArgumentException("Invalid encoding.");
        }
    }
}
