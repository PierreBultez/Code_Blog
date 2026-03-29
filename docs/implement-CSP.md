# Implementation Content Security Policy (CSP)

**Date :** 2026-03-29
**Contexte :** Suite a l'audit OWASP ([owasp-audit.md](owasp-audit.md)), implementation d'une politique CSP stricte basee sur des nonces pour proteger contre les attaques XSS.
**Tests :** 118 passes, 0 echecs apres implementation

---

## Sommaire

1. [Architecture CSP choisie](#architecture-csp-choisie)
2. [Middleware SecurityHeaders](#middleware-securityheaders)
3. [Mode CSP-safe Alpine/Livewire](#mode-csp-safe-alpinelivewire)
4. [Refactoring des expressions Alpine](#refactoring-des-expressions-alpine)
5. [Integration Flux UI](#integration-flux-ui)
6. [Gestion du Vite dev server](#gestion-du-vite-dev-server)
7. [Fichiers modifies](#fichiers-modifies)
8. [Directives CSP detaillees](#directives-csp-detaillees)
9. [Points d'attention pour les futurs developpements](#points-dattention-pour-les-futurs-developpements)

---

## Architecture CSP choisie

### Strategie nonce + strict-dynamic

La CSP utilise des **nonces** (tokens aleatoires uniques par requete) plutot que des hashes ou des whitelists d'URLs.

```
script-src 'nonce-{random}' 'strict-dynamic'
```

**Pourquoi cette approche :**

- **Nonce** : chaque requete HTTP genere un token unique via `Vite::useCspNonce()`. Seuls les scripts et styles portant ce nonce sont executes. Un attaquant ne peut pas deviner le nonce car il change a chaque requete.
- **`strict-dynamic`** : les scripts charges dynamiquement par un script nonce (imports Vite, TinyMCE) sont automatiquement autorises. Cela evite de devoir lister chaque URL de script dans la CSP.
- **Pas de `unsafe-inline`** dans `script-src` : les scripts inline sans nonce sont bloques, ce qui empeche l'execution de scripts injectes via XSS.
- **Pas de `unsafe-eval`** : l'utilisation du build CSP d'Alpine.js evite tout recours a `eval()` ou `new Function()`.

### Pourquoi `unsafe-inline` dans style-src uniquement

`style-src` contient `'unsafe-inline'` car :
- Alpine.js et Livewire manipulent des styles inline (`x-show` ajoute `display: none`, etc.)
- Flux UI injecte des styles dynamiques pour ses composants
- L'injection CSS est beaucoup moins dangereuse que l'injection JS (pas d'execution de code)

---

## Middleware SecurityHeaders

**Fichier :** `app/Http/Middleware/SecurityHeaders.php`

Le middleware existant a ete enrichi pour gerer la CSP :

```php
Vite::useCspNonce();        // Genere le nonce AVANT le rendu Blade
$response = $next($request); // Blade peut utiliser Vite::cspNonce()
// ... ajout du header CSP APRES le rendu
```

**Points cles :**
- `Vite::useCspNonce()` est appele **avant** `$next($request)` pour que le nonce soit disponible dans les templates Blade (via `{{ Vite::cspNonce() }}`)
- Le header CSP est ajoute **apres** le rendu pour inclure le nonce genere
- `@vite()` ajoute automatiquement l'attribut `nonce` sur les tags `<script>` et `<style>` qu'il genere
- Livewire detecte automatiquement le nonce via `Vite::cspNonce()` et l'ajoute a ses scripts injectes

---

## Mode CSP-safe Alpine/Livewire

**Fichier :** `config/livewire.php` — `'csp_safe' => true`

### Deux instances Alpine distinctes

Le projet utilise Alpine.js de deux manieres differentes :

1. **Pages publiques** (home, articles, a-propos) : Alpine est charge depuis npm via `resources/js/public.js`. Le package `@alpinejs/csp` remplace `alpinejs` standard.
2. **Pages admin/auth** (dashboard, login, settings) : Alpine est fourni par Livewire/Flux. Le mode `csp_safe => true` fait que Livewire bundle le build CSP d'Alpine.

### Detection automatique dans public.js

Pour eviter le conflit de deux instances Alpine sur les pages publiques qui contiennent aussi des composants Livewire (ex: formulaire de commentaire sur un article), `public.js` detecte la presence de Livewire :

```js
// Composants enregistres via alpine:init — fonctionne avec TOUTE source Alpine
document.addEventListener('alpine:init', () => {
    Alpine.data('animateCounter', ...);
    Alpine.data('darkModeToggle', ...);
});

// Charge Alpine depuis npm uniquement si Livewire ne le fournit pas deja
if (!document.querySelector('[wire\\:id], [wire\\:snapshot]')) {
    Promise.all([
        import('@alpinejs/csp'),
        import('@alpinejs/intersect'),
    ]).then(([{ default: Alpine }, { default: intersect }]) => {
        Alpine.plugin(intersect);
        window.Alpine = Alpine;
        Alpine.start();
    });
}
```

**Comment ca fonctionne :**
- Les attributs `wire:id` sont rendus cote serveur par Livewire (presents dans le HTML initial)
- `public.js` est un `type="module"` (differe), il s'execute apres le parsing HTML complet
- L'evenement `alpine:init` est dispatche par Alpine juste avant son demarrage, quelle que soit sa source (npm ou Livewire)

---

## Refactoring des expressions Alpine

Le build CSP d'Alpine utilise un parseur custom au lieu de `eval()`/`new Function()`. Ce parseur gere les expressions simples mais **ne supporte pas** :
- Les declarations de fonctions (`function foo() {}`)
- Les arrow functions (`() => {}`)
- `async/await`, `try/catch`
- Les IIFE (`(function() {})()`), `new Constructor()`
- Les template literals (`` ` ` ``)

### Expressions extraites (pages publiques)

**Fichier JS :** `resources/js/public.js`

| Composant | Avant (inline) | Apres (Alpine.data) |
|-----------|----------------|---------------------|
| Compteurs anime (home) | IIFE avec `requestAnimationFrame` dans `x-intersect` | `Alpine.data('animateCounter', (target) => ({...}))` |
| Toggle dark mode (navbar) | `$watch` avec arrow fn dans `x-init` | `Alpine.data('darkModeToggle', () => ({...}))` |

### Expressions extraites (pages admin/auth)

**Fichier JS :** `resources/js/app.js`

| Composant | Avant (inline) | Apres (Alpine.data) |
|-----------|----------------|---------------------|
| Action message | `@this.on()` avec arrow fn et `setTimeout` | `Alpine.data('actionMessage', (eventName) => ({...}))` |
| Copie presse-papier (2FA setup) | `async copy()` avec `try/catch` | `Alpine.data('clipboardCopy', () => ({...}))` |
| Challenge 2FA | Methode `toggleInput()` avec `$nextTick` arrow fn | `Alpine.data('twoFactorChallenge', (hasRecoveryError) => ({...}))` |
| Editeur TinyMCE | Init complete avec Promise, fetch, function declaration | `Alpine.data('tinyMceEditor', (wireModelName) => ({...}))` |

### Principe general

La logique complexe (arrow functions, async, Promise) est deplacee dans des fichiers JS externes (`public.js`, `app.js`). Ces fichiers ne sont **pas** soumis aux restrictions du parseur CSP Alpine — seules les expressions dans les attributs HTML (`x-data`, `x-init`, `@click`) doivent etre simples.

**Exemple — avant :**
```blade
<div x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
     x-init="$watch('dark', val => { localStorage.setItem('theme', val ? 'dark' : 'light'); ... })">
```

**Exemple — apres :**
```blade
<button x-data="darkModeToggle" x-on:click="toggle()">
```

---

## Integration Flux UI

Flux UI ne detecte pas automatiquement `Vite::cspNonce()` dans ses directives `@fluxScripts` et `@fluxAppearance`. Le nonce doit etre passe explicitement :

```blade
{{-- partials/head.blade.php --}}
@fluxAppearance(['nonce' => Vite::cspNonce()])

{{-- layouts/app/sidebar.blade.php et layouts/auth/simple.blade.php --}}
@fluxScripts(['nonce' => Vite::cspNonce()])
```

**Note :** Livewire, lui, detecte automatiquement le nonce via `Vite::cspNonce()` dans sa classe `FrontendAssets`. Ce n'est que Flux qui necessite le passage explicite — c'est un ecart dans l'implementation de Flux v2 par rapport a Livewire v4.

---

## Gestion du Vite dev server

En developpement, Vite utilise un serveur HMR sur `http://127.0.0.1:5173`. Le middleware detecte automatiquement sa presence via le fichier `public/hot` (cree par `npm run dev`) et ajoute l'origine du dev server aux directives CSP concernees :

- `style-src` : ajoute l'URL du dev server pour les CSS servies par Vite
- `connect-src` : ajoute l'URL HTTP et WebSocket (`ws://`) pour le HMR

En production, le fichier `public/hot` n'existe pas et ces origines ne sont pas ajoutees.

### Remplacement des handlers onload sur les fonts

Les balises `<link>` pour Google Fonts utilisaient `onload="this.media='all'"` pour le chargement async. Les inline event handlers sont bloques par la CSP (ils ne peuvent pas recevoir de nonce). Le chargement async est desormais gere dans `public.js` via un attribut `data-async-font` :

```html
<!-- Avant -->
<link href="..." rel="stylesheet" media="print" onload="this.media='all'">

<!-- Apres -->
<link href="..." rel="stylesheet" media="print" data-async-font>
```

```js
// public.js
document.querySelectorAll('link[data-async-font]').forEach(link => {
    if (link.sheet) { link.media = 'all'; }
    else { link.addEventListener('load', () => { link.media = 'all'; }); }
});
```

---

## Fichiers modifies

### Infrastructure
| Fichier | Modification |
|---------|-------------|
| `app/Http/Middleware/SecurityHeaders.php` | Header CSP avec nonces, detection Vite dev server |
| `config/livewire.php` | `csp_safe => true` |
| `package.json` | Ajout `@alpinejs/csp` |

### JavaScript
| Fichier | Modification |
|---------|-------------|
| `resources/js/public.js` | Alpine CSP build, `alpine:init`, detection Livewire, composants publics, font loading async |
| `resources/js/app.js` | Composants Alpine admin (actionMessage, clipboardCopy, twoFactorChallenge, tinyMceEditor) |

### Templates Blade — nonces
| Fichier | Modification |
|---------|-------------|
| `resources/views/layouts/public.blade.php` | Nonce sur script dark mode, `data-async-font` sur fonts |
| `resources/views/partials/head.blade.php` | `@fluxAppearance(['nonce' => Vite::cspNonce()])` |
| `resources/views/layouts/app/sidebar.blade.php` | `@fluxScripts(['nonce' => Vite::cspNonce()])` |
| `resources/views/layouts/auth/simple.blade.php` | `@fluxScripts(['nonce' => Vite::cspNonce()])` |

### Templates Blade — refactoring Alpine
| Fichier | Modification |
|---------|-------------|
| `resources/views/home.blade.php` | `x-data="animateCounter(...)"` + `x-intersect.once="start()"` |
| `resources/views/components/public/navbar.blade.php` | `x-data="darkModeToggle"` + `x-on:click="toggle()"` |
| `resources/views/components/action-message.blade.php` | `x-data="actionMessage('...')"` |
| `resources/views/components/tiny-mce.blade.php` | `x-data="tinyMceEditor('...')"` + data attributes |
| `resources/views/pages/auth/two-factor-challenge.blade.php` | `x-data="twoFactorChallenge(...)"` |
| `resources/views/pages/settings/⚡two-factor-setup-modal.blade.php` | `x-data="clipboardCopy"` + `$refs.setupKey` |

---

## Directives CSP detaillees

```
default-src 'self'
```
Seules les ressources du meme domaine sont autorisees par defaut.

```
script-src 'nonce-{random}' 'strict-dynamic'
```
Scripts autorises uniquement par nonce. `strict-dynamic` etend la confiance aux scripts charges dynamiquement par un script nonce.

```
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net
```
Styles du meme domaine + inline (necessaire pour Alpine/Livewire/Flux) + Google Fonts + Bunny Fonts.

```
font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net
```
Polices du meme domaine + serveurs de fonts Google et Bunny.

```
img-src 'self' data:
```
Images du meme domaine + data URIs (utilises par TinyMCE pour les previews).

```
connect-src 'self'
```
Requetes AJAX/fetch uniquement vers le meme domaine. (Elargi au dev server Vite en local.)

```
frame-src 'none'
```
Aucun iframe autorise.

```
object-src 'none'
```
Aucun plugin (Flash, Java) autorise.

```
base-uri 'self'
```
Empeche l'injection d'un `<base>` malveillant.

```
form-action 'self'
```
Les formulaires ne peuvent poster que vers le meme domaine.

---

## Points d'attention pour les futurs developpements

### Ajout de scripts inline

Tout nouveau `<script>` inline doit porter l'attribut nonce :
```blade
<script nonce="{{ Vite::cspNonce() }}">
    // ...
</script>
```

### Expressions Alpine dans les attributs HTML

Avec le mode CSP-safe, les expressions dans `x-data`, `x-init`, `@click`, etc. doivent rester simples :
- **OK :** appels de methodes, acces proprietes, ternaires, comparaisons
- **NON :** arrow functions, declarations de fonctions, async/await, Promise, IIFE

Pour la logique complexe, creer un composant `Alpine.data()` dans le fichier JS correspondant (`public.js` pour les pages publiques, `app.js` pour l'admin).

### Ajout de ressources externes

Si une nouvelle ressource externe est necessaire (CDN, API, iframe), la directive CSP correspondante doit etre mise a jour dans `SecurityHeaders.php`.

### Inline event handlers

Les attributs `onclick`, `onload`, `onerror`, etc. sont bloques par la CSP et **ne peuvent pas recevoir de nonce**. Utiliser des `addEventListener` en JS a la place.

### Directives Flux

Toujours passer le nonce explicitement aux directives Flux :
```blade
@fluxScripts(['nonce' => Vite::cspNonce()])
@fluxAppearance(['nonce' => Vite::cspNonce()])
```
