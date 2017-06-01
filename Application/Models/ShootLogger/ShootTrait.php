<?php

namespace Application\Models\ShootLogger;
use Application\Models\Board;

trait ShootTrait {

    /**
     * @var array
     */
    protected $shootMessage = [];

    /**
     * @param $message
     * @param type
     */
    public  function logShootMessage($message, $type)
    {
        $this->shootMessage[$type] =  $message;
    }

    /**
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public function getShootMessageByType($type)
    {

        if(!isset($this->shootMessage[$type])) throw new \Exception('Invalid Type');

        return $this->shootMessage[$type];
    }

    protected function shootActionIfDebugModeON()
    {
        $this->isDebugModeUsed = true;
        $this->logShootMessage('*** / You are entering in the matrix / ***', Board::DEBUG);

        return Board::DEBUG;

    }

    protected function shootActionIfShip($y, $x)
    {
        $this->board[$y][$x][Board::STATE] = Board::HIT;
        $ship = $this->board[$y][$x][Board::SHIP];
        $ship->hit();

        $state = Board::HIT;

        if($ship->isSunked()) {
            $this->logShootMessage('===== [ '. $ship->getName() .' ] is sunken =====', Board::SUNK);
            $this->ships->detach($ship);
            $state = Board::SUNK;

        }

        if($this->ships->count() == 0) {
            $this->isGameFinished = true;
            $msg = 'You finished the game in '.$this->shoots.' moves';
            $this->logShootMessage('=====THE END===== [' . $msg . '] =====THE END=====', Board::THE_END);
            $state = Board::THE_END;

        }

        $this->logShootMessage('===== [ Hit ] =====', Board::HIT);
        return $state;
        
    }

    protected function shootActionIfMiss($y , $x)
    {
        $this->board[$y][$x][Board::STATE] = Board::MISS;
        $this->logShootMessage('===== [ Miss ] =====', Board::MISS);

        return Board::MISS;
    }

    protected function shootActionIfInvalidField()
    {
        $this->logShootMessage('===== [ Invalid Input ] =====', Board::INVALID_FIELD);
        return Board::INVALID_FIELD;
    }

    protected function shootActionIfFieldIsUsed()
    {
        $this->logShootMessage('=====[ You are repeating yourself ] =====', Board::USED_FIELD);
        return Board::USED_FIELD;
    }

}