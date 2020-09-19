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

interface DataInterface
{
    /**
     * Append a value to a path (assumes path refers to an array value)
     *
     * If the path does not yet exist it will be created.
     * If the path does not reference an array it's existing contents will be placed into one first.
     *
     * @param mixed $value
     *
     * @throws InvalidPathException if the given path is empty
     */
    public function append(string $path, $value = null): void;

    /**
     * Set a value for a path
     *
     * If the path does not yet exist it will be created.
     * If the path exists but is not an array, and exception will be thrown.
     *
     * @param mixed $value
     *
     * @throws InvalidPathException if the given path is empty
     * @throws DataException if the given path does not target an array
     */
    public function set(string $path, $value = null): void;

    /**
     * Remove a path
     *
     * No exception will be thrown if the path does not exist
     *
     * @throws InvalidPathException if the given path is empty
     */
    public function remove(string $path): void;

    /**
     * Get the raw value for a path, or a default value
     *
     * If no default is provided then an exception will be thrown if the path does not exist
     *
     * @param mixed $default The default value to return
     *
     * @return mixed
     *
     * @throws InvalidPathException if the given path is empty
     * @throws InvalidPathException if the given path does not exist and no default value was given
     *
     * @psalm-mutation-free
     */
    public function get(string $path, $default = null);

    /**
     * Check if the path exists
     *
     * @throws InvalidPathException if the given path is empty
     *
     * @psalm-mutation-free
     */
    public function has(string $path): bool;

    /**
     * Get a data instance for a path
     *
     * @throws InvalidPathException if the given path is empty
     * @throws DataException if the given path does not reference an array
     *
     * @psalm-mutation-free
     */
    public function getData(string $path): DataInterface;

    /**
     * Import data into existing data
     *
     * @param array<string, mixed> $data
     */
    public function import(array $data, bool $clobber = true): void;

    /**
     * Import data from an external data into existing data
     */
    public function importData(DataInterface $data, bool $clobber = true): void;

    /**
     * Export data as raw data
     *
     * @return array<string, mixed>
     *
     * @psalm-mutation-free
     */
    public function export(): array;
}
