<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Game;

require_once("./Model/Game.php");

class GameController
{
    private Game $game;

    public function __construct()
    {
        $this->game = new Game();
    }

    public function welcome()
    {
        echo "\n Witaj w grze Scoring Bowling. \n\n";
        $this->game->startRoll();
    }
}
