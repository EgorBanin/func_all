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
 * Подключение файла с буферизацией вывода
 * @param string $file
 * @param array $params
 * @return string
 */
function ob_include(): string
{
    extract(func_get_arg(1));
    ob_start();
    require func_get_arg(0);
    return ob_get_clean();
}
/**
 * Установить свойства объекта
 * @param object $obj
 * @param array $properties
 */
function obj_init($obj, array $properties): void
{
    $func = function ($properties) {
        foreach (get_object_vars($this) as $name => $val) {
            if (array_key_exists($name, $properties)) {
                $this->{$name} = $properties[$name];
            }
        }
    };
    $closure = $func->bindTo($obj, $obj);
    $closure($properties);
}


/**
 * Преобразовать объект в массив
 * Работает аналогично get_object_vars,
 * но получает доступ к защищённым и приватным свойствам.
 * @param object $obj
 * @return array
 */
function obj_to_array($obj): array
{
    $arr = [];
    $func = function (&$properties) {
        $properties = get_object_vars($this);
    };
    $closure = $func->bindTo($obj, $obj);
    $closure($arr);
    return $arr;
}
/**
 * Заменить вхождения строки '{varName}' на соответствующее значение из массива
 * @param string $template
 * @param array $vars
 * @return string
 */
function str_template(string $template, array $vars): string
{
    $replaces = [];
    foreach ($vars as $name => $value) {
        $replaces['{' . $name . '}'] = $value;
    }
    return strtr($template, $replaces);
}