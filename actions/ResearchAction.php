<?php

class ResearchAction implements ActionInterface
{

    /**
     * @param Player $player
     * @param string $sampleId
     */
    public function __construct($player, $sampleId)
    {
        $this->player   = $player;
        $this->sampleId = $sampleId;
    }

    /**
     * @var Player
     */
    private $player;

    /**
     * @var int
     */
    private $sampleId;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->player->location !== GotoAction::MODULE_LABORATORY) {
            $subAction = new GotoAction(GotoAction::MODULE_LABORATORY);
        } else {
            $subAction = new ConnectAction($this->sampleId);
        }

        $subAction->execute();
    }
}
