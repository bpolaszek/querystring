<?php

namespace BenTools\QueryString;

use BenTools\QueryString\Renderer\ArrayValuesNormalizerRenderer;
use BenTools\QueryString\Renderer\NativeRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;

/**
 * @param                                  $input
 * @param QueryStringRendererInterface|null $renderer
 * @return QueryString
 * @throws \InvalidArgumentException
 */
function query_string($input = null, QueryStringRendererInterface $renderer = null): QueryString
{
    return QueryString::factory($input, $renderer);
}

/**
 * @param int $encoding
 * @return NativeRenderer
 * @throws \InvalidArgumentException
 */
function native(int $encoding = QueryStringRendererInterface::DEFAULT_ENCODING): NativeRenderer
{
    return NativeRenderer::factory($encoding);
}

/**
 * @param QueryStringRendererInterface|null $renderer
 * @return ArrayValuesNormalizerRenderer
 */
function withoutNumericIndices(QueryStringRendererInterface $renderer = null): ArrayValuesNormalizerRenderer
{
    return ArrayValuesNormalizerRenderer::factory($renderer);
}
