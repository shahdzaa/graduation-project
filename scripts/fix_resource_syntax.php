<?php
$root = dirname(__DIR__);
$controllersPath = $root . '/app/Http/Controllers';
$skip = ['Controller.php', 'AIQuizController.php'];
foreach (glob($controllersPath . '/*.php') as $controllerFile) {
    $name = basename($controllerFile);
    if (in_array($name, $skip, true)) {
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

    // Fix missing $ in collection returns and ensure response()
    $text = preg_replace_callback(
        '/return\s+([A-Za-z_][A-Za-z0-9_]*Resource)::collection\(\s*([A-Za-z_][A-Za-z0-9_]*)\s*\)\s*;/m',
        function ($m) {
            $var = str_starts_with($m[2], '$') ? $m[2] : '$' . $m[2];
            return 'return ' . $m[1] . '::collection(' . $var . ')->response();';
        },
        $text
    );

    // Fix broken new Resource($var, 201) missing extra parenthesis
    $text = preg_replace_callback(
        '/return\s+\(?new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([A-Za-z_][A-Za-z0-9_]*)(->load\((.*?)\))?\s*\)\s*,\s*201\s*;/m',
        function ($m) {
            $var = '$' . $m[2];
            $load = isset($m[3]) ? $m[3] : '';
            return 'return (new ' . $m[1] . '(' . $var . $load . '))->response()->setStatusCode(201);';
        },
        $text
    );

    // Fix new Resource(variable) missing $ and ensure response()
    $text = preg_replace_callback(
        '/return\s+\(?new\s+([A-Za-z_][A-Za-z0-9_]*Resource)\(\s*\$?([A-Za-z_][A-Za-z0-9_]*)(->load\((.*?)\))?\s*\)\s*;?/m',
        function ($m) {
            $var = '$' . $m[2];
            $load = isset($m[3]) ? $m[3] : '';
            return 'return (new ' . $m[1] . '(' . $var . $load . '))->response();';
        },
        $text
    );

    // Fix bad response()->json(...))->response() patterns
    $text = preg_replace_callback(
        '/return\s+response\(\)->json\(\s*\$?([A-Za-z_][A-Za-z0-9_]*)->load\((.*?)\)\s*\)\)\s*->response\(\)\s*;/m',
        function ($m) use ($resourceName) {
            return 'return (new ' . $resourceName . '($' . $m[1] . '->load(' . $m[2] . ')))->response();';
        },
        $text
    );

    // Fix response()->json($var, 201) to resource response
    $text = preg_replace_callback(
        '/return\s+response\(\)->json\(\s*\$?([A-Za-z_][A-Za-z0-9_]*)\s*,\s*201\s*\)\s*;/m',
        function ($m) use ($resourceName) {
            return 'return (new ' . $resourceName . '($' . $m[1] . '))->response()->setStatusCode(201);';
        },
        $text
    );

    // Fix response()->json($var->load(...)) patterns
    $text = preg_replace_callback(
        '/return\s+response\(\)->json\(\s*\$?([A-Za-z_][A-Za-z0-9_]*)->load\((.*?)\)\s*\)\s*;/m',
        function ($m) use ($resourceName) {
            return 'return (new ' . $resourceName . '($' . $m[1] . '->load(' . $m[2] . ')))->response();';
        },
        $text
    );

    if ($text !== $orig) {
        file_put_contents($controllerFile, $text);
        echo "patched {$name}\n";
    }
}
