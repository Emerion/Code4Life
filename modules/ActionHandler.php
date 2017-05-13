<?php

/**
 * VICTORY CONDITIONS:
 * - most health points after 200 turns
 * - OR: one player has 170 points
 */
class ActionHandler
{
    const RANK2_EXPERTISE_THRESHOLD = 1;
    const RANK3_EXPERTISE_THRESHOLD = 5;

    /**
     * @param GameState $state
     *
     * @return ActionInterface
     */
    public function getAction($state)
    {
        $player = $state->players[0];

        $canResearchSample   = $this->canResearchSample($player);
        $couldResearchSample = $this->couldResearchSample($state, $player);

        // if we can not carry more molecules and we can still not research anything
        // lets throw away the sample where we have least molecules for and grab a new one until
        // we can research something to get our inventory empty again
        if (!$canResearchSample && !$couldResearchSample && $player->getSampleCount() >= 1) {
            $sample = $this->getWorstSample($player);

            // upload to cloud
            return new UploadDownloadOrDiagnoseSampleAction($player, $sample->id);
        }

        // if we have undiagnosed samples and are currently standing at the diagnosis module
        // prefer diagnostics action over getting additional samples
        $sample = $player->getUndiagnosedSample();
        if ($sample !== null && $player->location === GotoAction::MODULE_DIAGNOSIS) {
            // diagnose
            return new UploadDownloadOrDiagnoseSampleAction($player, $sample->id);
        }

        // if the player is currently holding less than the maximum amount of samples
        // go and get some new ones. Except in case the player can research with currently
        // holding ones. In this case go and research first to be able to pickup more afterwards at once
        $sampleCount          = $player->getSampleCount(true);
        $shouldPickNewSamples = $sampleCount < Player::MAX_SAMPLE_CARRY_NUM && (in_array($player->location , [GotoAction::MODULE_SAMPLES, GotoAction::MODULE_DIAGNOSIS])) || $sampleCount === 0;

        if ($shouldPickNewSamples && !$canResearchSample) {
            // todo.. seems that the cloud download makes no sense in bronze league anymore because of the travel times
            // check if picking up an already known sample from the cloud would be a better option
            // than grabbing an entire new one
            // $cloudFulfillable = $this->getCloudFulfillable($state, $player);
            // if ($cloudFulfillable !== null) {
            //     // download from cloud
            //     return new UploadDownloadOrDiagnoseSampleAction($player, $cloudFulfillable->id);
            // }

            if ($player->location !== GotoAction::MODULE_SAMPLES) {
                return new GotoAction(GotoAction::MODULE_SAMPLES);
            }

            // get a new unknown sample from the sample module
            return new ConnectAction($this->getDownloadSampleRank($player));
        }

        // if we're carrying undiagnosed samples move to the diagnosis module
        // and diagnose them first before doing anything else
        $sample = $player->getUndiagnosedSample();
        if ($sample !== null) {
            return new UploadDownloadOrDiagnoseSampleAction($player, $sample->id);
        }

        // as long as it makes sense to pickup molecules in order to research afterwards
        // do the pickup...
        if (!$canResearchSample || $this->couldResearchMore($state, $player)) {
            $requiredMolecules = $this->getRequiredMolecules($state, $player);
            if (!empty($requiredMolecules)) {
                return new PickupMoleculeAction($player, $requiredMolecules[0]);
            }
        }

        $sample = $this->getResearchableSample($player);

        return new ResearchAction($player, $sample->id);
    }

    /**
     * @var Player $player
     * @return int
     */
    private function getDownloadSampleRank($player)
    {
        $totalExpertise = array_sum($player->expertise);
        if ($totalExpertise >= self::RANK3_EXPERTISE_THRESHOLD) {
            if ($player->getSampleCount(true) == 2) {
                return Sample::RANK_2;
            }
            return Sample::RANK_3;
        }

        if ($totalExpertise >= self::RANK2_EXPERTISE_THRESHOLD) {
            return Sample::RANK_2;
        }

        return Sample::RANK_2;
    }

    /**
     * @param GameState $state
     * @param Player    $player
     *
     * @return string[]
     */
    private function getRequiredMolecules($state, $player)
    {
        $required = [];
        foreach ($player->getDiagnosedSamples() as $sample) {
            foreach ($sample->cost as $molecule => $value) {
                if ($player->hasRequiredMolecules([$molecule => $value])) {
                    continue;
                }

                if (!$state->hasMolecule($molecule)) {
                    continue;
                }

                if (!$this->couldHaveRequiredMolecules($state, $player, $sample)) {
                    continue;
                }

                $required[] = $molecule;
            }
        }

        return $required;
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    private function canResearchSample($player)
    {
        $canResearchAny = false;
        foreach ($player->getDiagnosedSamples() as $sample) {
            $canResearchAny |= $player->hasRequiredMolecules($sample->cost);
        }

        return $canResearchAny;
    }

    /**
     * @param GameState $state
     * @param Player    $player
     *
     * @return bool
     */
    private function couldResearchSample($state, $player)
    {
        $canResearchAny = false;
        foreach ($player->getDiagnosedSamples() as $sample) {
            $canResearchAny |= $this->couldHaveRequiredMolecules($state, $player, $sample);
        }

        return $canResearchAny;
    }

    /**
     * @param Player $player
     *
     * @return Sample
     */
    private function getWorstSample($player)
    {
        $lowPriceRate  = 1;
        $lowPriceIndex = 0;

        foreach ($player->getDiagnosedSamples() as $index => $sample) {
            $priceRate = 0;
            $costNum   = 0;
            foreach ($sample->cost as $molecule => $value) {
                if ($value > 0) {
                    $costNum++;
                }
            }

            foreach ($sample->cost as $molecule => $value) {
                if ($value == 0) {
                    continue;
                }

                $playerValue = $player->storage[$molecule];
                $priceRate   += max($playerValue / $value, 1) / $costNum;
            }

            if ($priceRate < $lowPriceRate) {
                $lowPriceRate  = $priceRate;
                $lowPriceIndex = $index;
            }
        }

        return $player->samples[$lowPriceIndex];
    }

    /**
     * @param Player $player
     *
     * @return Sample|null
     */
    private function getResearchableSample($player)
    {
        foreach ($player->getDiagnosedSamples() as $sample) {
            if ($player->hasRequiredMolecules($sample->cost)) {
                return $sample;
            }
        }

        return null;
    }

    /**
     * @param GameState $state
     * @param Player    $player
     *
     * @return null|Sample
     */
    private function getCloudFulfillable($state, $player)
    {
        foreach ($state->samples as $sample) {
            if ($sample->carriedBy !== Sample::CARRY_FLAG_CLOUD) {
                continue;
            }

            if (!$sample->isDiagnosed()) {
                continue;
            }

            if ($sample->rank == Sample::RANK_1) {
                continue;
            }

            if ($player->hasRequiredMolecules($sample->cost)
                || $this->couldHaveRequiredMolecules($state, $player, $sample)
            ) {
                return $sample;
            }
        }

        return null;
    }

    /**
     * @param GameState $state
     * @param Player    $player
     * @param Sample    $sample
     *
     * @return bool
     */
    private function couldHaveRequiredMolecules($state, $player, $sample)
    {
        $storageUsage = array_sum($player->storage);

        foreach ($sample->cost as $molecule => $cost) {
            if ($cost <= 0) {
                continue;
            }

            $playerValue = $player->storage[$molecule];
            $levelValue  = $state->availableMolecules[$molecule];

            $total = $playerValue + $levelValue;
            if ($total < $cost) {
                return false;
            }

            $diff         = $cost - $playerValue;
            $storageUsage += max($diff, 0);
            if ($storageUsage >= Player::MAX_STORAGE_SIZE) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param GameState $state
     * @param Player    $player
     *
     * @return bool
     */
    private function couldResearchMore($state, $player)
    {
        foreach ($player->getDiagnosedSamples() as $sample) {
            if ($player->hasRequiredMolecules($sample->cost)) {
                continue;
            }

            if (!$this->couldHaveRequiredMolecules($state, $player, $sample)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
