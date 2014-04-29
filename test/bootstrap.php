<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('rg\\typewriter\\', __DIR__);

require __DIR__ . '/rg/typewriter/stub/classresolver/ClassReferencesClassToResolve_NoNamespace.php';
