<?php

$inputReader = new InputReader();
$inputReader->readInitialConfig();

$entityFactory = new StateFactory();
$actionHandler = new ActionHandler();

try {
    while (true) {
        $config = $inputReader->readTurnConfig();
        $state  = $entityFactory->convertConfig($config);

        // @todo simulate different actions and execute the most valuable one
        $actionHandler->executeAction($state);
    }
} catch (\Exception $e) {
    fwrite(STDERR, 'Error:' . $e->getMessage());
}
