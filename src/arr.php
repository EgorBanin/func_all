<?php declare(strict_types=1);

namespace func_all;

/**
 * Пользовательский поиск по массиву
 * @param array $array
 * @param callable $func
 * @return mixed ключ найденого значения или false, если значение не найдено
 */
function arr_usearch(array $array, callable $func)
{
    $result = false;
    foreach ($array as $k => $v) {
        if (call_user_func($func, $k, $v)) {
            $result = $k;
            break;
        }
    }
    return $result;
}