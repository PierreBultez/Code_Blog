# Bug Report: Alpine.js CSP build triggers Chrome "Shared Storage API deprecated" warning

**Package :** `@alpinejs/csp` (Alpine.js CSP build)
**Repository :** [alpinejs/alpine](https://github.com/alpinejs/alpine) (MIT License)
**Fichier source :** `packages/csp/src/parser.js`
**Severite :** Low (deprecation warning, no functional impact)
**Impact :** Baisse du score Lighthouse "Best Practices" de 100 a 96

---

## Description du bug

Le build CSP d'Alpine.js enumere toutes les proprietes de `globalThis` (= `window`) pour construire un ensemble de globales connues. Ce mecanisme permet au parseur CSP d'evaluer les expressions Alpine sans recourir a `eval()` ou `new Function()`.

Lors de cette enumeration, le simple **acces en lecture** a `window.sharedStorage` suffit a declencher l'avertissement de deprecation de Chrome :

```
[Deprecation] Shared Storage API is deprecated and will be removed in a future release.
```

Chrome traite l'acces a cette propriete comme une "utilisation" de l'API Shared Storage, meme si la valeur n'est jamais exploitee.

---

## Pourquoi ce bug existe

Le parseur CSP d'Alpine utilise `Object.getOwnPropertyNames(globalThis)` pour lister toutes les proprietes de `window`, puis accede a chacune via `globalThis[key]` pour les ajouter a un `Set` de globales connues.

Le code filtrait deja `styleMedia` (une autre propriete de `window` qui pose probleme), mais pas `sharedStorage`.

### Code concerne

**Fichier :** `packages/csp/src/parser.js` (ligne ~52-56 dans la version actuelle)

```js
Object.getOwnPropertyNames(globalThis).forEach((key) => {
    if (key === "styleMedia")
        return;

    globals.add(globalThis[key]);
});
```

Le probleme est a la ligne `globals.add(globalThis[key])` : quand `key === "sharedStorage"`, l'acces a `globalThis["sharedStorage"]` declenche l'avertissement Chrome.

---

## Quand le bug se manifeste

Le bug apparait quand **toutes** ces conditions sont reunies :

1. **Alpine.js est charge via le build CSP** (`@alpinejs/csp` au lieu de `alpinejs`)
2. **Le navigateur est Chrome/Chromium** (version 126+, quand `sharedStorage` est devenu une propriete de `window`)
3. **La page utilise Alpine** (le parseur s'initialise au chargement)

Le bug **ne se manifeste PAS** avec :
- Le build standard d'Alpine.js (qui utilise `eval()`/`new Function()` au lieu du parseur CSP)
- Firefox ou Safari (qui n'exposent pas `window.sharedStorage`)
- Les anciennes versions de Chrome (avant l'ajout de l'API Shared Storage)

---

## Comment reproduire

### Prerequis

- Chrome/Chromium >= 126
- Node.js et npm

### Etapes

1. **Creer un projet minimal avec Alpine CSP :**

```bash
mkdir alpine-csp-bug && cd alpine-csp-bug
npm init -y
npm install @alpinejs/csp
```

2. **Creer un fichier HTML :**

```html
<!DOCTYPE html>
<html>
<head>
    <title>Alpine CSP sharedStorage Bug</title>
</head>
<body>
    <div x-data="{ count: 0 }">
        <button x-on:click="count = count + 1">
            Clicks: <span x-text="count">0</span>
        </button>
    </div>
    <script type="module">
        import Alpine from '@alpinejs/csp';
        window.Alpine = Alpine;
        Alpine.start();
    </script>
</body>
</html>
```

3. **Servir avec un serveur local :**

```bash
npx serve .
```

4. **Ouvrir dans Chrome et verifier :**
   - Ouvrir les DevTools (F12) > Console
   - Observer l'avertissement : `[Deprecation] Shared Storage API is deprecated and will be removed in a future release.`
   - Ouvrir l'onglet "Issues" des DevTools pour voir le detail

5. **Lancer un audit Lighthouse :**
   - DevTools > Lighthouse > "Best Practices"
   - Le score sera inferieur a 100 a cause de cet avertissement

### Resultat attendu

Aucun avertissement de deprecation ne devrait apparaitre, car Alpine n'utilise pas l'API Shared Storage — il ne fait qu'enumerer les globales.

### Resultat observe

Chrome detecte l'acces a `window.sharedStorage` et emet un avertissement de deprecation, faisant baisser le score Lighthouse.

---

## Correctif propose

### Approche

Ajouter `"sharedStorage"` a la liste de filtrage existante, de la meme maniere que `"styleMedia"` est deja filtre. C'est une approche coherente avec le pattern existant.

### Diff

```diff
--- a/packages/csp/src/parser.js
+++ b/packages/csp/src/parser.js
@@ -52,7 +52,7 @@
 Object.getOwnPropertyNames(globalThis).forEach((key) => {
-    if (key === "styleMedia")
+    if (key === "styleMedia" || key === "sharedStorage")
         return;

     globals.add(globalThis[key]);
 });
```

### Code apres correction

```js
Object.getOwnPropertyNames(globalThis).forEach((key) => {
    if (key === "styleMedia" || key === "sharedStorage")
        return;

    globals.add(globalThis[key]);
});
```

---

## Approche alternative (plus robuste)

Plutot que d'ajouter chaque propriete problematique a une liste de filtrage manuelle, une approche plus defensive serait d'envelopper l'acces dans un `try/catch` ou d'utiliser un getter safe :

```js
Object.getOwnPropertyNames(globalThis).forEach((key) => {
    try {
        globals.add(globalThis[key]);
    } catch {
        // Skip properties that throw on access
    }
});
```

Ou utiliser `Reflect.get` avec un wrapper qui detecte les getters a effets de bord :

```js
const SKIP_GLOBALS = new Set(["styleMedia", "sharedStorage"]);

Object.getOwnPropertyNames(globalThis).forEach((key) => {
    if (SKIP_GLOBALS.has(key)) return;
    globals.add(globalThis[key]);
});
```

L'avantage de la `Set` est la performance O(1) et l'extensibilite facile si d'autres proprietes problematiques emergent a l'avenir.

---

## Template de PR pour alpinejs/alpine

### Titre

```
fix(csp): skip sharedStorage when enumerating globalThis properties
```

### Description

```markdown
## Problem

The CSP parser enumerates all `globalThis` properties to build a set of known
globals. Accessing `window.sharedStorage` triggers Chrome's deprecation warning:

> [Deprecation] Shared Storage API is deprecated and will be removed in a future release.

This causes Lighthouse "Best Practices" score to drop from 100 to 96.

This is the same class of issue that was already fixed for `styleMedia`.

## Solution

Add `"sharedStorage"` to the skip list in the globalThis enumeration loop,
alongside the already-skipped `"styleMedia"`.

## Testing

- Before: Chrome DevTools shows deprecation warning on any page using Alpine CSP
- After: No deprecation warning, Lighthouse Best Practices score returns to 100
- No functional impact: `sharedStorage` is not used by Alpine or typical web apps
```

---

## Workarounds actuels

En attendant la correction upstream, deux patchs peuvent etre appliques :

### 1. Plugin Vite (pour `@alpinejs/csp` via npm)

```js
// vite.config.js
{
    name: 'patch-alpine-csp-globals',
    transform(code, id) {
        if (id.includes('@alpinejs/csp') || id.includes('@alpinejs_csp')) {
            return code.replace(
                'if (key === "styleMedia")',
                'if (key === "styleMedia" || key === "sharedStorage")'
            );
        }
    },
},
```

**Note :** En mode dev, Vite pre-bundle les dependances sous le nom `@alpinejs_csp` (underscore au lieu de slash). Le pattern de detection doit couvrir les deux formes.

### 2. Script post-publication (pour le bundle Livewire)

Livewire embarque son propre build d'Alpine CSP. Apres publication des assets (`artisan livewire:publish --assets`), un script PHP applique le meme patch :

```php
<?php
foreach (glob('public/vendor/livewire/livewire.csp*.js') as $file) {
    $content = file_get_contents($file);
    $patched = str_replace(
        'if (key === "styleMedia")',
        'if (key === "styleMedia" || key === "sharedStorage")',
        $content
    );
    if ($patched !== $content) {
        file_put_contents($file, $patched);
    }
}
```

---

## References

- [Alpine.js GitHub](https://github.com/alpinejs/alpine)
- [Alpine CSP package](https://github.com/alpinejs/alpine/tree/main/packages/csp)
- [Chrome Shared Storage API](https://developer.chrome.com/docs/privacy-sandbox/shared-storage/)
- [Lighthouse Best Practices audit](https://developer.chrome.com/docs/lighthouse/best-practices/)
