<?php

namespace Codingwithrk\DoubleBackToClose\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired on the first back press.
 *
 * The timer has started — if the user presses back again within the configured
 * timeout the app will exit and AppExiting will be dispatched instead.
 */
class DoubleBackToCloseTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $message
    ) {}
}
