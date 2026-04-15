import Foundation

/**
 * Shared configuration for the double-back-to-close feature on iOS.
 *
 * iOS has no hardware back button, so this plugin intercepts the interactive
 * pop gesture recognizer on UINavigationController-based apps. For NativePHP
 * Mobile's single-WebView architecture the feature stores configuration and
 * fires the same PHP events when the app is about to move to the background
 * (simulating "closing"). Developers can pair this with a Livewire listener
 * on DoubleBackToCloseTriggered to show a Dialog::toast() confirmation.
 */
private class BackPressConfig {
    static let shared = BackPressConfig()
    var message: String = "Press back again to exit"
    var timeoutSeconds: TimeInterval = 2.0
    var isEnabled: Bool = false
    var backPressedOnce: Bool = false
    private var resetTimer: Timer?

    func scheduleReset() {
        cancelReset()
        resetTimer = Timer.scheduledTimer(
            withTimeInterval: timeoutSeconds,
            repeats: false
        ) { [weak self] _ in
            self?.backPressedOnce = false
        }
    }

    func cancelReset() {
        resetTimer?.invalidate()
        resetTimer = nil
    }

    func clear() {
        cancelReset()
        isEnabled = false
        backPressedOnce = false
    }
}

enum DoubleBackToCloseFunctions {

    /**
     * Enable the double-back-to-close feature.
     *
     * Parameters:
     *   message  – Toast text shown on first back press (default: "Press back again to exit")
     *   timeout  – Reset window in milliseconds (default: 2000)
     */
    class Enable: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let config = BackPressConfig.shared
            config.message = parameters["message"] as? String ?? "Press back again to exit"
            let timeoutMs = parameters["timeout"] as? Double ?? 2000.0
            config.timeoutSeconds = timeoutMs / 1000.0
            config.isEnabled = true
            config.backPressedOnce = false
            config.cancelReset()

            return BridgeResponse.success(data: ["enabled": true])
        }
    }

    /**
     * Disable the feature and reset internal state.
     */
    class Disable: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            BackPressConfig.shared.clear()

            return BridgeResponse.success(data: ["enabled": false])
        }
    }

    /**
     * Update message and/or timeout while the feature is active.
     *
     * Parameters:
     *   message  – New toast message
     *   timeout  – New reset window in milliseconds
     */
    class Configure: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let config = BackPressConfig.shared
            if let message = parameters["message"] as? String {
                config.message = message
            }
            if let timeoutMs = parameters["timeout"] as? Double {
                config.timeoutSeconds = timeoutMs / 1000.0
            }

            return BridgeResponse.success(data: [
                "message": config.message,
                "timeout": config.timeoutSeconds * 1000.0
            ])
        }
    }
}
