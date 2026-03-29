<?php

/**
 * Patch Livewire's CSP Alpine build to skip Chrome's deprecated sharedStorage API.
 *
 * Alpine CSP's parser enumerates all globalThis properties to build a set of known
 * globals. Accessing window.sharedStorage triggers Chrome's "Shared Storage API is
 * deprecated" warning. This patch adds sharedStorage to the skip list alongside
 * the already-skipped styleMedia property.
 *
 * This script runs automatically via composer post-update-cmd after Livewire assets
 * are published. The same fix is applied to @alpinejs/csp via a Vite plugin in
 * vite.config.js.
 */

$patched = 0;

foreach (glob(__DIR__.'/../public/vendor/livewire/livewire.csp*.js') as $file) {
    $content = file_get_contents($file);
    $original = $content;

    $content = str_replace(
        'if (key === "styleMedia")',
        'if (key === "styleMedia" || key === "sharedStorage")',
        $content
    );

    if ($content !== $original) {
        file_put_contents($file, $content);
        $patched++;
    }
}

echo $patched > 0
    ? "Patched {$patched} Livewire CSP file(s) to skip sharedStorage global.\n"
    : "Livewire CSP files already patched or not found.\n";
