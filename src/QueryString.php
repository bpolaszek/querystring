<?php

namespace BenTools\QueryString;

use BenTools\QueryString\Parser\NativeParser;
use BenTools\QueryString\Parser\QueryStringParserInterface;
use BenTools\QueryString\Renderer\NativeRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;
use Traversable;

final class QueryString
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @var QueryStringRendererInterface
     */
    private $renderer;

    /**
     * @var QueryStringRendererInterface
     */
    private static $defaultRenderer;

    /**
     * @var QueryStringParserInterface
     */
    private static $defaultParser;

    /**
     * QueryString constructor.
     * @param array|null                       $params
     * @throws \InvalidArgumentException
     */
    protected function __construct(?array $params = [])
    {
        $params = $params ?? [];
        foreach ($params as $key => $value) {
            $this->params[(string) $key] = $value;
        }
        $this->renderer = self::getDefaultRenderer();
    }

    /**
     * @param array $params
     * @return QueryString
     */
    private static function createFromParams(array $params): self
    {
        return new self($params);
    }

    /**
     * @param \Psr\Http\Message\UriInterface  $uri
     * @param QueryStringParserInterface $queryStringParser
     * @return QueryString
     */
    private static function createFromUri($uri, QueryStringParserInterface $queryStringParser): self
    {
        return self::createFromString($uri->getQuery(), $queryStringParser);
    }

    /**
     * @param string                     $string
     * @param QueryStringParserInterface $queryStringParser
     * @return QueryString
     */
    private static function createFromString(string $string, QueryStringParserInterface $queryStringParser): self
    {
        return new self($queryStringParser->parse($string));
    }

    /**
     * @param QueryStringParserInterface|null $queryStringParser
     * @return QueryString
     * @throws \RuntimeException
     */
    public static function createFromCurrentLocation(QueryStringParserInterface $queryStringParser = null): self
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            throw new \RuntimeException('$_SERVER[\'REQUEST_URI\'] has not been set.');
        }
        return self::createFromString($_SERVER['REQUEST_URI'], $queryStringParser ?? self::getDefaultParser());
    }

    /**
     * @return QueryString
     * @throws \RuntimeException
     */
    public function withCurrentLocation(): self
    {
        return self::createFromCurrentLocation();
    }

    /**
     * @param                                 $input
     * @param QueryStringParserInterface|null $queryStringParser
     * @return QueryString
     * @throws \InvalidArgumentException
     * @throws \TypeError
     */
    public static function factory($input = null, QueryStringParserInterface $queryStringParser = null): self
    {
        if (is_array($input)) {
            return self::createFromParams($input);
        } elseif (null === $input) {
            return self::createFromParams([]);
        } elseif (is_a($input, 'Psr\Http\Message\UriInterface')) {
            return self::createFromUri($input, $queryStringParser ?? self::getDefaultParser());
        } elseif (is_string($input)) {
            return self::createFromString($input, $queryStringParser ?? self::getDefaultParser());
        }
        throw new \InvalidArgumentException(sprintf('Expected array, string or Psr\Http\Message\UriInterface, got %s', is_object($input) ? get_class($input) : gettype($input)));
    }

    /**
     * @return array
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param string $key
     * @param array  ...$deepKeys
     * @return mixed|null
     */
    public function getParam(string $key, ...$deepKeys)
    {
        $param = $this->params[$key] ?? null;
        foreach ($deepKeys as $key) {
            if (!isset($param[$key])) {
                return null;
            }
            $param = $param[$key];
        }
        return $param;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key, ...$deepKeys): bool
    {
        return [] === $deepKeys ? array_key_exists($key, $this->params) : null !== $this->getParam($key, ...$deepKeys);
    }

    /**
     * Yield key => value pairs.
     *
     * @param bool $decodeKeys
     * @param bool $decodeValues
     * @return Traversable
     */
    public function getPairs(bool $decodeKeys = false, bool $decodeValues = false): Traversable
    {
        return new Pairs((string) $this, $decodeKeys, $decodeValues, $this->getRenderer()->getSeparator());
    }

    /**
     * @param string $key
     * @param        $value
     * @return QueryString
     */
    public function withParam(string $key, $value): self
    {
        $clone = clone $this;
        $clone->params[$key] = $value;
        return $clone;
    }

    /**
     * @param array $params
     * @return QueryString
     */
    public function withParams(array $params): self
    {
        $clone = clone $this;
        $clone->params = [];
        foreach ($params as $key => $value) {
            $clone->params[(string) $key] = $value;
        }
        return $clone;
    }

    /**
     * @param string $key
     * @param array  ...$deepKeys
     * @return QueryString
     */
    public function withoutParam(string $key, ...$deepKeys): self
    {
        $clone = clone $this;

        // $key does not exist
        if (!isset($clone->params[$key])) {
            return $clone;
        }

        // $key exists and there are no $deepKeys
        if ([] === $deepKeys) {
            unset($clone->params[$key]);
            return $clone;
        }

        // Deepkeys
        $clone->params[$key] = $this->removeFromPath($clone->params[$key], ...$deepKeys);
        return $clone;
    }

    /**
     * @return QueryStringRendererInterface
     */
    public function getRenderer(): QueryStringRendererInterface
    {
        return $this->renderer;
    }

    /**
     * @param QueryStringRendererInterface $renderer
     * @return QueryString
     */
    public function withRenderer(QueryStringRendererInterface $renderer): self
    {
        $clone = clone $this;
        $clone->renderer = $renderer;
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->renderer->render($this);
    }

    /**
     * @param array $array
     * @return bool
     */
    private function isAnIndexedArray(array $array): bool
    {
        $keys = array_keys($array);
        return $keys === array_filter($keys, 'is_int');
    }

    /**
     * @param array $params
     * @param array ...$keys
     * @return array
     */
    private function removeFromPath(array $params, ...$keys): array
    {
        $nbKeys = count($keys);
        $lastIndex = $nbKeys - 1;
        $cursor = &$params;

        foreach ($keys as $k => $key) {
            if (!isset($cursor[$key])) {
                return $params; // End here if not found
            }

            if ($k === $lastIndex) {
                unset($cursor[$key]);
                if (is_array($cursor) && $this->isAnIndexedArray($cursor)) {
                    $cursor = array_values($cursor);
                }
                break;
            }

            $cursor = &$cursor[$key];
        }

        return $params;
    }

    /**
     * Returns the default renderer.
     *
     * @return QueryStringRendererInterface
     */
    public static function getDefaultRenderer(): QueryStringRendererInterface
    {
        if (!isset(self::$defaultRenderer)) {
            self::restoreDefaultRenderer();
        }
        return self::$defaultRenderer;
    }

    /**
     * Changes default renderer.
     *
     * @param QueryStringRendererInterface $defaultRenderer
     */
    public static function setDefaultRenderer(QueryStringRendererInterface $defaultRenderer): void
    {
        self::$defaultRenderer = $defaultRenderer;
    }

    /**
     * Restores the default renderer.
     */
    public static function restoreDefaultRenderer(): void
    {
        self::$defaultRenderer = NativeRenderer::factory();
    }

    /**
     * Returns the default parser.
     *
     * @return QueryStringParserInterface
     */
    public static function getDefaultParser(): QueryStringParserInterface
    {
        if (!isset(self::$defaultParser)) {
            self::restoreDefaultParser();
        }
        return self::$defaultParser;
    }

    /**
     * Changes default parser.
     *
     * @param QueryStringParserInterface $defaultParser
     */
    public static function setDefaultParser(QueryStringParserInterface $defaultParser): void
    {
        self::$defaultParser = $defaultParser;
    }

    /**
     * Restores the default parser.
     */
    public static function restoreDefaultParser(): void
    {
        self::$defaultParser = new NativeParser();
    }
}
