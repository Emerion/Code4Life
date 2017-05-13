<?php

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
    fwrite(STDERR, 'Error:' . $e->getMessage());
}
