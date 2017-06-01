<?php

namespace Application\Models\Ship;

class FactoryShip {


    public static function getShips()
    {

        $shipStorage = new \SplObjectStorage;

        $shipStorage->attach(new Destroyer());
        $shipStorage->attach(new Destroyer());
        $shipStorage->attach(new BattleShip());

        return $shipStorage;

    }



}