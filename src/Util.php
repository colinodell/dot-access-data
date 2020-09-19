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

class Util
{
    /**
     * Test if array is an associative array
     *
     * Note that this function will return true if an array is empty. Meaning
     * empty arrays will be treated as if they are associative arrays.
     *
     * @param array<mixed> $arr
     *
     * @psalm-pure
     */
    public static function isAssoc(array $arr): bool
    {
        return ! \count($arr) || \count(\array_filter(\array_keys($arr), 'is_string')) === \count($arr);
    }

    /**
     * Merge contents from one associative array to another
     *
     * @param mixed $to
     * @param mixed $from
     *
     * @return mixed
     *
     * @psalm-pure
     */
    public static function mergeAssocArray($to, $from, bool $clobber = true)
    {
        if (\is_array($from)) {
            foreach ($from as $k => $v) {
                if (! isset($to[$k])) {
                    $to[$k] = $v;
                } else {
                    $to[$k] = self::mergeAssocArray($to[$k], $v, $clobber);
                }
            }

            return $to;
        }

        return $clobber ? $from : $to;
    }
}
