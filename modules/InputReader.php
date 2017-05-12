<?php

class InputReader
{
    public function readInitialConfig()
    {
        // always 0 for now, ignore until in higher leagues

        fscanf(STDIN, '%d', $projectCount);
        for ($i = 0; $i < $projectCount; $i++) {
            fscanf(STDIN, '%d %d %d %d %d', $a, $b, $c, $d, $e);
        }
    }

    /**
     * Read the config of a turn from <STDIN> and return in array format.
     *
     * @return array[]
     */
    public function readTurnConfig()
    {
        $config = [
            'players'            => [],
            'availableMolecules' => [],
            'samples'            => [],
        ];

        for ($playerCount = 0; $playerCount < 2; $playerCount++) {
            fscanf(STDIN, "%s %d %d %d %d %d %d %d %d %d %d %d %d",
                $target, $eta, $score,
                $storageA, $storageB, $storageC, $storageD, $storageE,
                $expertiseA, $expertiseB, $expertiseC, $expertiseD, $expertiseE
            );

            $config['players'][] = [
                'target'    => $target,
                'eta'       => $eta,
                'score'     => $score,
                'storage'   => [
                    Molecule::TYPE_A => $storageA,
                    Molecule::TYPE_B => $storageB,
                    Molecule::TYPE_C => $storageC,
                    Molecule::TYPE_D => $storageD,
                    Molecule::TYPE_E => $storageE,
                ],
                'expertise' => [
                    Molecule::TYPE_A => $expertiseA,
                    Molecule::TYPE_B => $expertiseB,
                    Molecule::TYPE_C => $expertiseC,
                    Molecule::TYPE_D => $expertiseD,
                    Molecule::TYPE_E => $expertiseE,
                ],
            ];
        }

        fscanf(STDIN, "%d %d %d %d %d", $availableA, $availableB, $availableC, $availableD, $availableE);
        $config['availableMolecules'] = [
            Molecule::TYPE_A => $availableA,
            Molecule::TYPE_B => $availableB,
            Molecule::TYPE_C => $availableC,
            Molecule::TYPE_D => $availableD,
            Molecule::TYPE_E => $availableE,
        ];

        fscanf(STDIN, "%d", $sampleCount);
        for ($i = 0; $i < $sampleCount; $i++) {
            fscanf(STDIN, "%d %d %d %s %d %d %d %d %d %d",
                $sampleId, $carriedBy, $rank, $expertiseGain, $health,
                $costA, $costB, $costC, $costD, $costE
            );

            $config['samples'][] = [
                'id'            => $sampleId,
                'carriedBy'     => $carriedBy,
                'rank'          => $rank,
                'expertiseGain' => $expertiseGain,
                'health'        => $health,
                'cost'          => [
                    Molecule::TYPE_A => $costA,
                    Molecule::TYPE_B => $costB,
                    Molecule::TYPE_C => $costC,
                    Molecule::TYPE_D => $costD,
                    Molecule::TYPE_E => $costE,
                ],
            ];
        }

        return $config;
    }
}
