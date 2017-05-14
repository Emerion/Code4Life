<?php

class WaitAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        echo 'WAIT' . PHP_EOL;
    }
}
