<?php

namespace snake;

class Snake
{
    /**
     * @var array 整个画布
     */
    private array $drawer = [];

    /**
     * @var array 蛇头部的坐标
     */
    private array $head = [];

    /**
     * @var string 蛇的移动方向
     */
    private string $direct = '';

    /**
     * @var array 蛇身体的坐标
     */
    private array $body = [];

    /**
     * @var array 食物的坐标
     */
    private array $food = [];

    /**
     * @var int 蛇移动速度
     */
    private int $frequency = 0;

    /**
     * @var int 画布长宽
     */
    private int $length = self::LEN;

    /**
     * @var string 死亡提示
     */
    private string $tip = 'you died >_<';

    private string $body_str = '';

    /**
     * @var bool 是否生成画布
     */
    private $draw = false;

    const BEST_WISHES = 'best wishes for next challenge';

    const WELCOME_TIP = <<<TIP

        play this game by keys:
        w => up
        s => down
        a => left
        d => right
        
        triangle is snake's head and the round is food, have fun!

        TIP;

    const MAX_FREQUENCY = 600000;
    const MIN_FREQUENCY = 80000;
    const LEN = 10;

    const KEYMAP = [
        'w' => 'up',
        's' => 'down',
        'a' => 'left',
        'd' => 'right'
    ];

    const QUIT_KEY = 'q';

    /**
     * 蛇头部图标
     */
    const HEAD_ICON = [
        'up' => '^ ',
        'down' => '| ',
        'left' => '< ',
        'right' => '> '
    ];

    /**
     * 蛇身体坐标
     */
    const BODY_ICON = '+ ';

    /**
     * 食物图标
     */
    const FOOD_ICON = '* ';


    public function __construct() {
        $this->clear();
        echo self::WELCOME_TIP, PHP_EOL;
    }

    /**
     * @desc 初始化画布/蛇头(起点)/食物/方向
     * @user chenlei11
     * @date 2021/11/24
     */
    public function init(): void {

        //画布
        $this->initDrawer();

        $random_x = mt_rand(0, $this->length - 1);
        $random_y = mt_rand(0, $this->length - 1);

        //这是蛇的起始点
        $this->drawer[$random_x][$random_y] = 1;
        $this->head = [$random_x, $random_y];
        $this->body[] = $this->head;
        //食物
        $this->randomFoods();
        //方向
        $this->direct = $this->initDirect();
        //设置初始移动速度
        $this->setFrequency(self::MAX_FREQUENCY);
    }

    /**
     * @desc 生成画布
     * @user chenlei11
     * @date 2021/11/24
     */
    private function initDrawer(): void {
        //生成画布
        for ($i = 0; $i < $this->length; $i++) {
            $this->drawer[$i] = array_fill(0, $this->length, 0);
        }
    }

    public function setDrawerLength(int $len): void {
        $this->length = $len;
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

    /**
     * @desc 刷新贪吃蛇移动速度
     * @user chenlei11
     * @date 2021/11/24
     */
    public function refreshFrequency(): void {
        if ($this->frequency <= self::MIN_FREQUENCY) {
            $this->frequency = self::MIN_FREQUENCY;
            return;
        }
        $this->frequency = self::MAX_FREQUENCY - (22000 * count($this->body));
    }

    /**
     * @desc 设置蛇移动的放向
     * @user chenlei11
     * @date 2021/11/24
     * @param string $direct
     */
    public function setDirect(string $direct): void {
        $this->direct = $direct;
    }

    /**
     * @desc 设置蛇的移动的速度
     * @user chenlei11
     * @date 2021/11/24
     * @param int $frequency
     */
    public function setFrequency(int $frequency): void {
        $this->frequency = $frequency;
    }

    /**
     * @desc 获取当前蛇运行的速度
     * @user chenlei11
     * @date 2021/11/24
     * @return int
     */
    public function getFrequency(): int {
        return $this->frequency;
    }

    /**
     * @desc 移动蛇的身体
     * @user chenlei11
     * @date 2021/11/24
     */
    public function runDirect(): void {

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

        //超出范围(撞墙),死了,退出
        if (!isset($this->drawer[$x][$y])) {
            $this->tip = 'crush wall, died!';
            $this->die();
        }
        //新的头部
        $head = [$x, $y];
        //如果新的头部,即蛇的下一步是自己的身体,则是吃自己,死了,退出
        if (in_array($head, $this->body)) {
            $this->tip = 'eat self, died!';
            $this->die();
        }
        //把新头部塞到身体的第一个位置
        array_unshift($this->body, $head);
        $this->head = $head;
        //如果新头部不是食物,即只是移动,不增长身体,把身体最后一个元素弹出(模拟移动效果),否则是吃到了食物,生成食物
        if ($head != $this->food) {
            array_pop($this->body);
        } else {
            $this->randomFoods();
        }
    }

    /**
     * @desc 生成食物
     * @user chenlei11
     * @date 2021/11/24
     */
    private function randomFoods(): void {
        $blank = [];
        $len = $this->length;
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

    /**
     * @desc 刷新整个画布
     * @user chenlei11
     * @date 2021/11/24
     */
    public function draw(): void {
        if ($this->draw) {
            $this->clearDrawer();
        }
        for ($i = 0; $i < $this->length; $i++) {
            for ($j = 0; $j < $this->length; $j++) {
                if ([$i, $j] == $this->head) {
                    echo self::HEAD_ICON[$this->direct];
                    continue;
                }
                if (in_array([$i, $j], $this->body)) {
                    if (!empty($this->body_str) && count($this->body) > strlen($this->body_str)) {
                        $key = array_search([$i, $j], $this->body);
                        $icon = substr($this->body_str, $key - 1, 1) ?: self::BODY_ICON;
                        echo strlen($icon) == 1 ? "$icon " : $icon;
                        continue;
                    }
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
        $this->draw = true;
    }

    /**
     * @desc 死了
     * @user chenlei11
     * @date 2021/11/24
     */
    private function die(): void {
        echo $this->tip, PHP_EOL, self::BEST_WISHES, PHP_EOL, 'current speed:' . ($this->frequency / 1000) . 'ms';exit;
    }

    /**
     * @desc 清空画布
     * @user chenlei11
     * @date 2021/11/29
     */
    private function clear(): void {
        system('clear');
    }

    /**
     * @desc 清空画布
     * @user chenlei11
     * @date 2021/11/29
     */
    private function clearDrawer(): void {
        system("tput cuu {$this->length}");
    }

    /**
     * @desc 设置特殊字符
     * @user chenlei11
     * @date 2021/11/30
     * @param string $str
     */
    public function setBodyStr(string $str): void {
        $this->body_str = $str;
    }
}