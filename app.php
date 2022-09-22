<?php

declare(strict_types=1);

namespace App;


use App\Controller\GameController;

require_once("./Controller/GameController.php");



(new GameController())->welcome();
