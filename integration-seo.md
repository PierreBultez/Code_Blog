# Intégration SEO — Journal d'implémentation

**Date :** 26 mars 2026
**Référence :** [audit-seo.md](audit-seo.md)

---

## Vue d'ensemble

Implémentation des 7 quick wins identifiés dans l'audit SEO, suivie du système d'images OG (génération automatique + upload manuel), du champ `og_text` pour les miniatures, du redesign visuel des miniatures, et de la restructuration de la page d'accueil. Toutes les modifications ont été validées par les tests et formatées par Laravel Pint.

---

## 1. Correction de la locale française

**Problème :** `APP_LOCALE=en` produisait `<html lang="en">` sur un site entièrement en français, envoyant un signal contradictoire à Google pour le ciblage France.

**Fichier modifié :** `.env`

```diff
- APP_LOCALE=en
- APP_FALLBACK_LOCALE=en
- APP_FAKER_LOCALE=en_US
+ APP_LOCALE=fr
+ APP_FALLBACK_LOCALE=fr
+ APP_FAKER_LOCALE=fr_FR
```

**Pourquoi ça fonctionne :** Le layout `public.blade.php` génère déjà `<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">`. Changer la variable d'environnement suffit à corriger toutes les pages automatiquement.

---

## 2. Champ `meta_description` sur le modèle Article

**Problème :** Aucun moyen de définir une description SEO spécifique par article, indépendante de l'extrait affiché sur le site.

### Migration

**Fichier créé :** `database/migrations/2026_03_26_091102_add_meta_description_to_articles_table.php`

```php
Schema::table('articles', function (Blueprint $table) {
    $table->string('meta_description', 160)->nullable()->after('excerpt');
});
```

- Limité à 160 caractères (recommandation Google pour les meta descriptions)
- Nullable : si vide, le système utilise l'`excerpt` en fallback

### Modèle Article

**Fichier modifié :** `app/Models/Article.php`

- Ajout de `meta_description` dans `$fillable`
- Ajout d'un accessor `seoDescription` avec fallback :

```php
protected function seoDescription(): Attribute
{
    return Attribute::get(
        fn (): string => $this->meta_description
            ?? Str::limit($this->excerpt ?? '', 160, '')
    );
}
```

L'accessor `$article->seo_description` retourne la meta description si définie, sinon tronque l'excerpt à 160 caractères.

### Factory

**Fichier modifié :** `database/factories/ArticleFactory.php`

```php
'meta_description' => fake()->optional(0.7)->text(160),
```

70% des articles générés par la factory auront une meta description, pour tester les deux cas (avec et sans fallback).

### Formulaire Dashboard

**Fichiers modifiés :**
- `app/Livewire/Dashboard/ArticleForm.php` — propriété `$meta_description`, hydratation dans `mount()`, validation `nullable|string|max:160`, sauvegarde dans `save()`
- `resources/views/livewire/dashboard/article-form.blade.php` — champ textarea avec compteur de caractères

Le champ apparaît entre "Excerpt" et "Content" dans le formulaire, avec un placeholder explicatif et un compteur `X/160 caractères`.

---

## 3. Partial SEO réutilisable

**Problème :** Aucune balise meta description, canonical, Open Graph ou Twitter Card sur le site.

**Fichier créé :** `resources/views/partials/seo.blade.php`

### Balises générées

| Balise | Rôle |
|--------|------|
| `<meta name="description">` | Description dans les résultats Google |
| `<link rel="canonical">` | URL officielle de la page (évite le contenu dupliqué) |
| `<meta property="og:type">` | Type de contenu Open Graph (`website` ou `article`) |
| `<meta property="og:title">` | Titre pour le partage social |
| `<meta property="og:description">` | Description pour le partage social |
| `<meta property="og:url">` | URL canonique pour Open Graph |
| `<meta property="og:image">` | Image de partage (fallback : `og-default.png`) |
| `<meta property="og:locale">` | `fr_FR` |
| `<meta property="og:site_name">` | Nom du site |
| `<meta property="article:published_time">` | Date de publication (articles uniquement) |
| `<meta property="article:modified_time">` | Date de modification (articles uniquement) |
| `<meta property="article:author">` | Auteur (articles uniquement) |
| `<meta property="article:tag">` | Tags de l'article (articles uniquement) |
| `<meta name="twitter:card">` | Type de carte Twitter (`summary_large_image`) |
| `<meta name="twitter:title">` | Titre pour Twitter |
| `<meta name="twitter:description">` | Description pour Twitter |
| `<meta name="twitter:image">` | Image pour Twitter |

### Props acceptées

```blade
@props([
    'title'       => config('app.name'),        // Titre de la page
    'description' => 'Description par défaut',   // Meta description
    'canonical'   => null,                       // URL canonique (défaut: url()->current())
    'ogType'      => 'website',                  // Type OG (website ou article)
    'ogImage'     => null,                       // Image OG (défaut: og-default.png)
    'article'     => null,                       // Instance Article pour les balises dynamiques
])
```

---

## 4. Données structurées JSON-LD

**Problème :** Aucune donnée structurée, empêchant les rich snippets et la compréhension sémantique par les moteurs de recherche.

**Fichier créé :** `resources/views/partials/structured-data.blade.php`

### Schemas implémentés

#### WebSite (toutes les pages)

```json
{
    "@type": "WebSite",
    "name": "<Code_Blog>",
    "url": "https://...",
    "inLanguage": "fr-FR",
    "author": {
        "@type": "Person",
        "name": "Pierre Bultez",
        "sameAs": ["GitHub", "LinkedIn"]
    }
}
```

#### BreadcrumbList (pages avec breadcrumbs)

Généré dynamiquement à partir du tableau `$seoBreadcrumbs` passé depuis chaque vue. Chaque entrée contient `position`, `name`, et `item` (URL).

#### Article (page article uniquement)

```json
{
    "@type": "Article",
    "headline": "Titre de l'article",
    "description": "...",
    "datePublished": "ISO 8601",
    "dateModified": "ISO 8601",
    "author": { "@type": "Person", "name": "Pierre Bultez" },
    "keywords": "tag1, tag2",
    "wordCount": 1234,
    "inLanguage": "fr-FR"
}
```

---

## 5. Titres de page optimisés

**Problème :** Les titres n'incluaient aucun mot-clé cible et utilisaient des formulations non descriptives.

### Modification du layout

**Fichier modifié :** `resources/views/layouts/public.blade.php`

Le layout accepte maintenant des props SEO et construit le titre avec le format `Titre — <Code_Blog>` :

```php
$pageTitle = $title
    ? $title . ' — ' . $appName
    : $appName . ' — Blog dev Laravel freelance';
```

### Titres par page

| Page | Avant | Après |
|------|-------|-------|
| Accueil | `Just another <code_blog>` | `Blog Développeur Laravel Freelance — <Code_Blog>` |
| Articles | `Manuscrits <Code_Blog>` | `Articles & Tutoriels Laravel — <Code_Blog>` |
| Article | `{titre} <Code_Blog>` | `{titre} — <Code_Blog>` |
| À Propos | `À Propos <Code_Blog>` | `À Propos — Pierre Bultez, Développeur Laravel Freelance — <Code_Blog>` |

### Breadcrumbs par page

Chaque vue passe maintenant un tableau `seoBreadcrumbs` au layout :

- **Accueil** : aucun (page racine)
- **Articles index** : Accueil > Articles
- **Article show** : Accueil > Articles > {titre}
- **À Propos** : Accueil > À Propos

---

## 6. Correction des liens du footer

**Problème :** Les liens RSS et GitHub dans le footer pointaient vers `#`.

**Fichier modifié :** `resources/views/components/public/footer.blade.php`

```diff
- <a href="#">RSS</a>
- <a href="#">Github</a>
+ <a href="{{ url('/feed') }}" title="Flux RSS">RSS</a>
+ <a href="https://github.com/PierreBultez" target="_blank" rel="noopener noreferrer">Github</a>
```

Note : le lien RSS pointe vers `/feed` en anticipation de la route RSS à créer (investissement stratégique #9). Le lien GitHub pointe vers le vrai profil.

---

## 7. Mise à jour de robots.txt

**Problème :** Le fichier robots.txt n'interdisait rien et ne référençait pas le sitemap.

**Fichier modifié :** `public/robots.txt`

```diff
  User-agent: *
- Disallow:
+ Disallow: /dashboard
+ Disallow: /login
+ Disallow: /register
+
+ Sitemap: https://blog.pierrebultez.com/sitemap.xml
```

Les pages d'administration et d'authentification sont maintenant exclues de l'indexation. Le sitemap est référencé (à créer dans l'investissement stratégique #8).

> **Important :** Vérifier que le domaine `blog.pierrebultez.com` est correct et ajuster si nécessaire.

---

---

## 8. Système d'images Open Graph (génération auto + upload manuel)

**Problème :** Les balises `og:image` et `twitter:image` étaient en place (partial SEO) mais aucune image n'existait réellement. Les articles n'avaient aucun visuel dans la liste non plus.

**Bibliothèque utilisée :** GD (extension PHP native, déjà disponible sur le serveur)

### Architecture du système

```
$article->og_image_url
    ├── Si og_image renseigné → asset('storage/' . $article->og_image)  (upload manuel)
    └── Sinon               → route('articles.og-image', $article)     (génération auto)
```

L'accessor `ogImageUrl` sur le modèle Article centralise la logique. Il est utilisé à la fois par :
- Les balises `og:image` et `twitter:image` (partial SEO)
- Les vignettes dans la liste des articles (index)

### Migration

**Fichier créé :** `database/migrations/2026_03_26_094950_add_og_image_to_articles_table.php`

```php
$table->string('og_image')->nullable()->after('meta_description');
```

Stocke le chemin relatif de l'image uploadée manuellement (ex: `og-images/mon-article.png`). `null` = utiliser la génération automatique.

### Contrôleur de génération

**Fichier créé :** `app/Http/Controllers/OgImageController.php`

Route : `GET /articles/{article:slug}/og-image.png` (nommée `articles.og-image`)
Enregistrée dans `bootstrap/app.php` avec middleware minimal (voir section 11).

Génère une image PNG 1200x630px avec le titre (ou `og_text`) centré, rendu en dégradé de couleur via une technique de masquage pixel par pixel. Le design visuel a été simplifié (voir section 10a pour les détails).

L'image est mise en cache 7 jours via `Cache::remember()` (driver Redis, voir section 12) avec une clé basée sur `article.id` + `article.updated_at`. Le header HTTP `Cache-Control: max-age=604800` est aussi envoyé.

Retourne une 404 si l'article n'est pas publié.

### Modèle Article

**Fichier modifié :** `app/Models/Article.php`

- Ajout de `og_image` dans `$fillable`
- Ajout de l'accessor `ogImageUrl` :

```php
protected function ogImageUrl(): Attribute
{
    return Attribute::get(function (): string {
        if ($this->og_image) {
            return asset("storage/{$this->og_image}");
        }

        return route('articles.og-image', $this);
    });
}
```

### Upload dans le formulaire Dashboard

**Fichier modifié :** `app/Livewire/Dashboard/ArticleForm.php`

- Ajout du trait `WithFileUploads`
- Propriété `$og_image_upload` (fichier temporaire Livewire)
- Propriété `$remove_og_image` (flag de suppression)
- Méthode `removeOgImage()` pour supprimer l'image existante
- Validation : `image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:min_width=1200,min_height=630`
- Stockage sur le disque `public` dans le dossier `og-images/`
- Suppression de l'ancienne image lors du remplacement

**Fichier modifié :** `resources/views/livewire/dashboard/article-form.blade.php`

Le champ apparaît entre "Meta Description" et "Content" :
- Description : "Format recommandé : 1200x630px, PNG ou JPG (max 2 Mo). Laissez vide pour une image générée automatiquement."
- Preview de l'image uploadée (via `temporaryUrl()`) ou de l'image existante
- Bouton pour retirer/supprimer l'image
- Input file avec `accept="image/png,image/jpeg,image/webp"`

### Intégration dans les vues publiques

**Fichier modifié :** `resources/views/articles/show.blade.php`

Passe `seoOgImage` au layout :
```blade
:seoOgImage="$article->og_image_url"
```

**Fichier modifié :** `resources/views/articles/index.blade.php`

Ajout d'une vignette dans chaque carte article :
```blade
<img src="{{ $article->og_image_url }}" alt="{{ $article->title }}"
     class="w-full aspect-[1.91/1] object-cover rounded-lg" loading="lazy">
```

L'image est affichée à gauche sur desktop (largeur fixe 224px) et pleine largeur sur mobile, avec un ratio 1.91:1 correspondant au format OG standard.

### Prérequis serveur

- Extension PHP GD (vérifiée disponible)
- Symlink `storage:link` (créé lors de l'implémentation)
- Police DejaVu Sans Bold embarquée dans `storage/fonts/DejaVuSans-Bold.ttf` (voir section 13)

### Tests

**Fichier créé :** `tests/Feature/OgImageTest.php` — 4 tests :

1. Génère une image PNG pour un article publié (vérifie status 200 + Content-Type)
2. Retourne 404 pour un brouillon
3. Retourne l'URL de l'upload quand `og_image` est défini
4. Retourne l'URL générée quand `og_image` est null

---

## 9. Champ `og_text` — Texte court pour les miniatures

**Problème :** Les titres d'articles sont souvent trop longs pour être lisibles sur une miniature 1200x630px. Il fallait pouvoir définir un texte alternatif plus concis, dédié à l'affichage sur la miniature générée.

### Migration

**Fichier créé :** `database/migrations/2026_03_26_..._add_og_text_to_articles_table.php`

```php
$table->string('og_text')->nullable()->after('og_image');
```

- Nullable : si vide, le titre de l'article est utilisé
- Pas de limite stricte en base, mais le formulaire valide `max:100`

### Modèle Article

**Fichier modifié :** `app/Models/Article.php`

- Ajout de `og_text` dans `$fillable`

### Utilisation dans la génération d'image

**Fichier modifié :** `app/Http/Controllers/OgImageController.php`

```php
$text = $article->og_text ?: $article->title;
```

Le contrôleur utilise `og_text` en priorité, avec fallback sur `title`. Cela permet de garder un titre SEO long et descriptif tout en ayant une miniature visuellement propre.

### Formulaire Dashboard

**Fichiers modifiés :**
- `app/Livewire/Dashboard/ArticleForm.php` — propriété `$og_text`, hydratation dans `mount()`, validation `nullable|string|max:100`, sauvegarde dans `save()`
- `resources/views/livewire/dashboard/article-form.blade.php` — champ input avec placeholder et description :

```blade
<flux:input wire:model="og_text" placeholder="Texte court pour la miniature générée. Laissez vide pour utiliser le titre." />
<flux:description>Texte affiché sur la miniature auto-générée (max 100 car.). Idéal pour raccourcir un titre trop long.</flux:description>
```

---

## 10. Redesign des miniatures OG et restructuration de la page d'accueil

### 10a. Simplification de la miniature générée

**Problème :** La miniature initiale contenait trop d'éléments (nom du blog, tags, auteur, date) qui la rendaient chargée et peu lisible en petit format (vignettes dans la liste d'articles, partages sociaux).

**Fichier modifié :** `app/Http/Controllers/OgImageController.php`

#### Avant

- Fond : dégradé 3 stops (noir → violet sombre → bordeaux)
- Texte : dégradé magenta → violet
- Éléments affichés : `<Code_Blog>` en haut, titre au centre, tags en bas à gauche, auteur + date en bas
- 2 constantes de police : `FONT_PATH` (regular) + `FONT_BOLD_PATH` (bold)

#### Après

- Fond : dégradé 2 stops `#000000` → `#920021` (noir → rouge profond)
- Texte : dégradé `#FFE17A` → `#FD5561` (or/jaune → corail/rouge)
- Éléments affichés : **uniquement le titre ou `og_text`**, centré verticalement
- 1 seule constante de police : `FONT_BOLD_PATH` (bold uniquement)

```php
// Fond
$this->drawVerticalGradient($canvas, $width, $height, [
    ['r' => 0, 'g' => 0, 'b' => 0],         // #000000
    ['r' => 146, 'g' => 0, 'b' => 33],      // #920021
]);

// Texte
$textColorFrom = ['r' => 255, 'g' => 225, 'b' => 122];  // #FFE17A
$textColorTo = ['r' => 253, 'g' => 85, 'b' => 97];      // #FD5561
```

#### Technique de rendu du dégradé texte

Le texte à dégradé horizontal est rendu via une technique de **masquage pixel par pixel** (GD ne supportant pas nativement le texte en dégradé) :

1. **Calque dégradé** — Image temporaire remplie du dégradé horizontal or→corail
2. **Calque masque** — Image temporaire avec le texte en blanc sur fond noir (via `imagettftext`)
3. **Composition** — Pour chaque pixel du masque dont la luminosité dépasse un seuil (>10), on copie le pixel correspondant du dégradé sur le canvas final, avec un alpha proportionnel à la luminosité du masque (assure l'anti-aliasing des bords de lettres)

Cette approche produit un texte avec des bords lisses et un dégradé de couleur net, sans dépendance externe.

### 10b. Restructuration de la page d'accueil

**Problème :** L'ajout des vignettes OG au-dessus des articles featured créait un déséquilibre visuel : la carte article occupait 8 colonnes avec une grande image, tandis que le compteur de stats sur 4 colonnes se retrouvait étiré verticalement.

**Fichier modifié :** `resources/views/home.blade.php`

#### Avant

```
┌──────────────────────┐ ┌──────────┐
│  1 article featured  │ │  Stats   │
│     (8 colonnes)     │ │ (4 col.) │
└──────────────────────┘ └──────────┘
```

#### Après

```
┌──────────┐ ┌──────────┐ ┌──────────┐
│ Article  │ │ Article  │ │  Stats   │
│ featured │ │ featured │ │ (1/3)    │
│  (1/3)   │ │  (1/3)   │ │          │
└──────────┘ └──────────┘ └──────────┘
```

- 2 articles featured au lieu d'1, chacun en 1/3 de largeur
- Carte stats en 1/3, proportionnée
- Chaque carte article affiche : vignette OG (ratio 1.91:1) + tags + date + titre + excerpt + lien "Lire"
- Section "Articles Récents" en dessous avec les 3 articles suivants (excluant les 2 featured)

**Fichier modifié :** `app/Http/Controllers/HomeController.php`

```php
// Avant : 1 featured + 4 récents
// Après : 2 featured + 3 récents (en excluant les featured)
$featuredArticles = Article::query()
    ->published()->with('tags')->latest('published_at')->take(2)->get();

$featuredIds = $featuredArticles->pluck('id')->all();

$recentArticles = Article::query()
    ->published()->with('tags')->latest('published_at')
    ->whereNotIn('id', $featuredIds)->take(3)->get();
```

Les articles récents excluent les IDs des featured via `whereNotIn` pour éviter les doublons.

---

## 11. Route OG image — Optimisation middleware

**Problème :** La route `articles.og-image` était enregistrée dans `routes/web.php`, ce qui lui appliquait les 15+ middlewares du groupe `web` (sessions, CSRF, cookies, etc.) — totalement inutiles pour servir une image binaire.

**Fichier modifié :** `bootstrap/app.php`

La route a été déplacée dans le callback `then:` de `withRouting()`, avec uniquement le middleware `SubstituteBindings` (nécessaire pour le route model binding `{article:slug}`) :

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware(SubstituteBindings::class)
            ->get('/articles/{article:slug}/og-image.png', OgImageController::class)
            ->name('articles.og-image');
    },
)
```

**Fichier modifié :** `routes/web.php`

- Suppression de la route `articles.og-image` et de l'import `OgImageController`

**Impact :** Chaque requête d'image OG passe par 1 middleware au lieu de ~15, réduisant le temps de traitement côté serveur.

> **Note :** Le middleware `SubstituteBindings` est indispensable ici. Sans lui, `{article:slug}` ne résout pas l'instance Eloquent et la route retourne 404.

---

## 12. Migration vers Redis (cache et sessions)

**Problème :** Le cache et les sessions utilisaient le driver `database`. Cela posait deux problèmes :
1. **Performance** — chaque lecture/écriture de cache = requête SQL
2. **Bug binaire** — `Cache::remember()` dans `OgImageController` tentait de stocker du PNG binaire (~50 Ko) dans une table `cache` en charset `utf8mb4`, causant des erreurs 500 en production (solution temporaire : encodage base64, supprimé depuis)

**Fichier modifié :** `.env` (en production uniquement)

```diff
- CACHE_STORE=database
- SESSION_DRIVER=database
+ CACHE_STORE=redis
+ SESSION_DRIVER=redis
```

**Impact :** Le cache Redis stocke nativement les données binaires et offre des temps d'accès sub-milliseconde. Le workaround base64 a été supprimé du code.

---

## 13. Portabilité de la police OG

**Problème :** `OgImageController` référençait la police système `/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf`, qui n'existait pas sur le VPS de production (erreur 500).

**Fichier modifié :** `app/Http/Controllers/OgImageController.php`

```diff
- private const FONT_BOLD_PATH = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
+ private const FONT_BOLD_PATH = __DIR__.'/../../../storage/fonts/DejaVuSans-Bold.ttf';
```

**Fichier ajouté :** `storage/fonts/DejaVuSans-Bold.ttf`

La police est embarquée dans le projet. Le chemin relatif via `__DIR__` fonctionne quel que soit l'emplacement d'installation.

---

## 14. Optimisation Lighthouse — Performance (score 83 → 95+)

**Problème :** Le score Lighthouse Performance est tombé à 83. L'analyse du rapport JSON a identifié 4 causes, **sans rapport avec Redis** :

### 14a. Google Fonts non-bloquantes (économie estimée : ~1 060 ms sur FCP)

**Fichier modifié :** `resources/views/layouts/public.blade.php`

Les 2 `<link rel="stylesheet">` pour Google Fonts bloquaient le rendu : le navigateur suspendait l'affichage tant que le CSS des fonts n'était pas téléchargé et parsé.

#### Avant

```html
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
```

#### Après

```html
{{-- Preload pour démarrer le téléchargement en priorité haute --}}
<link rel="preload" as="style" href="...Manrope+JetBrains...&display=swap">
<link rel="preload" as="style" href="...Material+Symbols...&display=swap">

{{-- Chargement non-bloquant via media="print" + onload --}}
<link href="...Manrope+JetBrains...&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<link href="...Material+Symbols...&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

{{-- Fallback sans JavaScript --}}
<noscript>
    <link href="...Manrope+JetBrains...&display=swap" rel="stylesheet">
    <link href="...Material+Symbols...&display=swap" rel="stylesheet">
</noscript>
```

**Technique :** `media="print"` dit au navigateur que le CSS ne concerne que l'impression → il ne bloque pas le rendu. `onload="this.media='all'"` bascule sur `media="all"` une fois chargé → les styles s'appliquent. Le `rel="preload"` en parallèle déclenche le téléchargement en priorité haute malgré le `media="print"`. Le `display=swap` dans l'URL Google assure que le texte est visible immédiatement avec une police système de fallback.

### 14b. Material Symbols allégé (~1.1 MB → taille réduite)

**Fichier modifié :** `resources/views/layouts/public.blade.php`

L'URL Google Fonts demandait la police variable complète avec tous les axes de variation :

```diff
- Material+Symbols+Outlined:wght,FILL@100..700,0..1
+ Material+Symbols+Outlined:opsz,wght,FILL@24,400,0
```

L'ancienne URL chargeait **tous les poids** (100 à 700) et **toutes les variantes de fill** (0 à 1), soit la police variable multi-axes complète (~1.1 MB en woff2). La nouvelle URL fixe une seule combinaison (`opsz=24, wght=400, FILL=0`), correspondant à l'utilisation réelle dans le projet (le CSS définit `font-variation-settings: 'FILL' 0, 'wght' 400`). Google Fonts sert alors un fichier statique beaucoup plus léger.

### 14c. CLS réduit — Dimensions explicites sur les images OG

**Fichiers modifiés :**
- `resources/views/home.blade.php` — images featured + articles récents
- `resources/views/articles/index.blade.php` — vignettes de la liste

```diff
- <img src="{{ $article->og_image_url }}" alt="..." class="w-full aspect-[1.91/1] object-cover" loading="lazy">
+ <img src="{{ $article->og_image_url }}" alt="..." width="1200" height="630" class="w-full aspect-[1.91/1] object-cover" loading="lazy">
```

Les attributs HTML `width` et `height` permettent au navigateur de calculer le ratio et réserver l'espace avant le chargement de l'image, éliminant le layout shift (CLS). Les images générées font 1200x630px.

### 14d. Cache navigateur pour les assets Vite (à configurer en production)

**À ajouter dans la config Nginx du VPS :**

```nginx
location /build/assets/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}
```

Les fichiers Vite utilisent des noms hashés (ex: `app-Bx7K3qF2.css`). Un cache d'1 an avec `immutable` est sûr car le hash change automatiquement à chaque build. Actuellement, le TTL cache est à 0 dans Lighthouse.

---

## Récapitulatif des fichiers (mis à jour)

### Fichiers créés

| Fichier | Rôle |
|---------|------|
| `database/migrations/..._add_meta_description_to_articles_table.php` | Migration champ `meta_description` |
| `database/migrations/..._add_og_image_to_articles_table.php` | Migration champ `og_image` |
| `database/migrations/..._add_og_text_to_articles_table.php` | Migration champ `og_text` |
| `resources/views/partials/seo.blade.php` | Meta tags, canonical, OG, Twitter |
| `resources/views/partials/structured-data.blade.php` | JSON-LD (WebSite, Article, Breadcrumb) |
| `app/Http/Controllers/OgImageController.php` | Génération d'images OG avec GD (dégradés + masquage) |
| `tests/Feature/OgImageTest.php` | 4 tests pour le système OG |
| `storage/fonts/DejaVuSans-Bold.ttf` | Police embarquée pour la génération OG |

### Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `.env` | Locale `fr`, faker `fr_FR` |
| `.env` (prod) | Cache et sessions → Redis |
| `composer.json` | Dépendances du projet |
| `bootstrap/app.php` | Route OG image avec middleware minimal (SubstituteBindings uniquement) |
| `routes/web.php` | Suppression de la route OG image |
| `app/Models/Article.php` | `meta_description` + `og_image` + `og_text` fillable, accessors `seoDescription` + `ogImageUrl` |
| `database/factories/ArticleFactory.php` | `meta_description` dans la factory |
| `app/Http/Controllers/OgImageController.php` | Police embarquée, design simplifié (dégradé noir→rouge + texte or→corail) |
| `app/Livewire/Dashboard/ArticleForm.php` | Meta description + upload OG image + og_text + WithFileUploads |
| `resources/views/livewire/dashboard/article-form.blade.php` | Champs meta description + upload OG image + og_text |
| `resources/views/layouts/public.blade.php` | Props SEO, includes partials, titre dynamique, fonts non-bloquantes |
| `resources/views/home.blade.php` | Titre + description SEO + layout 2 featured + vignettes OG avec dimensions |
| `app/Http/Controllers/HomeController.php` | 2 featured + 3 récents (sans doublons) |
| `resources/views/articles/index.blade.php` | Titre + description + breadcrumbs + vignette OG avec dimensions |
| `resources/views/articles/show.blade.php` | Titre + description + OG image + OG article + breadcrumbs |
| `resources/views/about.blade.php` | Titre + description + breadcrumbs SEO |
| `resources/views/components/public/footer.blade.php` | Liens RSS et GitHub fonctionnels |
| `public/robots.txt` | Disallow admin + référence sitemap |

### Validation

- **76 tests passés** (185 assertions, 0 régression)
- **Pint** : code formaté automatiquement

---

## 15. Sitemap XML dynamique (`/sitemap.xml`)

**Date :** 28 mars 2026

**Problème :** Le fichier `robots.txt` référençait `/sitemap.xml` mais la route n'existait pas — Google recevait un 404 en tentant de l'indexer.

### Architecture

**Fichier créé :** `app/Http/Controllers/SitemapController.php`

Contrôleur invocable qui génère le XML via une vue Blade. Retourne le `Content-Type: application/xml`.

**Fichier créé :** `resources/views/sitemap.blade.php`

Template XML conforme au protocole [sitemaps.org](https://www.sitemaps.org/protocol.html) :
- Pages statiques : accueil, articles index, à propos
- Tous les articles publiés avec `<lastmod>` basé sur `updated_at`

**Route :** Enregistrée dans `bootstrap/app.php` (callback `then:`) **sans middleware** — un crawler n'a besoin ni de session, ni de CSRF.

```php
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
```

### Tests

**Fichier créé :** `tests/Feature/SitemapTest.php` — 4 tests :

1. Retourne du XML valide avec le bon Content-Type
2. Inclut les articles publiés
3. Exclut les brouillons
4. Inclut les pages statiques (home, articles, about)

---

## 16. Flux RSS fonctionnel (`/feed`)

**Date :** 28 mars 2026

**Problème :** Le lien RSS dans le footer pointait vers `/feed` mais aucune route n'existait — lien cassé visible par les utilisateurs.

### Architecture

**Fichier créé :** `app/Http/Controllers/RssFeedController.php`

Contrôleur invocable retournant du RSS 2.0 avec `Content-Type: application/rss+xml; charset=UTF-8`. Limité aux 20 articles les plus récents.

**Fichier créé :** `resources/views/feed.blade.php`

Template RSS 2.0 avec namespace Atom (`xmlns:atom`) :
- `<channel>` : titre, description, link, language (`fr`), lastBuildDate, self-link Atom
- `<item>` par article : title (échappé XML), link, guid (permalink), pubDate (RFC 2822), description (excerpt)

**Route :** Enregistrée dans `bootstrap/app.php` (callback `then:`) sans middleware.

```php
Route::get('/feed', RssFeedController::class)->name('feed');
```

### Découverte automatique

**Fichier modifié :** `resources/views/layouts/public.blade.php`

Ajout de la balise `<link rel="alternate">` dans le `<head>` :

```html
<link rel="alternate" type="application/rss+xml" title="<Code_Blog>" href="/feed">
```

Les navigateurs et lecteurs RSS détectent automatiquement le flux via cette balise.

### Tests

**Fichier créé :** `tests/Feature/RssFeedTest.php` — 4 tests :

1. Retourne du XML RSS valide avec le bon Content-Type
2. Inclut les articles publiés
3. Exclut les brouillons
4. Limite à 20 articles maximum

---

## 17. Image OG par défaut

**Date :** 28 mars 2026

**Problème :** Le partial `seo.blade.php` faisait fallback sur `asset('images/og-default.png')` pour les pages non-article (accueil, about, index), mais le fichier n'existait pas — les réseaux sociaux affichaient un lien d'image cassé lors du partage.

**Fichier créé :** `public/images/og-default.png`

Image 1200x630px (19.7 Ko) générée avec GD, dans le même style visuel que les miniatures d'articles :
- Fond : dégradé vertical noir → rouge profond (`#000000` → `#920021`)
- Texte : `<Code_Blog>` centré, dégradé horizontal or → corail (`#FFE17A` → `#FD5561`)
- Police : DejaVu Sans Bold (embarquée dans `storage/fonts/`)

Image statique (pas de génération dynamique) — le nom du blog change rarement.

---

## 18. Fil d'Ariane visuel

**Date :** 28 mars 2026

**Problème :** Les données `$seoBreadcrumbs` étaient déjà passées au layout et utilisées pour le JSON-LD `BreadcrumbList`, mais aucun rendu HTML n'était affiché. Les visiteurs n'avaient pas de repère visuel de leur position dans la hiérarchie du site.

**Fichier modifié :** `resources/views/layouts/public.blade.php`

Ajout d'un `<nav aria-label="Fil d'Ariane">` entre la navbar et le `<main>`, conditionné par `count($seoBreadcrumbs) > 1` (pas affiché sur la home page) :

```blade
@if (count($seoBreadcrumbs) > 1)
    <nav aria-label="Fil d'Ariane" class="max-w-4xl mx-auto px-6 pt-20 pb-0">
        <ol class="flex flex-wrap items-center gap-1 text-sm text-on-surface-variant">
            @foreach / @endforeach avec séparateur "/"
        </ol>
    </nav>
@endif
```

- Dernier élément : `aria-current="page"`, style `font-medium`, tronqué (`truncate max-w-xs`)
- Éléments précédents : liens cliquables avec hover
- Sémantique : `<nav>` + `<ol>` + `aria-label` + `aria-current` pour l'accessibilité

**Fichiers modifiés (ajustement padding) :**
- `resources/views/articles/index.blade.php` — `pt-20` → `pt-6`
- `resources/views/articles/show.blade.php` — `mt-20` → `mt-6`
- `resources/views/about.blade.php` — `pt-32` → `pt-6`

Le breadcrumb fournit désormais l'espacement depuis la navbar (`pt-20`), les sections enfants n'ont plus besoin de leur propre top padding.

---

## 19. Articles connexes

**Date :** 28 mars 2026

**Problème :** Chaque page article était une impasse — aucun lien vers d'autres articles. Le maillage interne était inexistant, ce qui limitait à la fois la navigation utilisateur et la capacité de Google à découvrir et comprendre les relations entre les contenus.

### Logique de recommandation

**Fichier modifié :** `app/Http/Controllers/ArticleController.php`

La méthode `show()` charge maintenant les articles connexes, triés par nombre de tags communs décroissant, puis par date :

```php
$relatedArticles = Article::query()
    ->published()
    ->where('id', '!=', $article->id)
    ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
    ->withCount(['tags as shared_tags_count' => fn ($q) => $q->whereIn('tags.id', $tagIds)])
    ->orderByDesc('shared_tags_count')
    ->latest('published_at')
    ->with('tags')
    ->take(3)
    ->get();
```

- Exclut l'article courant
- Ne s'affiche que s'il y a des tags (`$tagIds->isNotEmpty()`)
- Limité à 3 articles
- `withCount` avec alias `shared_tags_count` pour trier par pertinence

### Affichage

**Fichier modifié :** `resources/views/articles/show.blade.php`

Section "Articles connexes" ajoutée entre le contenu et les commentaires :
- Grille 3 colonnes (responsive : 1 colonne mobile, 3 desktop)
- Chaque carte : vignette OG (1200x630, `loading="lazy"`), tags (max 2), date, titre (tronqué 2 lignes)
- Effet hover sur le titre (`group-hover:text-primary`)
- Séparateur visuel (`border-t border-outline-variant`) au-dessus de la section

### Tests

**Fichier créé :** `tests/Feature/RelatedArticlesTest.php` — 4 tests :

1. Affiche les articles liés par tags communs
2. N'affiche pas les articles sans tags communs
3. N'affiche pas l'article courant dans les suggestions
4. Limite à 3 articles connexes

---

## Récapitulatif des fichiers — Session 2 (28 mars 2026)

### Fichiers créés

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/SitemapController.php` | Génère le sitemap XML dynamique |
| `app/Http/Controllers/RssFeedController.php` | Génère le flux RSS 2.0 |
| `resources/views/sitemap.blade.php` | Template XML du sitemap |
| `resources/views/feed.blade.php` | Template XML du flux RSS |
| `public/images/og-default.png` | Image OG par défaut (1200x630, 19.7 Ko) |
| `tests/Feature/SitemapTest.php` | 4 tests sitemap |
| `tests/Feature/RssFeedTest.php` | 4 tests RSS |
| `tests/Feature/RelatedArticlesTest.php` | 4 tests articles connexes |

### Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `bootstrap/app.php` | Routes `/sitemap.xml` et `/feed` sans middleware |
| `resources/views/layouts/public.blade.php` | Balise RSS `<link rel="alternate">` + fil d'Ariane visuel |
| `resources/views/articles/show.blade.php` | Section "Articles connexes" + padding `mt-6` |
| `resources/views/articles/index.blade.php` | Padding `pt-6` (breadcrumbs) |
| `resources/views/about.blade.php` | Padding `pt-6` (breadcrumbs) |
| `app/Http/Controllers/ArticleController.php` | Requête articles connexes par tags communs |

### Validation

- **103 tests passés** (253 assertions, 0 régression)
- **Pint** : code formaté automatiquement

---

## Prochaines étapes

Les points suivants restent à implémenter :

- [x] ~~Sitemap XML dynamique~~ (fait — section 15)
- [x] ~~Flux RSS fonctionnel~~ (fait — section 16)
- [x] ~~Image OG par défaut~~ (fait — section 17)
- [x] ~~Fil d'Ariane visuel~~ (fait — section 18)
- [x] ~~Optimisation du chargement des fonts~~ (fait — section 14a/14b)
- [x] ~~Section "Articles connexes"~~ (fait — section 19)
- [ ] Inscription à Google Search Console + soumission du sitemap
- [ ] Cache navigateur Nginx pour les assets Vite (section 14d)
- [ ] Pillar page "Guide Laravel" avec topic cluster
- [ ] Produire 2 articles/mois ciblant les mots-clés identifiés
