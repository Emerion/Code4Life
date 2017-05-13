<?php

class StateFactory
{
    /**
     * samples uploaded to the cloud lose their level and have 0...
     * so we have to store levels on our own when we first get them
     *
     * @var int
     */
    private $sampleRanks = [];

    /**
     * @param array[] $initialConfig
     * @param array[] $config
     *
     * @return GameState
     */
    public function convertConfig($initialConfig, $config)
    {
        $state = new GameState();

        $state->scienceProjects = $this->createScienceProjects($initialConfig);

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

        usort($state->players[0]->samples, [$this, 'sortSamples']);
        usort($state->players[1]->samples, [$this, 'sortSamples']);

        foreach ($state->samples as $sample) {
            $this->updateSampleRank($sample);
            if ($sample->carriedBy !== Sample::CARRY_FLAG_OTHER_ROBOT) {
                $sample->reduceCostsByExpertise($state->players[0]->expertise);
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

    /**
     * @param array[] $initialConfig
     *
     * @return ScienceProject[]
     */
    private function createScienceProjects($initialConfig)
    {
        $scienceProjects = [];

        foreach ($initialConfig['scienceProjects'] as $requiredMolecules) {
            $project                    = new ScienceProject();
            $project->requiredExpertise = $requiredMolecules;

            $scienceProjects[] = $project;
        }

        return $scienceProjects;
    }

    /**
     * @param Sample $sample
     */
    private function updateSampleRank($sample)
    {
        $rankNotSet = (!isset($this->sampleRanks[$sample->id]) || $this->sampleRanks[$sample->id] == 0);
        if ($rankNotSet && $sample->rank != 0) {
            $this->sampleRanks[$sample->id] = $sample->rank;
        }

        $sample->rank = $this->sampleRanks[$sample->id];
    }

    /**
     * @param Sample $a
     * @param Sample $b
     *
     * @return int
     */
    private function sortSamples($a, $b)
    {
        if ($b->rank == $a->rank) {
            return $b->health - $a->health;
        }

        return $b->rank - $a->rank;
    }

}
