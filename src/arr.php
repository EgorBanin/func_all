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

/**
 * Получение одномерного массива из многомерного
 * @param array $arr
 * @param bool $useKeys сохранять ключи (для одинаковых ключей значения перезапишутся)
 * @return array
 */
function arr_flatten(array $arr, bool $useKeys = false): array
{
    $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arr));

    return iterator_to_array($it, $useKeys);
}