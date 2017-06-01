<?php

namespace Application\Models;

use Application\Models\Ship\Ship;

class Coordinates {

    private static $_positions = [
        'horizontal' => [
            'left',
            'right'
        ],
        'vertical' => [
            'up',
            'down'
        ]
    ];

    private $boardModel;

    private $x;

    private $y;

    /**
     * Coordinates constructor.
     * @param Board $boardModel
     */
    public function __construct(Board $boardModel)
    {
        $this->boardModel = $boardModel;

        $this->x = $boardModel->getHorizontal();

        $this->y = $boardModel->getVertical();


    }


    /**
     * @param Ship $ship
     * @return array
     */
    public function buildCoordinates(Ship $ship) {

        switch(array_rand(self::$_positions)) {

            case 'horizontal' :
                $coordinates = $this->buildHorizontalPlacement($ship);
                break;

            case 'vertical':

                $coordinates = $this->buildVerticalPlacement($ship);
                break;
        }

        return $coordinates;

    }

    /**
     * @return array
     */
    private function getRandomCor()
    {
        return [
            'x' => array_rand(array_flip($this->x)),
            'y' => array_rand(array_flip($this->y))
        ];
    }

    /**
     * @param Ship $ship
     * @return array
     */
    private function buildVerticalPlacement(Ship $ship)
    {
        $board = $this->boardModel->getBoard();
        $direction = array_rand(array_flip(self::$_positions['vertical']));
        $length = $ship->getHealth();

        $startFromX = $this->getRandomCor()['x'];
        $startFromY = $this->getRandomCor()['y'];

        $coordinates = [];
        $boardY = array_keys($board);
        $start = array_search($startFromY, $boardY);

        switch($direction) {

            case 'down':

                $end = isset($boardY[($start + ($length-1))]) ? $boardY[($start + ($length-1))] : false;
                break;
            case 'up':

                $end = isset($boardY[($start - ($length-1))]) ? $boardY[($start - ($length-1))] : false;
                $board = array_reverse($board);
                break;

        }

        $outOfTheBoard = (!$end) ? true : false;

        if(!$outOfTheBoard) {

            foreach($board as $keyY => $valueX) {
                if(in_array($keyY, range($boardY[$start], $end)))
                {
                    $valueX[$startFromX][Board::SHIP] = $ship;
                    $coordinates['y'][$keyY] = $valueX[$startFromX];
                }
            }

            $coordinates['x'] = $startFromX;

            if($direction == 'up') {
                $coordinates['y'] = array_reverse($coordinates['y']);
            }

        }

        return [
            'position' => 'vertical',
            'outOfTheBoard' => $outOfTheBoard,
            'shipCoordinates' => $coordinates
        ];

    }

    /**
     * @param $hitPos
     * @param Ship $ship
     * @return array
     */
    private function buildShipLeftHorizontalPlacement($hitPos, Ship $ship) {

        $length = $ship->getHealth();
        $start = ($hitPos - $length)+1;
        $replacement = array_fill($start,$length, array ( Board::STATE => Board::FREE, Board::SHIP => $ship, ));
        $checkPosition = array_keys($replacement);
        $checkPosition = $checkPosition[0];

        return [
            'replacement' => $replacement,
            'checkPosition' => $checkPosition
        ];

    }

    /**
     * @param $hitPos
     * @param Ship $ship
     * @return array
     */
    private function buildShipRightHorizontalPlacement($hitPos, Ship $ship) {

        $length = $ship->getHealth();
        $start = $hitPos;
        $replacement = array_fill($start,$length, array ( Board::STATE => Board::FREE, Board::SHIP => $ship, ));
        $checkPosition = array_keys($replacement);
        $checkPosition = $checkPosition[$length-1];

        return [
            'replacement' => $replacement,
            'checkPosition' => $checkPosition
        ];

    }

    /**
     * @param $direction
     * @param $hitPos
     * @param Ship $ship
     * @return array
     */
    private function getHorizontalPlacementByDirection($direction, $hitPos, Ship $ship)
    {
        $data = [];

        switch($direction) {

            case 'left':

                $data = $this->buildShipLeftHorizontalPlacement($hitPos, $ship);

                break;
            case 'right':
                $data = $this->buildShipRightHorizontalPlacement($hitPos, $ship);
                break;
        }
        return $data;

    }

    /**
     * @param Ship $ship
     * @return array
     */
    private function buildHorizontalPlacement(Ship $ship)
    {
        $startFromX = $this->getRandomCor()['x'];
        $startFromY = $this->getRandomCor()['y'];
        $hitPos = $startFromX;
        $coordinates = [];
        $direction = array_rand(array_flip(self::$_positions['horizontal']));
        $data = $this->getHorizontalPlacementByDirection($direction, $hitPos, $ship);
        $outOfTheBoard = ($data['checkPosition'] < 1 || $data['checkPosition'] > count($this->y)) ? true : false;

        if(!$outOfTheBoard) {
            $coordinates = [
                'x' => $data['replacement'],
                'y' => $startFromY
            ];
        }

        return [
            'position' => 'horizontal',
            'outOfTheBoard' => $outOfTheBoard,
            'shipCoordinates' => $coordinates
        ];

    }

}