<?php

class Sample
{

    const CARRY_FLAG_OWN_ROBOT   = 0;
    const CARRY_FLAG_OTHER_ROBOT = 1;
    const CARRY_FLAG_CLOUD       = -1;

    /**
     * unique id of the sample
     *
     * @var int
     */
    public $id;

    /**
     * indicates who's currently carrying this sample (see Sample::CARRY_FLAG_*)
     *
     * @var int
     */
    public $carriedBy;

    /**
     * ignore for this league
     *
     * @var int
     */
    public $rank;

    /**
     * ignore for this league
     *
     * @var int
     */
    public $expertiseGain;

    /**
     * number of health points you gain from this sample
     *
     * @var int
     */
    public $health;

    /**
     * number of molecules of each type needed to research the sample
     *
     * @var int[]
     */
    public $cost = [];
}

