<?php

if (getenv('ENABLE_C4L_AUTOLOAD')) {
    // add some autoloading so that we can use the AI together with the brutaltester
    require_once 'helper/debug.php';
    require_once('helper/AutoLoader.php');
    $autoLoader = new AutoLoader();
    $autoLoader->registerDir(__DIR__);
    $autoLoader->registerDir(__DIR__ . '/actions/');
    $autoLoader->registerDir(__DIR__ . '/entities/');
    $autoLoader->registerDir(__DIR__ . '/helper/');
    $autoLoader->registerDir(__DIR__ . '/modules/');
}

$inputReader   = new InputReader();
$initialConfig = $inputReader->readInitialConfig();

$entityFactory = new StateFactory();
$actionHandler = new ActionHandler();

try {
    while (true) {
        $config = $inputReader->readTurnConfig();
        $state  = $entityFactory->convertConfig($initialConfig, $config);

        $action = $actionHandler->getAction($state);
        $action->execute();
    }
} catch (\Exception $e) {
    debug('Error:');
    debug($e->getMessage());
}
