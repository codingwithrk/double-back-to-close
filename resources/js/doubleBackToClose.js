/**
 * DoubleBackToClose Plugin for NativePHP Mobile
 *
 * Mirrors the Android double-back-to-close pattern popularised by the Flutter
 * package of the same name. On first back press a toast is shown; pressing back
 * again within the timeout exits the app.
 *
 * @example
 * import { DoubleBackToClose, Events } from '@codingwithrk/double-back-to-close';
 * import { on } from '@nativephp/native';
 *
 * // Enable on mount
 * await DoubleBackToClose.enable({ message: 'Press back again to exit', timeout: 2000 });
 *
 * // Listen for first back press
 * on(Events.DoubleBackToCloseTriggered, ({ message }) => console.log(message));
 *
 * // Listen for app exit
 * on(Events.AppExiting, () => console.log('Goodbye!'));
 */

const baseUrl = '/_native/api/call';

async function bridgeCall(method, params = {}) {
    const response = await fetch(baseUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ method, params })
    });

    const result = await response.json();

    if (result.status === 'error') {
        throw new Error(result.message || 'Native call failed');
    }

    const nativeResponse = result.data;
    if (nativeResponse && nativeResponse.data !== undefined) {
        return nativeResponse.data;
    }

    return nativeResponse;
}

/**
 * Enable the double-back-to-close feature.
 *
 * @param {Object} options
 * @param {string} [options.message='Press back again to exit'] - Toast text on first back press
 * @param {number} [options.timeout=2000] - Reset window in milliseconds
 * @returns {Promise<{ enabled: boolean }>}
 */
async function enable(options = {}) {
    return bridgeCall('DoubleBackToClose.Enable', {
        message: options.message ?? 'Press back again to exit',
        timeout: options.timeout ?? 2000
    });
}

/**
 * Disable the feature and restore default back behaviour.
 *
 * @returns {Promise<{ enabled: boolean }>}
 */
async function disable() {
    return bridgeCall('DoubleBackToClose.Disable');
}

/**
 * Update the toast message and/or timeout while the feature is active.
 *
 * @param {Object} options
 * @param {string} [options.message] - New toast message
 * @param {number} [options.timeout] - New reset window in milliseconds
 * @returns {Promise<{ message: string, timeout: number }>}
 */
async function configure(options = {}) {
    return bridgeCall('DoubleBackToClose.Configure', options);
}

// PascalCase namespace export
export const DoubleBackToClose = {
    enable,
    disable,
    configure
};

// Fully-qualified event class names — use these with on()/off() instead of hardcoded strings
export const Events = {
    DoubleBackToCloseTriggered: 'Codingwithrk\\DoubleBackToClose\\Events\\DoubleBackToCloseTriggered',
    AppExiting: 'Codingwithrk\\DoubleBackToClose\\Events\\AppExiting'
};

export default DoubleBackToClose;
