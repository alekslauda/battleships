<?php

namespace Application\Controllers;

use Application\Models\Board;
use Helpers\Request;
use Helpers\TemplateEngine as View;
use Helpers\Session as Session;

class Application {


    private $session;
    private $request;
    private $board_model;
    private $isCLI = false;

    public function __construct()
    {
        $this->board_model = new Board();
        $this->request = new Request();
        $this->isCLI = $this->request->isCLI();

        if($this->isCLI) {
            $this->cliAction();

        } else {
            $this->session = Session::getInstance();
            $this->session->start();
            $this->webAction();

        }
    }
    /**
     * command line action
     */
    private function cliAction() {

        while(!$this->board_model->isGameFinished()) {
            $board = $this->board_model->getBoard();
            $debugMode = $this->board_model->isDebugModeUsed();

            echo PHP_EOL;
            echo $this->board_model->parseBoard($board, $this->isCLI, $debugMode);
            echo PHP_EOL;
            echo 'Enter coordinates (row, col), e.g. A5: ';

            $input = trim(fgets(STDIN, 1024));
            $type = $this->board_model->shoot($input, $this->board_model->getBoard());

            echo PHP_EOL;
            echo $this->board_model->getShootMessageByType($type);
            echo PHP_EOL;

        }
    }

    /**
     * web action
     */
    private function webAction() {

        $board_model = $this->session->exists('board_model') ? unserialize($this->session->get('board_model')) : $this->board_model;
        $msg = '';

        if($this->request->isPOSTRequest()) {

            $input = $this->request->post('hit');
            $type = $board_model->shoot($input);
            $msg = $board_model->getShootMessageByType($type);

        }
        //get board from the session after its updated
        $board = $board_model->getBoard();
        $debugMode = $board_model->isDebugModeUsed();
        $parseBoard = $board_model->parseBoard($board, $this->isCLI, $debugMode);
        $isGameFinished = $board_model->isGameFinished();

        $this->session->set('board_model', serialize($board_model));

        if($isGameFinished) {
            $this->session->clear();
        }

        echo View::getInstance()->render('index', [
            'board' => $parseBoard,
            'msg' => $msg,
            'isGameFinished' => $isGameFinished,
        ]);

    }
}