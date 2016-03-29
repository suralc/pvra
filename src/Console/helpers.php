<?php
/**
 * helpers.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\Console;

/**
 * @param string $from
 * @param string $to
 * @return string
 * @author Gordon http://stackoverflow.com/users/208809/gordon
 * @see Source http://stackoverflow.com/a/2638272/2912456
 * @license cc-by-sa http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
function makeRelativePath($from, $to)
{
    // some compatibility fixes for Windows paths
    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
    $from = str_replace('\\', '/', $from);
    $to = str_replace('\\', '/', $to);

    // /foo//////bar should be treated the same as /foo/bar
    do {
        $from = str_replace('//', '/', $from, $cFrom);
        $to = str_replace('//', '/', $to, $cTo);
    } while ($cFrom !== 0 && $cTo !== 0);

    $from = explode('/', $from);
    $to = explode('/', $to);
    $relPath = $to;

    foreach ($from as $depth => $dir) {
        // find first non-matching dir
        if ($dir === $to[ $depth ]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if ($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
}

const EXTREME_MIN = 0;
const EXTREME_MAX = 1;

/**
 * Get an extremum value from an array
 *
 * @param array $data List of sortable data
 * @param callable $comparator Method used to compare the data. Should accept two parameters and return an integer > 0 if
 * the first parameter is greater than the second, 0 if they are equals and -1 if it is smaller.
 * @return null|mixed Null of the input array was empty. The greatest value otherwise.
 */
function get_array_extreme(array $data, callable $comparator, $mode)
{
    if (empty($data)) {
        return null;
    }
    $data = array_values($data);
    $composedComparator = function ($a, $b) use ($mode, $comparator) {
        $order = $comparator($a, $b);
        return $mode === EXTREME_MIN ? -$order : $order;
    };
    $extreme = $data[0];
    foreach ($data as $datum) {
        if ($composedComparator($extreme, $datum) < 0) {
            $extreme = $datum;
        }
    }

    return $extreme;
}

/**
 * Get the greatest value from an array
 *
 * @param array $data List of sortable data
 * @param callable $comparator Method used to compare the data. Should accept two parameters and return an integer > 0 if
 * the first parameter is greater than the second, 0 if they are equals and -1 if it is smaller.
 * @return null|mixed Null of the input array was empty. The greatest value otherwise.
 */
function get_array_max_value(array $data, callable $comparator)
{
    return get_array_extreme($data, $comparator, EXTREME_MAX);
}

/**
 * Get the smallest value from an array
 *
 * @param array $data List of sortable data
 * @param callable $comparator Method used to compare the data. Should accept two parameters and return an integer > 0 if
 * the first parameter is greater than the second, 0 if they are equals and -1 if it is smaller.
 * @return null|mixed Null of the input array was empty. The greatest value otherwise.
 */
function get_array_min_value(array $data, callable $comparator)
{
    return get_array_extreme($data, $comparator, EXTREME_MIN);
}