<?php

class UploadDownloadOrDiagnoseSampleAction implements ActionInterface
{

    /**
     * @param Player $player
     * @param int    $sampleId
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
        if ($this->player->location !== GotoAction::MODULE_DIAGNOSIS) {
            $subAction = new GotoAction(GotoAction::MODULE_DIAGNOSIS);
        } else {
            $subAction = new ConnectAction($this->sampleId);
        }

        $subAction->execute();
    }
}
