<?php

namespace snake;

class Snake
{
    private array $drawer = [];
    private array $head = [];
    private string $direct = '';
    private array $body = [];
    private array $food = [];
    private int $frequency = 0;

    const MAX_FREQUENCY = 900000;
    const MIN_FREQUENCY = 300000;
    const LEN = 10;

    const KEYMAP = [
        'w' => 'up',
        's' => 'down',
        'a' => 'left',
        'd' => 'right'
    ];

    const QUIT_KEY = 'q';

    const HEAD_ICON = '♥ ';
    const BODY_ICON = '◎ ';
    const FOOD_ICON = '● ';


    public function __construct() {
        for ($i = 0; $i < self::LEN; $i++) {
            $this->drawer[$i] = array_fill(0, self::LEN, 0);
        }
    }

    public function init() {
        $random_x = mt_rand(0, self::LEN - 1);
        $random_y = mt_rand(0, self::LEN - 1);

        //这是蛇的起始点
        $this->drawer[$random_x][$random_y] = 1;
        $this->head = [$random_x, $random_y];
        $this->body[] = $this->head;
        $this->randomFoods();
        $this->direct = $this->initDirect();
    }

    private function initDirect() {
        $down = $this->drawer[$this->head[0] + 1][$this->head[1]] ?? null;
        $rand = [];
        if (isset($down) && $down !== 1) {
            $rand[] = 'down';
        }
        $up = $this->drawer[$this->head[0] - 1][$this->head[1]] ?? null;
        if (isset($up) && $up !== 1) {
            $rand[] = 'up';
        }
        $left = $this->drawer[$this->head[0]][$this->head[1] - 1] ?? null;
        if (isset($left) && $left !== 1) {
            $rand[] = 'left';
        }
        $right = $this->drawer[$this->head[0]][$this->head[1] + 1] ?? null;
        if (isset($right) && $right !== 1) {
            $rand[] = 'right';
        }
        if (empty($rand)) {
            $this->die();
        }
        $direct = $rand[array_rand($rand)];
        return $direct;
    }

    public function refreshFrequency() {
        if ($this->frequency <= self::MIN_FREQUENCY) {
            $this->frequency = self::MIN_FREQUENCY;
        }
        $this->frequency = self::MAX_FREQUENCY - (10000 * count($this->body));
    }

    public function setDirect(string $direct) {
        $this->direct = $direct;
    }

    public function setFrequency(int $frequency) {
        $this->frequency = $frequency;
    }

    public function getFrequency() {
        return $this->frequency;
    }

    public function runDirect() {

        switch ($this->direct) {
            case 'up':
                $x = $this->head[0] - 1;
                $y = $this->head[1];
                break;
            case 'down':
                $x = $this->head[0] + 1;
                $y = $this->head[1];
                break;
            case 'left':
                $x = $this->head[0];
                $y = $this->head[1]  - 1;
                break;
            case 'right':
                $x = $this->head[0];
                $y = $this->head[1] + 1;
                break;
        }

        if (!isset($this->drawer[$x][$y])) {
            $this->die();
        }
        $head = [$x, $y];
        if (in_array($head, $this->body)) {
            $this->die();
        }
        array_unshift($this->body, $head);
        $this->head = $head;
        if ($head != $this->food) {
            array_pop($this->body);
        } else {
            $this->randomFoods();
        }
    }

    private function randomFoods() {
        $blank = [];
        $len = self::LEN;
        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < $len; $j++) {
                if ([$i, $j] == $this->head) {
                    continue;
                }
                if (in_array([$i, $j], $this->body)) {
                    continue;
                }
                $blank[$i][$j] = 1;
            }
        }

        $rand_x = array_rand($blank);
        $rand_y = array_rand($blank[$rand_x]);
        $this->drawer[$rand_x][$rand_y] = 1;
        $this->food = [$rand_x, $rand_y];
    }

    public function draw() {
        system("clear");
        $len = self::LEN;
        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < $len; $j++) {
                if ([$i, $j] == $this->head) {
                    echo self::HEAD_ICON;
                    continue;
                }
                if (in_array([$i, $j], $this->body)) {
                    echo self::BODY_ICON;
                    continue;
                }
                if ([$i, $j] == $this->food) {
                    echo self::FOOD_ICON;
                    continue;
                }
                echo '- ';
            }
            echo PHP_EOL;
        }
    }

    private function die() {
        echo 'you die >_<';exit;
    }
}