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
     * Perform a deep clone of all nested objects when cloning.
     * This is needed because calculation of different scenarios should not alter other states.
     *
     * @return GameState
     */
    public function __clone()
    {
        return json_decode(json_encode($this));
    }
}
