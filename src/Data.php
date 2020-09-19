<?php

declare(strict_types=1);

/*
 * This file is a part of colinodell/dot-access-data.
 *
 * (c) Colin O'Dell
 *
 * Based on dflydev/dot-access-data, (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ColinODell\DotAccessData;

use ColinODell\DotAccessData\Exception\DataException;
use ColinODell\DotAccessData\Exception\InvalidPathException;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class Data implements DataInterface, \ArrayAccess
{
    private const DELIMITERS = ['/', '.'];

    /**
     * Internal representation of data data
     *
     * @var array<string, mixed>
     */
    protected $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function append(string $path, $value = null): void
    {
        $currentValue =&$this->data;
        $keyPath      = self::pathStringToArray($path);
        $endKey       = \array_pop($keyPath);
        foreach ($keyPath as $currentKey) {
            if (! isset($currentValue[$currentKey])) {
                $currentValue[$currentKey] = [];
            }

            $currentValue =&$currentValue[$currentKey];
        }

        if (! isset($currentValue[$endKey])) {
            $currentValue[$endKey] = [];
        }

        if (! \is_array($currentValue[$endKey])) {
            $currentValue[$endKey] = [$currentValue[$endKey]];
        }

        // Promote this key to an array.
        $currentValue[$endKey][] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $path, $value = null): void
    {
        $currentValue =&$this->data;
        $keyPath      = self::pathStringToArray($path);
        $endKey       = \array_pop($keyPath);

        foreach ($keyPath as $currentKey) {
            if (! isset($currentValue[$currentKey])) {
                $currentValue[$currentKey] = [];
            }

            if (! \is_array($currentValue[$currentKey])) {
                throw new DataException(\sprintf('Key path at %s of %s cannot be indexed into (is not an array)', $currentKey, $path));
            }

            $currentValue =&$currentValue[$currentKey];
        }

        $currentValue[$endKey] = $value;
    }

    public function remove(string $path): void
    {
        $currentValue =&$this->data;
        $keyPath      = self::pathStringToArray($path);
        $endKey       = \array_pop($keyPath);
        foreach ($keyPath as $key) {
            if (! isset($currentValue[$key])) {
                return;
            }

            $currentValue =&$currentValue[$key];
        }

        unset($currentValue[$endKey]);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function get(string $path, $default = null)
    {
        /** @psalm-suppress ImpureFunctionCall */
        $hasDefault = \func_num_args() > 1;

        $data = $this->data;

        foreach (self::pathStringToArray($path) as $key) {
            if (! \is_array($data) || ! \array_key_exists($key, $data)) {
                if (! $hasDefault) {
                    throw new InvalidPathException(\sprintf('No data exists at the given path: "%s"', $path));
                }

                return $default;
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * @psalm-mutation-free
     */
    public function has(string $path): bool
    {
        $data = $this->data;

        foreach (self::pathStringToArray($path) as $key) {
            if (! \is_array($data) || ! \array_key_exists($key, $data)) {
                return false;
            }

            $data = $data[$key];
        }

        return true;
    }

    /**
     * @psalm-mutation-free
     */
    public function getData(string $path): DataInterface
    {
        $value = $this->get($path);

        if (\is_array($value) && Util::isAssoc($value)) {
            return new Data($value);
        }

        throw new DataException(\sprintf('Value at "%s" could not be represented as a DataInterface', $path));
    }

    /**
     * {@inheritDoc}
     */
    public function import(array $data, bool $clobber = true): void
    {
        $this->data = Util::mergeAssocArray($this->data, $data, $clobber);
    }

    public function importData(DataInterface $data, bool $clobber = true): void
    {
        $this->import($data->export(), $clobber);
    }

    /**
     * {@inheritDoc}
     */
    public function export(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function offsetGet($key)
    {
        return $this->get($key, null);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($key, $value)
    {
        if (! \is_string($key)) {
            throw new InvalidPathException('Path must be a string');
        }

        $this->set($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * @return string[]
     *
     * @psalm-return non-empty-list<string>
     *
     * @psalm-pure
     */
    protected static function pathStringToArray(string $path): array
    {
        if (\strlen($path) === 0) {
            throw new InvalidPathException('Path cannot be an empty string');
        }

        $path = \str_replace(self::DELIMITERS, '/', $path);

        return \explode('/', $path);
    }
}
