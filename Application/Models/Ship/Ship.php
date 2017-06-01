<?php

namespace Application\Models\Ship;

abstract class Ship {

    protected $isSunked = false;

    public function isSunked() {
        return $this->isSunked;
    }

    public function getHealth()
    {
        return $this->health;
    }

    public function hit()
    {
        --$this->health;

        if($this->health == 0) {
            $this->isSunked = true;
        }

    }

    public function getName()
    {
        return $this->name;
    }

}