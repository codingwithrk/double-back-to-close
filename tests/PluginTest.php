<?php

/**
 * Plugin validation tests for DoubleBackToClose.
 *
 * Run with: ./vendor/bin/pest
 */

beforeEach(function () {
    $this->pluginPath = dirname(__DIR__);
    $this->manifestPath = $this->pluginPath . '/nativephp.json';
});

describe('Plugin Manifest', function () {
    it('has a valid nativephp.json file', function () {
        expect(file_exists($this->manifestPath))->toBeTrue();

        $content = file_get_contents($this->manifestPath);
        $manifest = json_decode($content, true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    });

    it('has required fields', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest)->toHaveKeys(['name', 'namespace', 'bridge_functions']);
        expect($manifest['name'])->toBe('codingwithrk/double-back-to-close');
        expect($manifest['namespace'])->toBe('DoubleBackToClose');
    });

    it('has valid bridge functions', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest['bridge_functions'])->toBeArray();

        foreach ($manifest['bridge_functions'] as $function) {
            expect($function)->toHaveKeys(['name']);
            $hasPlatform = isset($function['android']) || isset($function['ios']);
            expect($hasPlatform)->toBeTrue();
        }
    });

    it('declares Enable, Disable and Configure bridge functions', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        $names = array_column($manifest['bridge_functions'], 'name');

        expect($names)->toContain('DoubleBackToClose.Enable');
        expect($names)->toContain('DoubleBackToClose.Disable');
        expect($names)->toContain('DoubleBackToClose.Configure');
    });

    it('declares DoubleBackToCloseTriggered and AppExiting events', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest['events'])->toContain(
            'Codingwithrk\\DoubleBackToClose\\Events\\DoubleBackToCloseTriggered'
        );
        expect($manifest['events'])->toContain(
            'Codingwithrk\\DoubleBackToClose\\Events\\AppExiting'
        );
    });

    it('has valid marketplace metadata', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        if (isset($manifest['keywords'])) {
            expect($manifest['keywords'])->toBeArray();
        }

        if (isset($manifest['category'])) {
            expect($manifest['category'])->toBeString();
        }

        if (isset($manifest['platforms'])) {
            expect($manifest['platforms'])->toBeArray();
            foreach ($manifest['platforms'] as $platform) {
                expect($platform)->toBeIn(['android', 'ios']);
            }
        }
    });
});

describe('Native Code', function () {
    it('has Android Kotlin file', function () {
        $kotlinFile = $this->pluginPath . '/resources/android/DoubleBackToCloseFunctions.kt';

        expect(file_exists($kotlinFile))->toBeTrue();

        $content = file_get_contents($kotlinFile);
        expect($content)->toContain('package com.codingwithrk.plugins.double_back_to_close');
        expect($content)->toContain('object DoubleBackToCloseFunctions');
        expect($content)->toContain('BridgeFunction');
    });

    it('has iOS Swift file', function () {
        $swiftFile = $this->pluginPath . '/resources/ios/DoubleBackToCloseFunctions.swift';

        expect(file_exists($swiftFile))->toBeTrue();

        $content = file_get_contents($swiftFile);
        expect($content)->toContain('enum DoubleBackToCloseFunctions');
        expect($content)->toContain('BridgeFunction');
    });

    it('has matching bridge function classes in native code', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        $kotlinFile = $this->pluginPath . '/resources/android/DoubleBackToCloseFunctions.kt';
        $swiftFile = $this->pluginPath . '/resources/ios/DoubleBackToCloseFunctions.swift';

        $kotlinContent = file_get_contents($kotlinFile);
        $swiftContent = file_get_contents($swiftFile);

        foreach ($manifest['bridge_functions'] as $function) {
            if (isset($function['android'])) {
                $parts = explode('.', $function['android']);
                $className = end($parts);
                expect($kotlinContent)->toContain("class {$className}");
            }

            if (isset($function['ios'])) {
                $parts = explode('.', $function['ios']);
                $className = end($parts);
                expect($swiftContent)->toContain("class {$className}");
            }
        }
    });

    it('Kotlin dispatches DoubleBackToCloseTriggered event', function () {
        $kotlinFile = $this->pluginPath . '/resources/android/DoubleBackToCloseFunctions.kt';
        $content = file_get_contents($kotlinFile);

        expect($content)->toContain('DoubleBackToClose\\\\Events\\\\DoubleBackToCloseTriggered');
    });

    it('Kotlin dispatches AppExiting event', function () {
        $kotlinFile = $this->pluginPath . '/resources/android/DoubleBackToCloseFunctions.kt';
        $content = file_get_contents($kotlinFile);

        expect($content)->toContain('DoubleBackToClose\\\\Events\\\\AppExiting');
    });
});

describe('PHP Classes', function () {
    it('has service provider', function () {
        $file = $this->pluginPath . '/src/DoubleBackToCloseServiceProvider.php';
        expect(file_exists($file))->toBeTrue();

        $content = file_get_contents($file);
        expect($content)->toContain('namespace Codingwithrk\DoubleBackToClose');
        expect($content)->toContain('class DoubleBackToCloseServiceProvider');
    });

    it('has facade', function () {
        $file = $this->pluginPath . '/src/Facades/DoubleBackToClose.php';
        expect(file_exists($file))->toBeTrue();

        $content = file_get_contents($file);
        expect($content)->toContain('namespace Codingwithrk\DoubleBackToClose\Facades');
        expect($content)->toContain('class DoubleBackToClose extends Facade');
    });

    it('has main implementation class', function () {
        $file = $this->pluginPath . '/src/DoubleBackToClose.php';
        expect(file_exists($file))->toBeTrue();

        $content = file_get_contents($file);
        expect($content)->toContain('namespace Codingwithrk\DoubleBackToClose');
        expect($content)->toContain('class DoubleBackToClose');
    });

    it('implementation class has enable, disable, configure, and showToast methods', function () {
        $file = $this->pluginPath . '/src/DoubleBackToClose.php';
        $content = file_get_contents($file);

        expect($content)->toContain('function enable(');
        expect($content)->toContain('function disable(');
        expect($content)->toContain('function configure(');
        expect($content)->toContain('function showToast(');
    });

    it('uses mobile-dialog for showToast', function () {
        $file = $this->pluginPath . '/src/DoubleBackToClose.php';
        $content = file_get_contents($file);

        expect($content)->toContain('Dialog::toast(');
        expect($content)->toContain('Native\Mobile\Facades\Dialog');
    });

    it('has DoubleBackToCloseTriggered event class', function () {
        $file = $this->pluginPath . '/src/Events/DoubleBackToCloseTriggered.php';
        expect(file_exists($file))->toBeTrue();

        $content = file_get_contents($file);
        expect($content)->toContain('class DoubleBackToCloseTriggered');
    });

    it('has AppExiting event class', function () {
        $file = $this->pluginPath . '/src/Events/AppExiting.php';
        expect(file_exists($file))->toBeTrue();

        $content = file_get_contents($file);
        expect($content)->toContain('class AppExiting');
    });
});

describe('Composer Configuration', function () {
    it('has valid composer.json', function () {
        $composerPath = $this->pluginPath . '/composer.json';
        expect(file_exists($composerPath))->toBeTrue();

        $content = file_get_contents($composerPath);
        $composer = json_decode($content, true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE);
        expect($composer['type'])->toBe('nativephp-plugin');
        expect($composer['extra']['nativephp']['manifest'])->toBe('nativephp.json');
    });

    it('requires nativephp/mobile-dialog', function () {
        $composerPath = $this->pluginPath . '/composer.json';
        $composer = json_decode(file_get_contents($composerPath), true);

        expect($composer['require'])->toHaveKey('nativephp/mobile-dialog');
    });
});

describe('Lifecycle Hooks', function () {
    it('has valid hooks configuration', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        if (isset($manifest['hooks'])) {
            expect($manifest['hooks'])->toBeArray();

            $validHooks = ['pre_compile', 'post_compile', 'copy_assets', 'post_build'];
            foreach (array_keys($manifest['hooks']) as $hook) {
                expect($hook)->toBeIn($validHooks);
            }
        }
    });

    it('has copy_assets hook command', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        expect($manifest['hooks']['copy_assets'] ?? null)->not->toBeNull();

        $commandFile = $this->pluginPath . '/src/Commands/CopyAssetsCommand.php';
        expect(file_exists($commandFile))->toBeTrue();
    });

    it('copy_assets command extends NativePluginHookCommand', function () {
        $commandFile = $this->pluginPath . '/src/Commands/CopyAssetsCommand.php';
        $content = file_get_contents($commandFile);

        expect($content)->toContain('extends NativePluginHookCommand');
        expect($content)->toContain('use Native\Mobile\Plugins\Commands\NativePluginHookCommand');
    });

    it('copy_assets command has correct signature', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);
        $expectedSignature = $manifest['hooks']['copy_assets'];

        $commandFile = $this->pluginPath . '/src/Commands/CopyAssetsCommand.php';
        $content = file_get_contents($commandFile);

        expect($content)->toContain('$signature = \'' . $expectedSignature . '\'');
    });

    it('copy_assets command has platform-specific methods', function () {
        $commandFile = $this->pluginPath . '/src/Commands/CopyAssetsCommand.php';
        $content = file_get_contents($commandFile);

        expect($content)->toContain('$this->isAndroid()');
        expect($content)->toContain('$this->isIos()');
    });

    it('has valid assets configuration', function () {
        $manifest = json_decode(file_get_contents($this->manifestPath), true);

        if (isset($manifest['assets'])) {
            expect($manifest['assets'])->toBeArray();

            if (isset($manifest['assets']['android'])) {
                expect($manifest['assets']['android'])->toBeArray();
            }

            if (isset($manifest['assets']['ios'])) {
                expect($manifest['assets']['ios'])->toBeArray();
            }
        }
    });
});
