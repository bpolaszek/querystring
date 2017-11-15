<?php

namespace BenTools\QueryString;

use BenTools\QueryString\Parser\QueryStringParserInterface;
use BenTools\QueryString\Renderer\ArrayValuesNormalizerRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;

/**
 * @param $input
 * @return QueryString
 * @throws \InvalidArgumentException
 */
function query_string($input = null, QueryStringParserInterface $queryStringParser = null): QueryString
{
    return QueryString::factory($input, $queryStringParser);
}

/**
 * @param QueryStringRendererInterface|null $renderer
 * @return ArrayValuesNormalizerRenderer
 */
function withoutNumericIndices(QueryStringRendererInterface $renderer = null): ArrayValuesNormalizerRenderer
{
    return ArrayValuesNormalizerRenderer::factory($renderer);
}

/**
 * @param string      $queryString
 * @param bool        $decodeKeys
 * @param bool        $decodeValues
 * @param string|null $separator
 * @return Pairs
 */
function pairs(string $queryString, bool $decodeKeys = false, bool $decodeValues = false, string $separator = null): Pairs
{
    return new Pairs($queryString, $decodeKeys, $decodeValues, $separator);
}
