<?php
$root = dirname(__DIR__);
$controllersPath = $root . '/app/Http/Controllers';
foreach (glob($controllersPath . '/*.php') as $controllerFile) {
    $name = basename($controllerFile);
    if (in_array($name, ['Controller.php', 'AIQuizController.php'], true)) {
        continue;
    }
    $controllerName = basename($controllerFile, '.php');
    if (!str_ends_with($controllerName, 'Controller')) {
        continue;
    }
    $modelName = substr($controllerName, 0, -strlen('Controller'));
    $resourceName = $modelName . 'Resource';
    $text = file_get_contents($controllerFile);
    $orig = $text;

    // Fix collection calls missing $ for lower-case variable names
    $text = preg_replace_callback(
        '/return\s+([A-Za-z_][A-Za-z0-9_]*Resource::collection)\(\s*([a-z][A-Za-z0-9_]*)\s*\)\s*;/s',
        function ($m) {
            return 'return ' . $m[1] . '($' . $m[2] . ')->response();';
        },
        $text
    );

    // Fix new Resource(thing->load(...), 201) and simple new Resource(thing->load(...))
    $text = preg_replace_callback(
        '/return\s+new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*([a-z][A-Za-z0-9_]*)->load\((.*?)\)\s*\)\s*,\s*201\s*;/s',
        function ($m) {
            return 'return (new ' . $m[1] . '($' . $m[2] . '->load(' . $m[3] . ')))->response()->setStatusCode(201);';
        },
        $text
    );
    $text = preg_replace_callback(
        '/return\s+new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*([a-z][A-Za-z0-9_]*)->load\((.*?)\)\s*\)\s*;/s',
        function ($m) {
            return 'return (new ' . $m[1] . '($' . $m[2] . '->load(' . $m[3] . ')))->response();';
        },
        $text
    );

    // Fix new Resource(variable, 201) and new Resource(variable)
    $text = preg_replace_callback(
        '/return\s+new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*([a-z][A-Za-z0-9_]*)\s*\)\s*,\s*201\s*;/s',
        function ($m) {
            return 'return (new ' . $m[1] . '($' . $m[2] . '))->response()->setStatusCode(201);';
        },
        $text
    );
    $text = preg_replace_callback(
        '/return\s+new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*([a-z][A-Za-z0-9_]*)\s*\)\s*;/s',
        function ($m) {
            return 'return (new ' . $m[1] . '($' . $m[2] . '))->response();';
        },
        $text
    );

    // Fix response()->json($var, 201) where variable matches controller resource singular name
    $singular = lcfirst($modelName);
    $text = preg_replace(
        '/return\s+response\(\)->json\(\s*\$' . preg_quote($singular, '/') . '\s*,\s*201\s*\)\s*;/s',
        'return (new ' . $resourceName . '($' . $singular . '))->response()->setStatusCode(201);',
        $text
    );

    // Fix response()->json($var->load(...)) patterns
    $text = preg_replace(
        '/return\s+response\(\)->json\(\s*\$([a-z][A-Za-z0-9_]*)->load\((.*?)\)\s*\)\s*;/s',
        'return (new ' . $resourceName . '($$1->load($2)))->response();',
        $text
    );

    if ($text !== $orig) {
        file_put_contents($controllerFile, $text);
        echo "patched {$name}\n";
    }
}
