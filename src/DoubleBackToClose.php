<?php

namespace Codingwithrk\DoubleBackToClose;

use Native\Mobile\Facades\Dialog;

class DoubleBackToClose
{
    /**
     * Enable the double-back-to-close feature.
     *
     * On the first back press the native toast is shown. If the user presses
     * back a second time within $timeout milliseconds the app exits and the
     * AppExiting event is dispatched. Otherwise the state resets.
     *
     * @param string $message  Toast text shown on first back press
     * @param int    $timeout  Reset window in milliseconds (default: 2000)
     */
    public function enable(string $message = 'Press back again to exit', int $timeout = 2000): void
    {
        if (function_exists('nativephp_call')) {
            nativephp_call('DoubleBackToClose.Enable', json_encode([
                'message' => $message,
                'timeout' => $timeout,
            ]));
        }
    }

    /**
     * Disable the double-back-to-close feature and restore default back behaviour.
     */
    public function disable(): void
    {
        if (function_exists('nativephp_call')) {
            nativephp_call('DoubleBackToClose.Disable', '{}');
        }
    }

    /**
     * Update the toast message and/or timeout while the feature is already enabled.
     *
     * @param string $message  New toast message
     * @param int    $timeout  New reset window in milliseconds
     */
    public function configure(string $message, int $timeout = 2000): void
    {
        if (function_exists('nativephp_call')) {
            nativephp_call('DoubleBackToClose.Configure', json_encode([
                'message' => $message,
                'timeout' => $timeout,
            ]));
        }
    }

    /**
     * Show a toast message via the mobile-dialog plugin.
     *
     * Use this in a Livewire #[OnNative] handler to surface the
     * DoubleBackToCloseTriggered event as a dialog toast instead of
     * relying solely on the native Android Toast.
     *
     * @param string $message  Message to display
     * @param string $duration 'short' (2 s) or 'long' (4 s)
     */
    public function showToast(string $message, string $duration = 'short'): void
    {
        Dialog::toast($message, $duration);
    }
}
