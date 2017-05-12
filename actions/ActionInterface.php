<?php

interface ActionInterface
{
    /**
     * Executing an action will print out the needed output for the game turn.
     */
    public function execute();

    // @todo
    // add rating function to be able to prioritize different simulated actions and execute the most valuable one
}
