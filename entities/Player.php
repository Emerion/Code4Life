<?php

class Player
{

    /**
     * @todo rename back to target?
     * called "target" before.
     * renamed to location as long as moving does not need time (see eta)
     * module where the player is
     * @var string
     */
    public $location;

    /**
     * ignore for this league (time until player will arrive at target module)
     *
     * @var int
     */
    public $eta;

    /**
     * players number of health points
     *
     * @var int
     */
    public $score;

    /**
     * number of molecules held by this player for each molecule type
     *
     * @var int[]
     */
    public $storage = [];

    /**
     * ignore for this league (expertise gained for the different molecules)
     *
     * @var int[]
     */
    public $expertise = [];

    /**
     * samples currently held by the player
     * can be up to 3
     *
     * @var Sample[]
     */
    public $samples = [];

}
