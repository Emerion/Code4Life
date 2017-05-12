<?php

class StateFactory
{
    /**
     * @param array[] $config
     *
     * @return GameState
     */
    public function convertConfig($config)
    {
        $state = new GameState();

        $state->players            = $this->createPlayers($config);
        $state->availableMolecules = $this->convertMolecules($config);
        $state->samples            = $this->convertSamples($config);

        // link already taken samples to their owners
        foreach ($state->samples as $sample) {
            if ($sample->carriedBy == Sample::CARRY_FLAG_OWN_ROBOT) {
                $state->players[0]->samples[] = $sample;
            }
            if ($sample->carriedBy == Sample::CARRY_FLAG_OTHER_ROBOT) {
                $state->players[1]->samples[] = $sample;
            }
        }

        return $state;
    }

    /**
     * @param array[] $config
     *
     * @return Player[]
     */
    private function createPlayers($config)
    {
        $players = [];

        foreach ($config['players'] as $playerConfig) {
            $player            = new Player();
            $player->location  = $playerConfig['target'];
            $player->eta       = $playerConfig['eta'];
            $player->score     = $playerConfig['score'];
            $player->storage   = $playerConfig['storage'];
            $player->expertise = $playerConfig['expertise'];

            $players[] = $player;
        }

        return $players;
    }

    /**
     * @param array[] $config
     *
     * @return int[]
     */
    private function convertMolecules($config)
    {
        return $config['availableMolecules'];
    }

    /**
     * @param array[] $config
     *
     * @return Sample[]
     */
    private function convertSamples($config)
    {
        $samples = [];

        foreach ($config['samples'] as $sampleConfig) {
            $sample                = new Sample();
            $sample->id            = $sampleConfig['id'];
            $sample->carriedBy     = $sampleConfig['carriedBy'];
            $sample->rank          = $sampleConfig['rank'];
            $sample->expertiseGain = $sampleConfig['expertiseGain'];
            $sample->health        = $sampleConfig['health'];
            $sample->cost          = $sampleConfig['cost'];

            $samples[] = $sample;
        }

        return $samples;
    }
}
