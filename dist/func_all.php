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
function obj_init(object $obj, array $properties): object
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
    return $obj;
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

/**
 * Выполнить HTTP-запрос
 * Для работы функции необходима включённая директива allow_url_fopen.
 * @param string $method
 * @param string $url
 * @param array $headers массив строк-заголовков
 * @param string $body
 * @param array $options опции контекста
 * @throws \Exception
 * @return array list($code, $headers, $body) = http_request(...);
 */
function http_request(string $method, string $url, array $headers, $body, array $options = []): array
{
    $options['http'] = ($options['http'] ?? []) + [
            'method' => $method,
            'header' => $headers,
            'content' => $body,
            'max_redirects' => 0,
            'ignore_errors' => 1,
        ];
    $context = stream_context_create($options);
    set_error_handler(function ($code, $message, $file, $line) use ($method, $url) {
        restore_error_handler();
        throw new \Exception(
            'Не удалось выполнить запрос ' . $method . ' ' . $url,
            1,
            new \ErrorException($message, $code, $code, $file, $line)
        );
    });
    $stream = fopen($url, 'r', false, $context);
    restore_error_handler();
    $meta = stream_get_meta_data($stream);
    $responseHeaders = isset($meta['wrapper_data']) ? $meta['wrapper_data'] : [];
    $responseBody = stream_get_contents($stream);
    fclose($stream);
    $responseStatus = [];
    if (preg_match(
            '/^(?<protocol>https?\/[0-9\.]+)\s+(?<code>\d+)(?:\s+(?<comment>\S.*))?$/i',
            trim(reset($responseHeaders)),
            $responseStatus
        ) !== 1) {
        throw new \Exception('Не удалось распарсить статус ответа');
    }

    return [
        0 => $responseStatus['code'],
        'code' => $responseStatus['code'],
        1 => $responseHeaders,
        'headers' => $responseHeaders,
        2 => $responseBody,
        'body' => $responseBody,
    ];
}

/**
 * Закодировать параметры multipart-запроса
 * @param array $params массив из двух элеметов: заголовки и значение
 * @param string $boundary
 * @return string
 */
function http_multipart_encode(array $params, string $boundary): string
{
    $eol = "\r\n";
    $result = '';
    foreach ($params as list($headers, $value)) {
        $result .= '--' . $boundary . $eol;
        $result .= implode($eol, $headers) . $eol;
        $result .= $eol;
        $result .= $value . $eol;
    }
    $result .= '--' . $boundary . '--' . $eol;
    $result .= $eol;

    return $result;
}

/**
 * Сгенерировать границу, не встречающауюся в значениях multipart-запроса
 * @param array $values
 * @param int $length
 * @param int $tryLimit
 * @return string
 * @throws \Exception
 */
function http_multipart_boundary(array $values, int $length = 20, int $tryLimit = 5): string
{
    do {
        --$tryLimit;
        try {
            $boundary = bin2hex(random_bytes($length));
        } catch (\Exception $e) {
            throw new \Exception('Не удалось сгенерировать значение boundary', 0, $e);
        }
        foreach ($values as $value) {
            if (strpos($value, $boundary) !== false) {
                $boundary = null;
                break;
            }
        }
    } while ($boundary === null && $tryLimit > 0);

    if ($boundary === null) {
        throw new \Exception('Не удалось сгенерировать значение boundary');
    }

    return $boundary;
}