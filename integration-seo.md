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

Génère une image PNG 1200x630px avec le titre (ou `og_text`) centré, rendu en dégradé de couleur via une technique de masquage pixel par pixel. Le design visuel a été simplifié (voir section 10a pour les détails).

L'image est mise en cache 7 jours via `Cache::remember()` avec une clé basée sur `article.id` + `article.updated_at`. Le header HTTP `Cache-Control: max-age=604800` est aussi envoyé.

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
- Police DejaVu Sans Bold dans `/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf`

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

## Récapitulatif des fichiers

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

### Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `.env` | Locale `fr`, faker `fr_FR` |
| `composer.json` | Dépendances du projet |
| `routes/web.php` | Route `articles.og-image` + import contrôleur |
| `app/Models/Article.php` | `meta_description` + `og_image` + `og_text` fillable, accessors `seoDescription` + `ogImageUrl` |
| `database/factories/ArticleFactory.php` | `meta_description` dans la factory |
| `app/Livewire/Dashboard/ArticleForm.php` | Meta description + upload OG image + og_text + WithFileUploads |
| `resources/views/livewire/dashboard/article-form.blade.php` | Champs meta description + upload OG image + og_text |
| `resources/views/layouts/public.blade.php` | Props SEO, includes partials, titre dynamique |
| `resources/views/home.blade.php` | Titre + description SEO + layout 2 featured + vignettes OG |
| `app/Http/Controllers/HomeController.php` | 2 featured + 3 récents (sans doublons) |
| `resources/views/articles/index.blade.php` | Titre + description + breadcrumbs + vignette OG |
| `resources/views/articles/show.blade.php` | Titre + description + OG image + OG article + breadcrumbs |
| `resources/views/about.blade.php` | Titre + description + breadcrumbs SEO |
| `resources/views/components/public/footer.blade.php` | Liens RSS et GitHub fonctionnels |
| `public/robots.txt` | Disallow admin + référence sitemap |

### Validation

- **76 tests passés** (185 assertions, 0 régression)
- **Pint** : code formaté automatiquement

---

## Prochaines étapes (Investissements Stratégiques)

Les points suivants restent à implémenter :

- [ ] Sitemap XML dynamique (`/sitemap.xml`)
- [ ] Flux RSS fonctionnel (`/feed`)
- [ ] Fil d'Ariane visuel dans les pages
- [ ] Optimisation du chargement des fonts
- [ ] Section "Articles connexes" en bas des articles
- [ ] Inscription à Google Search Console + soumission du sitemap
