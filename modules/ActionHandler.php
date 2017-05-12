<?php

/**
 * VICTORY CONDITIONS:
 * - most health points after 200 turns
 * - OR: one player has 170 points
 */
class ActionHandler
{
    /**
     * @var GameState[]
     */
    private $history = [];

    /**
     * @var ActionInterface[]
     */
    private $actionHistory = [];

    /**
     * @param GameState $state
     */
    public function executeAction($state)
    {
        $action = $this->getAction($state);
        $action->execute();

        $this->history[]       = $state;
        $this->actionHistory[] = $action;
    }

    /**
     * First implementation of AI logic.
     *    1. Grab a sample
     *    2. Grab required molecules
     *    3. Go and research sample at the lab
     *    4. Start over again
     *
     * @param GameState $state
     *
     * @return ActionInterface
     */
    private function getAction($state)
    {
        $player = $state->players[0];

        if (!$this->hasSampleFile($player)) {
            if ($player->location !== GotoAction::MODULE_DIAGNOSIS) {
                return new GotoAction(GotoAction::MODULE_DIAGNOSIS);
            }

            // @todo just get the first sample for now
            // do some rating based on cost & gain to pick a better one later
            return new ConnectAction($this->getSampleToDownload($state));
        }

        if (!$this->hasRequiredMolecules($player)) {
            if ($player->location !== GotoAction::MODULE_MOLECULES) {
                return new GotoAction(GotoAction::MODULE_MOLECULES);
            }

            return new ConnectAction($this->getRequiredMolecule($player));
        }

        if ($player->location !== GotoAction::MODULE_LABORATORY) {
            return new GotoAction(GotoAction::MODULE_LABORATORY);
        }

        return new ConnectAction($player->samples[0]->id);
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    private function hasSampleFile($player)
    {
        return isset($player->samples[0]);
    }

    /**
     * @param GameState $state
     *
     * @return int
     */
    private function getSampleToDownload($state)
    {
        foreach ($state->samples as $sample) {
            if ($sample->carriedBy == Sample::CARRY_FLAG_CLOUD) {
                return $sample->id;
            }
        }

        throw new \InvalidArgumentException('No sample found.. What now?!');
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    private function hasRequiredMolecules($player)
    {
        $sample = $this->getCurrentSample($player);

        foreach ($sample->cost as $molecule => $value) {
            $stock = isset($player->storage[$molecule]) ? $player->storage[$molecule] : 0;
            if ($stock < $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $player
     *
     * @return string
     */
    private function getRequiredMolecule($player)
    {
        $sample = $this->getCurrentSample($player);

        foreach ($sample->cost as $molecule => $value) {
            $stock = isset($player->storage[$molecule]) ? $player->storage[$molecule] : 0;
            if ($stock < $value) {
                return $molecule;
            }
        }

        throw new \InvalidArgumentException('No needed molecule found. This should not happen..');
    }

    /**
     * @param Player $player
     *
     * @return Sample
     */
    private function getCurrentSample($player)
    {
        $sample = reset($player->samples);
        if (!$sample) {
            throw new \InvalidArgumentException('Player does not carry a sample at the moment..');
        }

        return $sample;
    }

}
