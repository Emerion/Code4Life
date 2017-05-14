<?php

class Player
{
    const MAX_STORAGE_SIZE     = 10;
    const MAX_SAMPLE_CARRY_NUM = 3;

    /**
     * module where the player is
     *
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

    /**
     * @return Sample[]
     */
    public function getDiagnosedSamples()
    {
        $diagnosed = [];
        foreach ($this->samples as $sample) {
            if ($sample->isDiagnosed()) {
                $diagnosed[] = $sample;
            }
        }

        return $diagnosed;
    }

    /**
     * @param bool $includeUndiagnosed
     *
     * @return int
     */
    public function getSampleCount($includeUndiagnosed = false)
    {
        $count = 0;
        foreach ($this->samples as $sample) {
            if ($sample->isDiagnosed() || $includeUndiagnosed) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return null|Sample
     */
    public function getUndiagnosedSample()
    {
        foreach ($this->samples as $sample) {
            if (!$sample->isDiagnosed()) {
                return $sample;
            }
        }

        return null;
    }

    /**
     * @param int[] $cost
     *
     * @return bool
     */
    public function hasRequiredMolecules($cost)
    {
        foreach ($cost as $molecule => $value) {
            if ($this->storage[$molecule] < $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function getStorageUsage()
    {
        return array_sum($this->storage);
    }

}
