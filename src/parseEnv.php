<?php
/*
 *  Author: Aaron Sollman
 *  Email:  unclepong@gmail.com
 *  Date:   11/17/25
 *  Time:   19:36
*/


/**
 * Parse an .env file and merge it into the `$_ENV` superglobal
 * @param string $path
 * @param bool $verbose
 * @return void
 */
function parsenv(
    string $path = '.env',
    bool   $verbose = false,
): void
{

    $resolvedPath = realpath($path);
    if ($resolvedPath === false) {
        $verbose && fwrite(STDERR, 'invalid or malformed path provided to parsenv');
        return;
    }
    if (!file_exists($resolvedPath)) {
        $verbose && fwrite(STDERR, 'invalid path provided to parsenv');
    }
    $parsed = parse_ini_file($resolvedPath, false, INI_SCANNER_TYPED) ?: [];
    if (PHP_SAPI == 'cli') {
        define("ENV", $parsed);
    } else {
        foreach ($parsed as $key => $value) {
            $_ENV[$key] = $value;
        }
    }
    define("PARSENV_LOADED", true);
}

/**
 * return all `$_ENV` vars beginning with the specified prefix
 * @param string $p
 * @return array
 */
function get_env_prefix(string $p, bool $removePrefix = true, bool $lcase = true): array
{
    $inputArray = PHP_SAPI == 'cli' ? ENV : $_ENV;
    $outputArray = array_filter($inputArray, function ($k) use ($p) {
        return str_starts_with($k, $p);
    }, ARRAY_FILTER_USE_KEY);
    if ($removePrefix) {
        $prefixLen = strlen($p);
        $filterArray = [];
        foreach ($outputArray as $key => $value) {
            $key = $lcase ? strtolower($key) : $key;
            $filterArray[substr($key, $prefixLen)] = $value;
        }
        return $filterArray;
    } else {
        return $outputArray;
    }
}

/**
 * Indicate that a particular prefix is present in a key found in `$_ENV`
 * @param string $p
 * @return bool
 */
function env_has_prefix(string $p): bool
{
    if (count(get_env_prefix($p)) != 0) return true;
    return false;
}

/**
 * Return an $_ENV value
 * @param string $k
 * @return string
 */
function env(string $k, mixed $default=''): string
{
    if(php_sapi_name() == 'cli') {
        return (ENV[$k] ?? $default);
    }else {
        return ($_ENV[$k] ?? $default);
    }
}

