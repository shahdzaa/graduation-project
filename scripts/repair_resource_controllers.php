<?php
$root = dirname(__DIR__);
$controllersPath = $root . '/app/Http/Controllers';
$skip = ['Controller.php', 'AIQuizController.php'];
$fixed = [];

foreach (glob($controllersPath . '/*.php') as $controllerFile) {
    $name = basename($controllerFile);
    if (in_array($name, $skip, true)) {
        continue;
    }

    $text = file_get_contents($controllerFile);
    $orig = $text;

    // Fix duplicate response calls
    $text = preg_replace(
        '/\)->response\(\);\)->response\(\)(->setStatusCode\(\d+\))?;/',
        ')->response()$1;',
        $text
    );

    // Fix collection resource double response helpers
    $text = preg_replace(
        '/::collection\((.*?)\)->response\(\)->response\(\)(->setStatusCode\(\d+\))?;/',
        '::collection($1)->response()$2;',
        $text
    );

    // Fix missing dollar sign before model variable names inside new Resource(...)
    $text = preg_replace(
        '/new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*([A-Za-z_][A-Za-z0-9_]*)->/s',
        'new $1($$2->',
        $text
    );

    // Fix invalid status code syntax in new Resource($var->load(...))->response()->setStatusCode(201)
    $text = preg_replace(
        '/return\s+\(new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([^\)]+?->load\([^\)]*\))\)->response\(\)->setStatusCode\((\d+)\);/s',
        'return (new $1($2))->response()->setStatusCode($3);',
        $text
    );

    // Fix invalid status code syntax in new Resource($var->load(...), 201);
    $text = preg_replace(
        '/return\s+\(new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([^,]+?->load\([^\)]*\))\s*,\s*201\s*\);/s',
        'return (new $1($2))->response()->setStatusCode(201);',
        $text
    );

    // Fix invalid status code syntax in new Resource($var, 201);
    $text = preg_replace(
        '/return\s+\(new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([^,\)]+?)\s*\)\s*,\s*201\s*\);/s',
        'return (new $1($2))->response()->setStatusCode(201);',
        $text
    );

    // Fix missing response() on singular resource returns
    $text = preg_replace(
        '/return\s+\(new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([^\)]+?)\s*\)\s*\);/s',
        'return (new $1($2))->response();',
        $text
    );

    // Fix missing response() on singular resource returns
    $text = preg_replace(
        '/return\s+\(new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([^\)]+?)\s*\)\s*\);/s',
        'return (new $1($2))->response();',
        $text
    );

    // Fix missing response() on collection resource returns
    $text = preg_replace(
        '/return\s+([A-Za-z_][A-Za-z0-9_]*Resource)::collection\((.*?)\)(?!->response\(\));/s',
        'return $1::collection($2)->response();',
        $text
    );

    if ($text !== $orig) {
        file_put_contents($controllerFile, $text);
        $fixed[] = $name;
    }
}

echo 'Repaired controllers: ' . implode(', ', $fixed) . "\n";
