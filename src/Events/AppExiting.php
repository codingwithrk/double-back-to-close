<?php

namespace Codingwithrk\DoubleBackToClose\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired immediately before the app exits.
 *
 * The user pressed back a second time within the timeout window.
 * Use this event to perform any last-moment cleanup before the process ends.
 */
class AppExiting
{
    use Dispatchable, SerializesModels;

    public function __construct() {}
}
