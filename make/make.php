<?php declare(strict_types=1);

/**
 * Скрипт для сборки функций в один файл
 */

$options = getopt('', ['config:']);
$configName = $options['config']?? __DIR__ . '/config.php';

$config = require $configName;

$sourceDir = realpath($config['sourceDir']?? __DIR__ . '/../src');
$functions = getAllFunctions($sourceDir);

$map = $config['map']?? null;
if ($map) {
    $mappedFunctions = [];
    foreach ($map as $originalName => $newName) {
        $function = $functions[$originalName]?? null;
        if ($function === null) {
            user_error("Не найдена функция $originalName", E_USER_WARNING);
            continue;
        }

        $mappedFunctions[] = strtr($function, [$originalName => $newName]);
    }
} else {
    $mappedFunctions = array_values($functions);
}

$namespace = $config['namespace']?? 'func_all';

$code = "<?php declare(strict_types=1);\n\n";
if ($namespace) {
    $code .= "namespace $namespace;\n\n";
}
$code .= implode("\n\n", $mappedFunctions);

$outputFile = $config['outputFile']?? 'php://stdout';
file_put_contents($outputFile, $code);

function getAllFunctions(string $dir): array
{
    $functions = [];
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $functions = array_merge($functions, getFunctions(file_get_contents($file)));
    }

    return $functions;
}

function getFunctions(string $code): array
{
    // по-простому
    $delimeter = "/**\n";
    $parts = explode($delimeter, $code);
    array_shift($parts);
    $functions = [];
    foreach ($parts as $part) {
        $matches = [];
        if ( ! preg_match('~^function\s+(?<name>[^(]+)\s*\(~m', $part, $matches)) {
            continue;
        }
        $functions[$matches['name']] = $delimeter . rtrim($part);
    }

    return $functions;
}