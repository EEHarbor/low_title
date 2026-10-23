<?php

$root = dirname(__DIR__, 2);
$archive = $root . '/low_title/low_title.phar';
$phar = new Phar($archive);
$inventory = array();

/**
 * Inventory functions, methods, closures, and source provenance.
 *
 * @param string $origin
 * @param string $contents
 * @return array
 */
function describeSource($origin, $contents)
{
    $functions = array();
    $tokens = token_get_all($contents);
    foreach ($tokens as $index => $token) {
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }

        $name = '{closure}';
        for ($next = $index + 1; isset($tokens[$next]); $next++) {
            if ($tokens[$next] === '(') {
                break;
            }
            if (is_array($tokens[$next]) && $tokens[$next][0] === T_STRING) {
                $name = $tokens[$next][1];
                break;
            }
        }
        $depth = 0;
        $started = false;
        $endLine = $token[2];
        $executable = true;
        for ($next = $index + 1; isset($tokens[$next]); $next++) {
            $nextToken = $tokens[$next];
            if (is_array($nextToken)) {
                if (in_array($nextToken[0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true)) {
                    $depth++;
                }
                $endLine = $nextToken[2] + substr_count($nextToken[1], "\n");
                continue;
            }
            if (!$started && $nextToken === ';') {
                $executable = false;
                break;
            }
            if ($nextToken === '{') {
                $started = true;
                $depth++;
            }
            if ($nextToken === '}') {
                $depth--;
                if ($started && $depth === 0) {
                    break;
                }
            }
        }
        $functions[] = array('name' => $name, 'line' => $token[2], 'end_line' => $endLine, 'executable' => $executable);
    }

    $proceduralSources = array(
        'low_title/addon.setup.php',
        'low_title/autoload.php',
        'low_title/pi.low_title.php',
        'low_title/views/license.php',
        'low_title/low_title.phar#stub',
        'low_title/low_title.phar/FluxCapacitor/Base/Ft.php',
        'low_title/low_title.phar/FluxCapacitor/Bridge/EE.php',
        'low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php',
        'low_title/low_title.phar/Views/license.php',
        'low_title/low_title.phar/index.php',
    );

    return array('origin' => $origin, 'sha256' => hash('sha256', $contents), 'bytes' => strlen($contents), 'functions' => $functions, 'procedural' => in_array($origin, $proceduralSources, true));
}

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/low_title', FilesystemIterator::SKIP_DOTS)) as $file) {
    if ($file->isLink() || $file->getExtension() !== 'php') {
        continue;
    }
    $relative = substr($file->getPathname(), strlen($root) + 1);
    $inventory[$relative] = describeSource($relative, file_get_contents($file->getPathname()));
}

foreach (new RecursiveIteratorIterator($phar) as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $relative = substr($file->getPathname(), strlen('phar://' . $archive . '/'));
    $contents = $file->getContent();
    $target = $root . '/tests/.source/' . $relative;
    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0777, true);
    }
    file_put_contents($target, $contents);
    if (hash_file('sha256', $target) !== hash('sha256', $contents)) {
        throw new RuntimeException('Measurement provenance mismatch: ' . $relative);
    }
    $origin = 'low_title/low_title.phar/' . $relative;
    $inventory[$origin] = describeSource($origin, $contents);
}

$inventory['low_title/low_title.phar#stub'] = describeSource('low_title/low_title.phar#stub', $phar->getStub());
ksort($inventory);
file_put_contents($root . '/tests/source-inventory.json', json_encode(array('baseline' => 'e3e13533ab0e67d13cdd16bedc0e377fa13910f0', 'archive_sha256' => hash_file('sha256', $archive), 'files' => $inventory), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
printf("Inventoried %d source units, %d functions/methods/closures; archive %s\n", count($inventory), array_sum(array_map(function ($file) { return count($file['functions']); }, $inventory)), hash_file('sha256', $archive));
