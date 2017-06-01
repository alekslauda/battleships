<?php

namespace Application\Models;

use Application\Models\ShootLogger\ShootLogger;
use Application\Models\ShootLogger\ShootTrait;
use Application\Models\Ship\FactoryShip;
use Application\Models\Ship\Ship;

class Board implements ShootLogger {

    use ShootTrait;

    const FREE = 0; // .
    const HIT = 1; // X
    const MISS = 2; // -

    const INVALID_FIELD = 4;
    const USED_FIELD = 5;
    const DEBUG = 'show';
    const SUNK = 6;

    const STATE = 'state';
    const SHIP = 'ship';

    const THE_END = 9;

    protected $xHorizontal;
    protected $yVertical;

    private $borderHeader;

    private $coordinate;

    protected $ships = [];

    protected $board = [];

    private $isDebugModeUsed = false;

    private $shoots = 0;

    private $usedFields = [];

    /**
     * @var bool
     */
    private $isGameFinished = false;

    /**
     * Board constructor.
     * @param int $xHorizontal
     * @param string $yVertical
     * @throws \Exception
     */
    public function __construct($xHorizontal = 10, $yVertical = 'J')
    {
        //generate horizontal array
        $this->xHorizontal = range(1, $xHorizontal);
        //generate vertical array
        $this->yVertical = range('A', $yVertical);

        //make sure that everything is ok with the board and then generate it
        if (count($this->yVertical) != count($this->xHorizontal)) {
            throw new \Exception('Board could not be loaded');
        }

        //set the header depend on the passed variables, preventing bug board
        $this->borderHeader = range(1, count($this->xHorizontal));

        $this->coordinate = new Coordinates($this);

        $this->generateBoard();
        $this->setShips();
        $this->placeShips();

    }

    /**
     * @return array
     */
    public function getHorizontal()
    {
        return $this->xHorizontal;
    }

    /**
     * @param $xHorizontal
     */
    public function setHorizontal($xHorizontal)
    {
        $this->xHorizontal = $xHorizontal;
    }

    /**
     * @param $yVertical
     */
    public function setVertical($yVertical)
    {
        $this->yVertical = $yVertical;
    }

    /**
     * @return array
     */
    public function getVertical()
    {
        return $this->yVertical;
    }

    /**
     * @return array
     */
    public function getBoard()
    {
        return (array)$this->board;
    }

    public function setBoard($board)
    {
        $this->board = $board;
    }

    /**
     * @param $inputField
     * @return array
     */
    private function parseInputField($inputField)
    {

        $regex = '/^([a-zA-Z])([1-9]|10)$/';

        $isValid = true;

        if(preg_match($regex, $inputField, $matches) === false) {
            $isValid = false;
        }
        if($matches != false) {
            if(!in_array(strtoupper($inputField[0]), $this->yVertical) || !in_array($inputField[1], $this->xHorizontal)) {
                $isValid = false;
            }
        }

        if($matches == false) {
            $isValid = false;
        }

        return [
            'isValid' => $isValid,
            'matches' => $matches
        ];

    }

    public function isDebugModeUsed()
    {
        return $this->isDebugModeUsed;
    }

    /**
     * @param $y
     * @param $x
     */
    private function setUsedFields($y, $x) {
        $this->usedFields[$y][] = $x;
    }

    //make them none - static because we store the whole object in the session
    /**
     * @return array
     */
    private function getUsedFields()
    {
        return $this->usedFields;
    }

    /**
     * @return bool
     */
    public function isGameFinished()
    {
        return $this->isGameFinished;
    }

    /**
     * @param $input
     * @return int|string
     * @throws \Exception
     */
    public function shoot($input)
    {
        $inputData = $this->parseInputField($input);
        $shootMessage = '';

        if((strcmp($input, self::DEBUG) == 0)) {
            $shootMessage = $this->shootActionIfDebugModeON();

        } else {

            $this->isDebugModeUsed = false;

            if($inputData['isValid']) {

                $this->shoots++;
                $x = $inputData['matches'][2];
                $y = strtoupper($inputData['matches'][1]);

                if($this->checkIfPositionIsUsed($y, $x)) {
                    $shootMessage = $this->shootActionIfFieldIsUsed();

                } elseif($this->board[$y][$x][self::SHIP] != null) {
                    $shootMessage = $this->shootActionIfShip($y, $x);

                } elseif($this->board[$y][$x][self::SHIP] == null) {
                    $shootMessage = $this->shootActionIfMiss($y, $x);

                }
                $this->setUsedFields($y, $x);

            } else {
                $shootMessage = $this->shootActionIfInvalidField();

            }

        }

        if($shootMessage == '') {
            throw new \Exception('Error!');
        }
        return $shootMessage;

    }

    /**
     * @param $board
     * @param bool $isCLI
     * @param bool $debugMode
     * @return string
     */
    public function parseBoard($board, $isCLI = false, $debugMode = false)
    {
        $delimeter  = str_repeat(" ", 2);

        $str = " ";
        $str .= $delimeter;

        foreach($this->borderHeader as $h) {

            $str .= $h . $delimeter;

        }
        $str .= PHP_EOL;
        foreach($board as $y => $x) {
            $str .= $y;

            foreach($x as $horizontal_values) {
                $horizontal_value = $this->convertValuesToSign($horizontal_values, $debugMode);
                $str .= $delimeter . $horizontal_value;

            }
            $str .= PHP_EOL;

        }
        $boardParsed =  '<pre>' . $str . '</pre>';
        if($isCLI) {
            $boardParsed = strip_tags($str);

        }
        return $boardParsed;

    }

    /**
     * generate the coordinate multidimensional array which will represent the board
     */
    private function generateBoard()
    {
        foreach($this->yVertical as $y) {
            foreach($this->xHorizontal as $x) {

                $this->board[$y][$x] = [
                    self::STATE => self::FREE,
                    self::SHIP => null
                ];
            }
        }
    }

    /**
     * @param $values
     * @param $debugMode
     * @return mixed|string
     * @throws \Exception
     */
    private function convertValuesToSign($values, $debugMode)
    {

        if($debugMode) {

            if(($values[self::STATE] == self::FREE && $values[self::SHIP] != null)) {
                $parsedItem = 'X';
            } else {
                $parsedItem = " ";
            }

        } else {

            $parseToSignData = [
                 '.' => self::FREE,
                'X' => self::HIT,
                '-' => self::MISS,
            ];

            if(!in_array($values[self::STATE], $parseToSignData)) {
                throw new \Exception('Invalid passed state - ' . $values[self::STATE] . ' is not available one');
            }

            $parsedItem = array_search($values[self::STATE], $parseToSignData);

        }

        return $parsedItem;


    }

    /**
     * prepare the ships for sailing :P
     */
    private function setShips()
    {
        $this->ships = FactoryShip::getShips();
    }


    /**
     * place ships on the board
     */
    private function placeShips()
    {

        foreach($this->ships as $ship) {

            //build the helper coordinates and pass it to the recursion
            $coordinates = $this->coordinate->buildCoordinates($ship);

            $this->placeShip($ship, $coordinates);

        }
    }

    private $usedPositions = [
        'vertical' => [],
        'horizontal' => []
    ];

    /**
     * @param Ship $ship
     * @param $coordinates
     * [doc] main functionality
     * -----    using recursion to place ships and check if that is possible based on the ship coordinates  -----
     */
    private function placeShip(Ship $ship, array $coordinates) {

        $skipShip = false;

        if($coordinates['outOfTheBoard'] == true || $coordinates['shipCoordinates'] == false) {

            $skipShip = true;

        } elseif(!$this->checkIfShipCanBePlaced($coordinates)) {

            $skipShip = true;
        }

        if($skipShip) {
            $this->placeShip($ship, $this->coordinate->buildCoordinates($ship));
        } else {

            $this->usedPositions[$coordinates['position']][] = $coordinates['shipCoordinates'];

            if ($coordinates['position'] == 'vertical') {
                $this->placeVerticalShipByCoordinates($coordinates['shipCoordinates']);
            }
            if ($coordinates['position'] == 'horizontal') {
                $this->placeHorizontalShipByCoordinates($coordinates['shipCoordinates']);
            }

        }

    }

    /**
     * @param $coordinates
     */
    private function placeHorizontalShipByCoordinates($coordinates) {

        $this->board[$coordinates['y']] = array_replace($this->board[$coordinates['y']], $coordinates['x']);

    }

    /**
     * @param $coordinates
     */
    private function placeVerticalShipByCoordinates($coordinates) {

        foreach($this->board as $keyY => $valueX) {

            if(array_key_exists($keyY, $coordinates['y'])) {
                $this->board[$keyY][$coordinates['x']] = $coordinates['y'][$keyY];
            }

        }

    }

    private function unifyShipCheckPlacement($coordinates, $placements) {

        if($placements == false) {
            throw new \Exception('Invalid Argument');
        }

        $canBePlaced = true;

        foreach($this->usedPositions as $position => $value) {

            if($this->usedPositions[$position] != false) {

                foreach($this->usedPositions[$position] as $key => $usedP) {

                    if(is_int($usedP[$placements[0]]) || is_string($usedP[$placements[0]])) {
                        if(array_key_exists($usedP[$placements[0]], $coordinates['shipCoordinates'][$placements[0]])) {

                            if(array_key_exists($coordinates['shipCoordinates'][$placements[1]], $usedP[$placements[1]])) {
                                $canBePlaced = false;
                            }
                        }
                    }

                    if(array_search($coordinates['shipCoordinates'][$placements[1]], $usedP) !== false) {

                        foreach($usedP[$placements[0]] as $k => $Y) {

                            if(array_key_exists($k, $coordinates['shipCoordinates'][$placements[0]])) {
                                $canBePlaced = false;
                            }
                        }
                    }
                }

            }

        }
        return $canBePlaced;

    }


    /**
     * @param $coordinates
     * @return bool
     */
    private function checkIfShipCanBePlaced($coordinates)
    {
        $placement = [];

        if ($coordinates['position'] == 'vertical') {
            $placement = ['y', 'x'];
        }
        if ($coordinates['position'] == 'horizontal') {
            $placement = ['x', 'y'];
        }

        return $this->unifyShipCheckPlacement($coordinates, $placement);
    }

    /**
     * @param $y
     * @param $x
     * @return bool
     */
    private function checkIfPositionIsUsed($y, $x)
    {
        $usedFields = $this->getUsedFields();

        return (array_key_exists($y, $usedFields) && in_array($x, $usedFields[$y]));
    }


}
