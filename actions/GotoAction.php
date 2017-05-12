<?php

class GotoAction implements ActionInterface
{
    // Collect sample data files from the cloud at the DIAGNOSIS module.
    // Gather required molecules for the medicines at the MOLECULES module.
    // Produce the medicines at the LABORATORY module and collect your health points.
    const MODULE_DIAGNOSIS  = 'DIAGNOSIS';
    const MODULE_MOLECULES  = 'MOLECULES';
    const MODULE_LABORATORY = 'LABORATORY';

    /**
     * @param string $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * @var string
     */
    public $module;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        echo 'GOTO ' . $this->module . PHP_EOL;
    }
}
