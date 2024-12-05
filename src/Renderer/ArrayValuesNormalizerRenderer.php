<?php

namespace BenTools\QueryString\Renderer;

use BenTools\QueryString\Pairs;
use BenTools\QueryString\QueryString;
use IteratorIterator;

final class ArrayValuesNormalizerRenderer implements QueryStringRendererInterface
{
    /**
     * @var NativeRenderer
     */
    private $renderer;

    /**
     * ArrayValuesStringifier constructor.
     */
    protected function __construct(?QueryStringRendererInterface $renderer = null)
    {
        $this->renderer = $renderer;
    }

    public static function factory(?QueryStringRendererInterface $renderer = null)
    {
        return new self($renderer ?? NativeRenderer::factory());
    }

    /**
     * @inheritDoc
     */
    public function render(QueryString $queryString): string
    {
        $separator = $this->getSeparator() ?? ini_get('arg_separator.output');
        $input = $this->renderer->render($queryString);
        $output = '';

        $iterator = new IteratorIterator(new Pairs($input, false, false, $separator));
        $iterator->rewind();
        while (true === $iterator->valid()) {
            $key = $iterator->key();
            $value = $iterator->current();
            $iterator->next();
            $output .= sprintf(
                '%s=%s',
                preg_replace('/\%5B\d+\%5D/', '%5B%5D', $key),
                $value
            );
            if (false !== $iterator->valid()) {
                $output .= $separator;
            }
        }

        return $output;
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
