<?php declare(strict_types=1);

namespace func_all;

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