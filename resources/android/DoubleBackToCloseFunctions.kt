package com.codingwithrk.plugins.double_back_to_close

import android.os.Handler
import android.os.Looper
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.fragment.app.FragmentActivity
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse
import com.nativephp.mobile.utils.NativeActionCoordinator
import org.json.JSONObject

/**
 * Shared mutable state for the back-press interceptor.
 *
 * Kept in a private singleton so all three bridge function classes share the
 * same handler, runnable, and callback reference without leaking state between
 * Enable → Configure → Disable cycles.
 */
private object BackPressState {
    var message: String = "Press back again to exit"
    var timeout: Long = 2000L
    var backPressedOnce: Boolean = false
    var registeredCallback: OnBackPressedCallback? = null

    private val handler = Handler(Looper.getMainLooper())
    private var resetRunnable: Runnable? = null

    fun scheduleReset() {
        cancelReset()
        resetRunnable = Runnable { backPressedOnce = false }
        handler.postDelayed(resetRunnable!!, timeout)
    }

    fun cancelReset() {
        resetRunnable?.let { handler.removeCallbacks(it) }
        resetRunnable = null
    }

    fun clear() {
        cancelReset()
        registeredCallback?.remove()
        registeredCallback = null
        backPressedOnce = false
    }
}

object DoubleBackToCloseFunctions {

    /**
     * Register the back-press interceptor.
     *
     * Parameters:
     *   message  – Toast text displayed on first back press (default: "Press back again to exit")
     *   timeout  – Milliseconds before the first-press state resets (default: 2000)
     */
    class Enable(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            BackPressState.message = parameters["message"] as? String ?: "Press back again to exit"
            BackPressState.timeout = (parameters["timeout"] as? Number)?.toLong() ?: 2000L
            BackPressState.backPressedOnce = false

            activity.runOnUiThread {
                // Remove any previously registered callback before adding a fresh one.
                BackPressState.clear()

                val callback = object : OnBackPressedCallback(true) {
                    override fun handleOnBackPressed() {
                        if (BackPressState.backPressedOnce) {
                            // Second press within timeout — exit the app.
                            BackPressState.cancelReset()

                            NativeActionCoordinator.dispatchEvent(
                                activity,
                                "Codingwithrk\\DoubleBackToClose\\Events\\AppExiting",
                                "{}"
                            )

                            // Disable this callback so the system handles back normally,
                            // then immediately finish the activity.
                            isEnabled = false
                            activity.finish()
                        } else {
                            // First press — show toast and start the reset timer.
                            BackPressState.backPressedOnce = true

                            Toast.makeText(
                                activity,
                                BackPressState.message,
                                Toast.LENGTH_SHORT
                            ).show()

                            val payload = JSONObject().apply {
                                put("message", BackPressState.message)
                            }.toString()

                            NativeActionCoordinator.dispatchEvent(
                                activity,
                                "Codingwithrk\\DoubleBackToClose\\Events\\DoubleBackToCloseTriggered",
                                payload
                            )

                            BackPressState.scheduleReset()
                        }
                    }
                }

                BackPressState.registeredCallback = callback
                activity.onBackPressedDispatcher.addCallback(activity, callback)
            }

            return BridgeResponse.success(mapOf("enabled" to true))
        }
    }

    /**
     * Remove the back-press interceptor and restore default navigation behaviour.
     */
    class Disable(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            activity.runOnUiThread {
                BackPressState.clear()
            }

            return BridgeResponse.success(mapOf("enabled" to false))
        }
    }

    /**
     * Update the toast message and/or timeout while the interceptor is already active.
     *
     * Parameters:
     *   message  – New toast text
     *   timeout  – New reset window in milliseconds
     */
    class Configure(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            if (parameters.containsKey("message")) {
                BackPressState.message = parameters["message"] as? String ?: BackPressState.message
            }
            if (parameters.containsKey("timeout")) {
                BackPressState.timeout =
                    (parameters["timeout"] as? Number)?.toLong() ?: BackPressState.timeout
            }

            return BridgeResponse.success(
                mapOf(
                    "message" to BackPressState.message,
                    "timeout" to BackPressState.timeout
                )
            )
        }
    }
}
