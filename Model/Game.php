<?php

declare(strict_types=1);

namespace App\Model;


class Game
{
    const TOTAL_PINS_FOR_FIELD = 10;

    private $points_for_field = 0;
    public $results = [];

    private $isSpare = false;
    private $isStrike = false;

    private $field = 1;
    private $roll = 1;

    public function startRoll()
    {
        if ($this->field > 10 && $this->isSpare) {
            return $this->bonusRound('spare');
        }

        if ($this->field > 10 && $this->isStrike) {
            return $this->bonusRound('strike');
        }

        if ($this->field > 10) {
            return $this->endGame();
        }

        echo "\n Razem masz " . $this->getScore() . " pkt. \n\n";
        echo "\n " . $this->field . " seria rzutów. \n\n";

        $pins = readline("Liczba zbitych kręgli w " . $this->roll . " rzucie: ");
        $pins = (int) $pins;

        $this->roll($pins);
    }
    public function roll(int $pins)
    {
        $pins = (int) $pins;

        if ($pins || $pins === 0) {

            if ($pins > self::TOTAL_PINS_FOR_FIELD) {
                echo "\n Nie możesz zbić więcej niż 10 kręgli!!! \n\n";

                return $this->startRoll();
            } else if ($this->isSpare && $pins === self::TOTAL_PINS_FOR_FIELD) {
                $this->updateData($pins, true, false, 1, true, true);
                $this->rollMessage($pins, 'strike', $pins);
                $this->points_for_field = 0;
                ///////
                return $this->startRoll();
                ///////////
            } else if ($this->isSpare && $pins < self::TOTAL_PINS_FOR_FIELD) {
                $this->updateData($pins, false, false, 2, true, false);
                $this->rollMessage($pins, null, $pins);
                ///////
                return $this->secondRoll($pins);
            } else if ($this->isStrike && $pins === self::TOTAL_PINS_FOR_FIELD) {

                $this->updateData($pins, true, false, 1, true, true);
                $this->rollMessage($pins, 'strike', $pins);
                $this->points_for_field = 0;
                ////////
                return $this->startRoll();
                //zdobywasz strike
            } else if ($this->isStrike && $pins < self::TOTAL_PINS_FOR_FIELD) {
                $this->updateData($pins, true, false, 2, false, false);
                $this->rollMessage($pins, null, null);
                //////////
                return $this->secondRoll($pins);
            } else if ($pins === self::TOTAL_PINS_FOR_FIELD) {
                $this->updateData($pins, true, false, 1, false, true);
                $this->rollMessage($pins, 'strike', null);
                $this->points_for_field = 0;

                ///////////
                return $this->startRoll();
            } else if ($pins < self::TOTAL_PINS_FOR_FIELD) {
                $this->updateData($pins, false, false, 2, false, false);
                $this->rollMessage($pins, null, null);
                //////////
                return $this->secondRoll($pins);
            }
        } else {
            echo "\n Nie podałeś liczby zbitych kręgli. \n\n";

            return $this->startRoll();
        }
    }
    public function secondRoll(int $pins_from_first_roll)
    {
        echo "\n Razem masz " . $this->getScore() . " pkt. \n\n";
        echo "\n " . $this->field . " seria rzutów. \n\n";

        $pins_in_second_roll = readline("Liczba zbitych kręgli w " . $this->roll . " rzucie: ");
        $pins_in_second_roll = (int) $pins_in_second_roll;

        if ($pins_in_second_roll || $pins_in_second_roll === 0) {

            $max_for_second_roll = self::TOTAL_PINS_FOR_FIELD - $pins_from_first_roll;

            if ($pins_in_second_roll > $max_for_second_roll) {
                echo "\n Nie możesz zbić więcej kręgli niż jest do zbicia !!! \n\n";

                ////////
                return $this->secondRoll($pins_from_first_roll);
                /////////
            } else if ($this->isStrike && $pins_in_second_roll === $max_for_second_roll) {
                $this->updateData($pins_in_second_roll, false, true, 1, true, true);
                $this->rollMessage($this->points_for_field, 'spare', $this->points_for_field);
                $this->points_for_field = 0;
                //////////
                return $this->startRoll();
            } else if ($this->isStrike) {
                $this->updateData($pins_in_second_roll, false, false, 1, true, true);
                $this->rollMessage($this->points_for_field, null, $this->points_for_field);
                $this->points_for_field = 0;
                /////////
                return $this->startRoll();
            } else if ($pins_in_second_roll === $max_for_second_roll) {
                ///////////
                $this->updateData($pins_in_second_roll, false, true, 1, false, true);
                $this->rollMessage($this->points_for_field, 'spare', null);
                $this->points_for_field = 0;
                //////////
                return $this->startRoll();
            } else if ($pins_in_second_roll < $max_for_second_roll) {
                $this->updateData($pins_in_second_roll, false, false, 1, false, true);
                $this->rollMessage($this->points_for_field, null, null);
                $this->points_for_field = 0;
                /////
                return $this->startRoll();
            }
        } else {
            echo "\n Nie podałeś liczby zbitych kręgli. \n\n";

            return $this->secondRoll($pins_from_first_roll);
        }
    }

    public function bonusRound($type)
    {
        echo "\n Razem masz: " . $this->getScore() . " pkt. \n\n";

        if ($type === 'strike') {
            echo "\n\n " . $this->field . " seria rzutów. \n\n";


            $pins = readline("Liczba zbitych kręgli w " . $this->roll . " rzucie: ");
            $pins = (int) $pins;

            if ($pins || $pins === 0) {

                if ($pins < self::TOTAL_PINS_FOR_FIELD) {
                    $this->updateData($pins, false, false, 2, false, false);
                    return $this->bonusSecondRoll($this->points_for_field);
                }
                $this->errorToManyPins($pins, 'strike');
                $this->updateData($pins, true, false, 2, true, false);
                $this->rollMessage($this->points_for_field, null, $this->points_for_field);
                $this->endGame();
            } else {
                echo "\n Nie podałeś liczby zbitych kręgli. \n\n";

                return $this->bonusRound('strike');
            }
        } else if ($type === 'spare') {

            echo $this->field . " seria rzutów. \n\n";

            $pins = readline("Liczba zbitych kręgli w " . $this->roll . " rzucie: ");
            $pins = (int) $pins;

            if ($pins || $pins === 0) {
                $this->errorToManyPins($pins, 'spare');
                $this->updateData($pins, false, false, 1, true, false);
                $this->rollMessage($this->points_for_field, null, $this->points_for_field);
                return $this->endGame();
            } else {
                echo "\n Nie podałeś liczby zbitych kręgli. \n\n";


                return $this->bonusRound('spare');
            }
        }
    }

    public function bonusSecondRoll($pins_from_first_roll)
    {
        echo "\n Razem masz " . $this->getScore() . " pkt. \n\n";
        echo " \n " . $this->field . " seria rzutów. \n\n";

        $pins_in_second_roll = readline("Liczba zbitych kręgli w " . $this->roll . "rzucie: ");

        if ($pins_in_second_roll || $pins_in_second_roll == 0) {

            $pins_in_second_roll = (int) $pins_in_second_roll;
            $max_for_second_roll = self::TOTAL_PINS_FOR_FIELD - $pins_from_first_roll;

            if ($pins_in_second_roll > $max_for_second_roll) {
                echo "\n Nie możesz zbić więcej kręgli niż jest do zbicia !!! \n\n";


                ////////
                return $this->bonusSecondRoll($pins_from_first_roll);
            }

            $this->updateData($pins_in_second_roll, false, false, 2, true, false);
            $this->rollMessage($this->points_for_field, null, $this->points_for_field);

            $this->endGame();
        }
    }
    public function errorToManyPins($pins, $type)
    {
        if ($pins > self::TOTAL_PINS_FOR_FIELD) {
            echo "\n Nie możesz zbić więcej niż 10 kręgli!!! \n\n";

            return $this->bonusRound($type);
        }
    }
    public function getScore()
    {
        $total_points = 0;

        foreach ($this->results as $key => $result) {
            $total_points += $result;
        };


        return $total_points;
    }
    private function rollMessage(int $pins, ?string $type, ?int $bonusPins)
    {
        if ($pins) {
            echo "\n Zbiłeś " . $pins . " kręgli. \n\n";
        }
        if ($type) {
            echo "\n Brawo! Zdobywasz " . $type . " ! \n\n  ";
        }

        if ($bonusPins) {
            echo "\n Bonus do poprzedniej rundy to " . $bonusPins . " pkt! \n\n";
        }
    }
    private function updateData(int $pins, bool $isStrike, bool $isSpare, int $roll, $bonus = false, $nextField = false)
    {
        $this->points_for_field += $pins;
        $this->isStrike = $isStrike;
        $this->isSpare = $isSpare;
        $this->results[$this->field] = $this->points_for_field;

        if ($roll) {
            $this->roll = $roll;
        }
        if ($bonus) {
            $this->results[$this->field - 1] += $this->points_for_field;
        }
        if ($nextField) {
            $this->field++;
        }
    }

    private function endGame()
    {
        echo "\n Gra zakończona z wynikiem " . $this->getScore() . "\n\n";

        foreach ($this->results as $key => $score) {
            echo " $key runda:";
            echo "$score pkt. ";
        }
        echo "\n\n";
    }
}
