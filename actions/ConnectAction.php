<?php

class ConnectAction implements ActionInterface
{

    /**
     * @param int|string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * connect to the target module with the specified sample id or molecule type
     *
     * @var int|string
     */
    public $identifier;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        echo 'CONNECT ' . $this->identifier . ' Just a little more.. ' . PHP_EOL;
    }
}
