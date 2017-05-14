<?php

/**
 * VICTORY CONDITIONS:
 * - most health points after 200 turns
 * - OR: one player has 170 points
 */
class ActionHandler
{
    const RANK3_EXPERTISE_THRESHOLD_ONE = 3;
    const RANK3_EXPERTISE_THRESHOLD_TWO = 5;

    const MOLECULES_STEAL_THRESHOLD = 2;

    /**
     * @param GameState $state
     *
     * @return ActionInterface
     */
    public function getAction($state)
    {
        $player      = $state->players[0];
        $otherPlayer = $state->players[1];

        if ($player->eta > 0) {
            return new WaitAction();
        }

        // if we're currently at the molecules module and the other player is as well
        // check if we can block him by stealing some molecules
        if ($player->location === GotoAction::MODULE_MOLECULES && $otherPlayer->location === GotoAction::MODULE_MOLECULES) {
            $molecule = $this->getMoleculeToSteal($state, $player, $otherPlayer);
            if ($molecule) {
                return new ConnectAction($molecule);
            }
        }

        $canResearchSample   = $this->canResearchSample($player);
        $couldResearchSample = $this->couldResearchSample($state, $player);
        $sampleCount         = $player->getSampleCount();

        // if we're at the lab because we just researched and are about to go back because we can not get the molecules
        // for the samples we're currently holding, check if the other one will research within the next X turns
        // because this might give the molecules we need and save time to travel
        if ($player->location === GotoAction::MODULE_LABORATORY
            && !$canResearchSample
            && !$couldResearchSample
            && $sampleCount >= 1
            && ($otherPlayer->location === GotoAction::MODULE_MOLECULES || $otherPlayer->location === GotoAction::MODULE_LABORATORY)
            && $this->couldResearchMore($state, $otherPlayer)
        ) {
            if ($player->location !== GotoAction::MODULE_MOLECULES) {
                return new GotoAction(GotoAction::MODULE_MOLECULES);
            }

            return new WaitAction();
        }

        // if we can not carry more molecules and we can still not research anything
        // lets throw away the sample where we have least molecules for and grab a new one until
        // we can research something to get our inventory empty again
        if (!$canResearchSample && !$couldResearchSample && $sampleCount >= 1) {
            $sample = $this->getWorstSample($player);

            // upload to cloud
            return new UploadDownloadOrDiagnoseSampleAction($player, $sample->id);
        }

        // if we have undiagnosed samples and are currently standing at the diagnosis module
        // prefer diagnostics action over getting additional samples
        if ($player->location === GotoAction::MODULE_DIAGNOSIS) {
            $sample = $player->getUndiagnosedSample();
            if ($sample === null && $player->getSampleCount(true) < Player::MAX_SAMPLE_CARRY_NUM) {
                $sample = $this->getCloudFulfillable($state, $player);
            }

            if ($sample !== null) {
                // diagnose or download
                return new UploadDownloadOrDiagnoseSampleAction($player, $sample->id);
            }
        }

        // if the player is currently holding less than the maximum amount of samples
        // go and get some new ones. Except in case the player can research with currently
        // holding ones. In this case go and research first to be able to pickup more afterwards at once
        $sampleCount          = $player->getSampleCount(true);
        $shouldPickNewSamples = $sampleCount < Player::MAX_SAMPLE_CARRY_NUM && (in_array($player->location,
                [GotoAction::MODULE_SAMPLES, GotoAction::MODULE_DIAGNOSIS])) || $sampleCount === 0;

        if ($shouldPickNewSamples && !$canResearchSample && !$couldResearchSample) {
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
            $requiredMolecules = $this->getRequiredMolecules($state, $player, $otherPlayer);
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
        $sampleCount    = $player->getSampleCount(true);

        if ($totalExpertise >= self::RANK3_EXPERTISE_THRESHOLD_TWO && $sampleCount < 2) {
            return Sample::RANK_3;
        }

        if ($totalExpertise >= self::RANK3_EXPERTISE_THRESHOLD_ONE && $sampleCount < 1) {
            return Sample::RANK_3;
        }

        return Sample::RANK_2;
    }

    /**
     * @param GameState $state
     * @param Player    $player
     * @param Player    $otherPlayer
     *
     * @return string[]
     */
    private function getRequiredMolecules($state, $player, $otherPlayer = null)
    {
        $required = [];
        foreach ($player->getDiagnosedSamples() as $sample) {
            $required[$sample->id] = [];
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

                $required[$sample->id][] = $molecule;
            }

            // try and sort required molecules to have the ones the other player needs as well
            // in front so that they are picked up first to reduce the chance of being blocked
            if ($otherPlayer) {
                $requiredMolecules = $this->getRequiredMolecules($state, $otherPlayer);
                usort(
                    $required[$sample->id],
                    function ($a, $b) use ($requiredMolecules) {
                        if (in_array($a, $requiredMolecules)) {
                            return -1;
                        }

                        if (in_array($b, $requiredMolecules)) {
                            return 1;
                        }

                        return 0;
                    }
                );
            }
        }

        $required = array_filter($required);
        if (empty($required)) {
            return [];
        }

        return call_user_func_array('array_merge', $required);
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
        $storageUsage = $player->getStorageUsage();

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

    /**
     * @param GameState $state
     * @param Player    $player
     * @param Player    $otherPlayer
     *
     * @return null|string
     */
    private function getMoleculeToSteal($state, $player, $otherPlayer)
    {
        if ($player->getStorageUsage() === Player::MAX_STORAGE_SIZE) {
            return null;
        }

        if (!$this->couldResearchMore($state, $otherPlayer)) {
            return null;
        }

        $molecules = $this->getRequiredMoleculesToSteal($otherPlayer);
        foreach ($molecules as $molecule) {
            if ($state->availableMolecules[$molecule] === self::MOLECULES_STEAL_THRESHOLD) {
                return $molecule;
            }
        }

        return null;
    }

    /**
     * @param Player $otherPlayer
     *
     * @return string[]
     */
    private function getRequiredMoleculesToSteal($otherPlayer)
    {
        $neededMolecules = [];
        $stock           = $otherPlayer->storage;
        $expertise       = $otherPlayer->expertise;

        foreach ($otherPlayer->getDiagnosedSamples() as $sample) {
            foreach ($sample->cost as $molecule => $value) {
                $cost = $value - $expertise[$molecule];
                $need = max(0, $cost - $stock[$molecule]);

                if ($need === self::MOLECULES_STEAL_THRESHOLD) {
                    $neededMolecules[] = $molecule;
                }
            }
        }

        return $neededMolecules;
    }
}
