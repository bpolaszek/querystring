<?php

namespace BenTools\QueryString;

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
     * QueryString constructor.
     * @param array|null                       $params
     * @param QueryStringRendererInterface|null $renderer
     * @throws \InvalidArgumentException
     */
    protected function __construct(?array $params = [], QueryStringRendererInterface $renderer = null)
    {
        $params = $params ?? [];
        foreach ($params as $key => $value) {
            $this->params[(string) $key] = $value;
        }
        $this->renderer = $renderer ?? self::getDefaultRenderer();
    }

    /**
     * @param array $params
     * @param QueryStringRendererInterface|null $renderer
     * @return QueryString
     */
    private static function createFromParams(array $params, QueryStringRendererInterface $renderer = null): self
    {
        return new self($params, $renderer);
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param QueryStringRendererInterface|null $renderer
     * @return QueryString
     * @throws \TypeError
     */
    private static function createFromUri($uri, QueryStringRendererInterface $renderer = null): self
    {
        $qs = $uri->getQuery();
        $params = [];
        parse_str($qs, $params);
        return new self($params, $renderer);
    }

    /**
     * @param string $qs
     * @param QueryStringRendererInterface|null $renderer
     * @return QueryString
     */
    private static function createFromString(string $qs, QueryStringRendererInterface $renderer = null): self
    {
        $params = [];
        parse_str(ltrim($qs, '?'), $params);
        return new self($params, $renderer);
    }

    /**
     * @param QueryStringRendererInterface|null $renderer
     * @return QueryString
     * @throws \RuntimeException
     */
    public static function createFromCurrentLocation(QueryStringRendererInterface $renderer = null): self
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            throw new \RuntimeException('$_SERVER[\'REQUEST_URI\'] has not been set.');
        }
        return self::createFromString($_SERVER['REQUEST_URI'], $renderer);
    }

    /**
     * @return QueryString
     * @throws \RuntimeException
     */
    public function withCurrentLocation(): self
    {
        return self::createFromCurrentLocation($this->renderer);
    }

    /**
     * @param          $input
     * @return QueryString
     * @throws \InvalidArgumentException
     */
    public static function factory($input = null, QueryStringRendererInterface $renderer = null): self
    {
        if (is_array($input)) {
            return self::createFromParams($input, $renderer);
        } elseif (is_a($input, 'Psr\Http\Message\UriInterface')) {
            return self::createFromUri($input, $renderer);
        } elseif (is_string($input)) {
            return self::createFromString($input, $renderer);
        } elseif (null === $input) {
            return self::createFromParams([], $renderer);
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
        return new Pairs($this, $decodeKeys, $decodeValues);
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
}
