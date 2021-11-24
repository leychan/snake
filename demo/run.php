<?php

require __DIR__ . '/../vendor/autoload.php';

use \snake\Snake;

$snake = new Snake();
$snake->setDrawerLength(20);
$snake->init();
$snake->draw();

//非阻塞获取命令行输入
stream_set_blocking(STDIN, false);
readline_callback_handler_install('', function(){});
while (true) {
    //刷新速度
    $snake->refreshFrequency();
    //设置速度
    $frequency = $snake->getFrequency();
    usleep($frequency);
    //从命令行获取方向输入
    $key = stream_get_contents(STDIN, 1);
    if (!empty($key) && isset(Snake::KEYMAP[$key])) {
        $snake->setDirect(Snake::KEYMAP[$key]);
    }
    //贪吃蛇按照方向运行
    $snake->runDirect();
    //刷新命令行
    $snake->draw();
    if ($key == Snake::QUIT_KEY) {
        exit;
    }
}

