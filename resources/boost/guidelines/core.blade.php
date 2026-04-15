## codingwithrk/double-back-to-close

A NativePHP Mobile plugin that intercepts the hardware back button (Android) and shows a toast asking the user to press back a second time before the app exits. If the second press happens within the configured timeout the app closes and the AppExiting event is dispatched. Requires `nativephp/mobile-dialog` for PHP-side toast support.

### Installation

```bash
# Install the package
composer require codingwithrk/double-back-to-close

# Publish the plugins provider (first time only)
php artisan vendor:publish --tag=nativephp-plugins-provider

# Register the plugin
php artisan native:plugin:register codingwithrk/double-back-to-close

# Verify registration
php artisan native:plugin:list
```

### PHP Usage (Livewire/Blade)

@verbatim
<code-snippet name="Enable double-back-to-close" lang="php">
use Codingwithrk\DoubleBackToClose\Facades\DoubleBackToClose;

// Enable with defaults (message: "Press back again to exit", timeout: 2000 ms)
DoubleBackToClose::enable();

// Enable with custom message and timeout
DoubleBackToClose::enable('Tap back again to quit', 3000);

// Disable at any time
DoubleBackToClose::disable();

// Update config while active
DoubleBackToClose::configure('Press back to exit', 2500);

// Show a toast via mobile-dialog (use in event handler for custom UI)
DoubleBackToClose::showToast('Press back again to exit');
</code-snippet>
@endverbatim

### Listening for Events

@verbatim
<code-snippet name="Handling back-press events" lang="php">
use Native\Mobile\Attributes\OnNative;
use Codingwithrk\DoubleBackToClose\Events\DoubleBackToCloseTriggered;
use Codingwithrk\DoubleBackToClose\Events\AppExiting;
use Codingwithrk\DoubleBackToClose\Facades\DoubleBackToClose;

// First back press — show a custom dialog toast instead of the native Android toast
#[OnNative(DoubleBackToCloseTriggered::class)]
public function onFirstBackPress(string $message): void
{
    DoubleBackToClose::showToast($message);
}

// Second back press — app is about to exit
#[OnNative(AppExiting::class)]
public function onAppExiting(): void
{
    // Perform last-moment cleanup
}
</code-snippet>
@endverbatim

### Available Methods

- `DoubleBackToClose::enable(string $message, int $timeout)` — register back-press interceptor
- `DoubleBackToClose::disable()` — remove the interceptor
- `DoubleBackToClose::configure(string $message, int $timeout)` — update config while active
- `DoubleBackToClose::showToast(string $message, string $duration)` — show toast via mobile-dialog

### Events

| Event | Payload | Description |
|-------|---------|-------------|
| `DoubleBackToCloseTriggered` | `message: string` | First back press; native Android Toast shown |
| `AppExiting` | _(none)_ | Second back press; app is about to exit |

### JavaScript Usage (Vue/React/Inertia)

@verbatim
<code-snippet name="JavaScript usage" lang="javascript">
import { DoubleBackToClose, Events } from '@codingwithrk/double-back-to-close';
import { on, off } from '@nativephp/native';

// Enable on component mount
await DoubleBackToClose.enable({ message: 'Press back again to exit', timeout: 2000 });

// Listen for first back press
const onTriggered = ({ message }) => console.log('First back press:', message);
on(Events.DoubleBackToCloseTriggered, onTriggered);

// Listen for app exit
const onExiting = () => console.log('App exiting');
on(Events.AppExiting, onExiting);

// Clean up on unmount
off(Events.DoubleBackToCloseTriggered, onTriggered);
off(Events.AppExiting, onExiting);
await DoubleBackToClose.disable();
</code-snippet>
@endverbatim

### Platform Notes

- **Android**: fully supported — intercepts the hardware back button via `OnBackPressedCallback` and shows a native `Toast`.
- **iOS**: configuration is accepted and events are stored; the physical hardware back button does not exist on iOS so the interceptor is a functional no-op. Use `Dialog::toast()` via `showToast()` if you need iOS confirmation UI.
