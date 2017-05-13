<?php

class PickupMoleculeAction implements ActionInterface
{

    /**
     * @param Player $player
     * @param string $molecule
     */
    public function __construct($player, $molecule)
    {
        $this->player   = $player;
        $this->molecule = $molecule;
    }

    /**
     * @var Player
     */
    private $player;

    /**
     * @var int
     */
    private $molecule;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->player->location !== GotoAction::MODULE_MOLECULES) {
            $subAction = new GotoAction(GotoAction::MODULE_MOLECULES);
        } else {
            $subAction = new ConnectAction($this->molecule);
        }

        $subAction->execute();
    }
}
