<?php

class GameState
{
    /**
     * @var Player[]
     */
    public $players = [];

    /**
     * @var int[]
     */
    public $availableMolecules = [];

    /**
     * @var Sample[]
     */
    public $samples = [];

    /**
     * @var ScienceProject[]
     */
    public $scienceProjects = [];

    /**
     * @param string $molecule
     *
     * @return bool
     */
    public function hasMolecule($molecule)
    {
        return $this->availableMolecules[$molecule] >= 1;
    }
}
