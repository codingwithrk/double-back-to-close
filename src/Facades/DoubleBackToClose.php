<?php

namespace Codingwithrk\DoubleBackToClose\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void enable(string $message = 'Press back again to exit', int $timeout = 2000)
 * @method static void disable()
 * @method static void configure(string $message, int $timeout = 2000)
 * @method static void showToast(string $message, string $duration = 'short')
 *
 * @see \Codingwithrk\DoubleBackToClose\DoubleBackToClose
 */
class DoubleBackToClose extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Codingwithrk\DoubleBackToClose\DoubleBackToClose::class;
    }
}
