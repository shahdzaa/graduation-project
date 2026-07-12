<?php

$root = dirname(__DIR__);
$controllersPath = $root . '/app/Http/Controllers';
$resourcesPath = $root . '/app/Http/Resources';
$resourceFiles = glob($resourcesPath . '/*.php');
$resources = array_map(function ($file) {
    return basename($file, '.php');
}, $resourceFiles);

$skip = ['Controller.php', 'AIQuizController.php'];
$changes = [];
foreach (glob($controllersPath . '/*.php') as $controllerFile) {
    $filename = basename($controllerFile);
    if (in_array($filename, $skip, true)) {
        continue;
    }

    $controllerName = basename($controllerFile, '.php');
    if (!str_ends_with($controllerName, 'Controller')) {
        continue;
    }

    $modelName = substr($controllerName, 0, -strlen('Controller'));
    $resourceName = $modelName . 'Resource';
    if (!in_array($resourceName, $resources, true)) {
        echo "SKIP {$filename}: no resource {$resourceName}\n";
        continue;
    }

    $text = file_get_contents($controllerFile);
    $original = $text;

    // Add resource import if missing
    $import = "use App\\Http\\Resources\\{$resourceName};";
    if (!str_contains($text, $import)) {
        $lines = preg_split('/\r?\n/', $text);
        $lastUseIndex = -1;
        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), 'use ')) {
                $lastUseIndex = $index;
            }
        }
        if ($lastUseIndex >= 0) {
            array_splice($lines, $lastUseIndex + 1, 0, $import);
            $text = implode("\n", $lines);
        } else {
            $text = preg_replace('/(class ' . preg_quote($controllerName, '/') . ')/', "$import\n\n$1", $text, 1);
        }
    }

    // Singular model variables ending in load() or direct object returns
    $text = preg_replace(
        '/return response\(\)->json\(\s*\$([A-Za-z_]+)->load\((.*?)\)\s*\);/s',
        'return new ' . $resourceName . '($1->load($2));',
        $text
    );

    $text = preg_replace(
        '/return response\(\)->json\(\s*\$' . preg_quote(lcfirst($modelName), '/') . '\s*,\s*201\s*\);/s',
        'return (new ' . $resourceName . '($' . lcfirst($modelName) . '))->response()->setStatusCode(201);',
        $text
    );

    $text = preg_replace(
        '/return response\(\)->json\(\s*\$' . preg_quote(lcfirst($modelName), '/') . '\s*\);/s',
        'return new ' . $resourceName . '($' . lcfirst($modelName) . ');',
        $text
    );

    // Collection returns for direct query builders
    $text = preg_replace(
        '/return response\(\)->json\(\s*' . preg_quote($modelName, '/') . '::with\((.*?)\)->get\(\)\s*\);/s',
        'return ' . $resourceName . '::collection(' . $modelName . '::with($1)->get());',
        $text
    );

    $text = preg_replace(
        '/return response\(\)->json\(\s*' . preg_quote($modelName, '/') . '::get\(\)\s*\);/s',
        'return ' . $resourceName . '::collection(' . $modelName . '::get());',
        $text
    );

    // Collection variables by naming convention
    $text = preg_replace(
        '/return response\(\)->json\(\s*\$([A-Za-z_]+s)\s*\);/s',
        'return ' . $resourceName . '::collection($1);',
        $text
    );

    if ($text !== $original) {
        file_put_contents($controllerFile, $text);
        $changes[] = $filename;
    }
}

echo "Finished patching controllers: " . implode(', ', $changes) . "\n";
