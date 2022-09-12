<?php

namespace BenTools\QueryString\Renderer;

use BenTools\QueryString\QueryString;

final class NativeRenderer implements QueryStringRendererInterface
{
    use QueryStringRendererTrait;

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
     * @inheritDoc
     */
    public function render(QueryString $queryString): string
    {
        return http_build_query(
            $queryString->getParams(),
            '',
            $this->separator ?? ini_get('arg_separator.output'),
            $this->encoding
        );
    }
}
