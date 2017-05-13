<?php

class Sample
{
    const CARRY_FLAG_OWN_ROBOT   = 0;
    const CARRY_FLAG_OTHER_ROBOT = 1;
    const CARRY_FLAG_CLOUD       = -1;

    const RANK_1             = 1;
    const RANK_2             = 2;
    const RANK_3             = 3;

    const COST_NOT_DIAGNOSED = -1;

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

    /**
     * check if the sample was already diagnosed
     * if not yet diagnosed, cost will be set to -1 for all molecules
     *
     * @return bool
     */
    public function isDiagnosed()
    {
        return reset($this->cost) > self::COST_NOT_DIAGNOSED;
    }

    /**
     * @param int[] $expertise
     */
    public function reduceCostsByExpertise($expertise)
    {
        foreach ($expertise as $molecule => $value) {
            if (!$this->isDiagnosed()) {
                continue;
            }

            $this->cost[$molecule] = max(0, ($this->cost[$molecule] - $value));
        }
    }

}

