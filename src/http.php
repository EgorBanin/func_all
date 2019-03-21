<?php declare(strict_types=1);

namespace func_all;

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