<?php

namespace BenTools\QueryString\Renderer;

use BenTools\QueryString\QueryString;

final class ArrayValuesNormalizerRenderer implements QueryStringRendererInterface
{
    /**
     * @var NativeRenderer
     */
    private $renderer;

    /**
     * ArrayValuesStringifier constructor.
     */
    protected function __construct(QueryStringRendererInterface $renderer = null)
    {
        $this->renderer = $renderer;
    }

    public static function factory(QueryStringRendererInterface $renderer = null)
    {
        return new self($renderer ?? NativeRenderer::factory());
    }

    /**
     * @inheritDoc
     */
    public function render(QueryString $queryString): string
    {
        return preg_replace(
            '/\%5B\d+\%5D/',
            '%5B%5D',
            $this->renderer->render($queryString)
        );
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): int
    {
        return $this->renderer->getEncoding();
    }

    /**
     * @inheritDoc
     */
    public function withEncoding(int $encoding): QueryStringRendererInterface
    {
        return new self($this->renderer->withEncoding($encoding));
    }

    /**
     * @inheritDoc
     */
    public function getSeparator(): ?string
    {
        return $this->renderer->getSeparator();
    }

    /**
     * @inheritDoc
     */
    public function withSeparator(?string $separator): QueryStringRendererInterface
    {
        return new self($this->renderer->withSeparator($separator));
    }
}
