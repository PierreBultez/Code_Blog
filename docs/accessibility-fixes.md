# Correctifs d'Accessibilite WCAG 2.1 AA — `<Code_Blog>`

**Date :** 2026-03-28 | **Audit de reference :** [accessibility-audit.md](accessibility-audit.md)

---

## Resume

19 problemes identifies, 19 corriges. 10 fichiers modifies.

### Fichiers modifies

- `resources/views/components/public/navbar.blade.php`
- `resources/views/components/public/footer.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/layouts/auth/simple.blade.php`
- `resources/views/home.blade.php`
- `resources/views/about.blade.php`
- `resources/views/articles/index.blade.php`
- `resources/views/articles/show.blade.php`
- `resources/views/livewire/article-comments.blade.php`
- `resources/views/livewire/contact-form.blade.php`

---

## Detail des correctifs

### #1 — Bouton dark mode sans label accessible (Critique)
**Critere WCAG :** 1.1.1 Non-text Content
**Fichier :** `navbar.blade.php`
**Correctif :** Ajout de `x-bind:aria-label="dark ? 'Activer le mode clair' : 'Activer le mode sombre'"` sur le bouton. Ajout de `aria-hidden="true"` sur les icones Material Symbols a l'interieur.

### #2 — Bouton menu mobile sans label (Critique)
**Critere WCAG :** 1.1.1 Non-text Content
**Fichier :** `navbar.blade.php`
**Correctif :** Ajout de `x-bind:aria-label="open ? 'Fermer le menu' : 'Ouvrir le menu'"`, `x-bind:aria-expanded="open"`, et `aria-controls="mobile-menu"`. Ajout de `aria-hidden="true"` sur les icones. Ajout de `id="mobile-menu"` et `x-on:keydown.escape.window="open = false"` sur le panneau du menu.

### #3 — Icones decoratives non masquees (Mineur)
**Critere WCAG :** 1.1.1 Non-text Content
**Fichiers :** `about.blade.php`, `home.blade.php`, `articles/show.blade.php`, `article-comments.blade.php`, `contact-form.blade.php`
**Correctif :** Ajout de `aria-hidden="true"` sur toutes les icones Material Symbols decoratives (terminal, code, database, commit, mail, location_on, bar_chart, arrow_forward, person, chat_bubble, check_circle, send, progress_activity, settings_ethernet, add_reaction, alternate_email, demography).

### #4 — Contraste insuffisant zinc-400 (Critique)
**Critere WCAG :** 1.4.3 Contrast
**Fichier :** `home.blade.php`
**Correctif :** Remplacement de `text-zinc-400` par `text-on-surface-variant` sur les dates d'articles et le titre "Articles Recents". Le token `on-surface-variant` offre un ratio de ~9:1 en mode clair et s'adapte automatiquement au mode sombre.

### #5 — Contraste outline borderline (Majeur)
**Critere WCAG :** 1.4.3 Contrast
**Fichiers :** `articles/show.blade.php`, `articles/index.blade.php`, `article-comments.blade.php`
**Correctif :** Remplacement de `text-outline` (~4.5:1) par `text-on-surface-variant` (~9:1) sur toutes les occurrences de texte (dates, temps de lecture, meta des commentaires).

### #6 — Texte a text-[10px] (Majeur)
**Critere WCAG :** 1.4.4 Resize Text
**Fichier :** `home.blade.php`
**Correctif :** Remplacement de `text-[10px]` par `text-xs` (12px) sur les tags et dates des cartes d'articles.

### #7 — Compteurs animes affichent 0 (Majeur)
**Critere WCAG :** 1.3.1 Info and Relationships
**Fichier :** `home.blade.php`
**Correctif :** Ajout de `aria-hidden="true"` sur les `<p>` animes et de `<span class="sr-only">{{ $stats['articles'] }}</span>` / `<span class="sr-only">{{ $stats['tags'] }}</span>` avec les vraies valeurs.

### #8 — Skip to content manquant (Critique)
**Critere WCAG :** 2.4.1 Bypass Blocks
**Fichier :** `layouts/public.blade.php`
**Correctif :** Ajout d'un lien `<a href="#main-content" class="sr-only focus:not-sr-only ...">Aller au contenu</a>` avant la navbar. Ajout de `id="main-content"` sur la balise `<main>`. Le lien apparait au premier Tab et saute directement au contenu.

### #9 — Liens overlay invisibles au focus (Majeur)
**Critere WCAG :** 2.4.7 Focus Visible / 2.4.4 Link Purpose
**Fichiers :** `home.blade.php`, `articles/index.blade.php`
**Correctif :** Ajout de `aria-label="{{ $article->title }}"` pour identifier le lien, et `focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2` + `rounded-*` pour afficher un focus ring visible au clavier.

### #10 — Menu mobile sans aria-expanded (Majeur)
**Critere WCAG :** 2.4.3 Focus Order
**Fichier :** `navbar.blade.php`
**Correctif :** Traite conjointement avec le correctif #2. `aria-expanded`, `aria-controls`, fermeture Escape inclus.

### #11 — Liens externes sans rel noopener (Mineur)
**Best Practice**
**Fichier :** `about.blade.php`
**Correctif :** Ajout de `rel="noopener noreferrer"` sur les 4 liens sociaux (GitHub, LinkedIn, Web, CV).

### #12 — Filtres tags sans indication d'etat actif (Mineur)
**Critere WCAG :** 2.4.4 Link Purpose
**Fichier :** `articles/index.blade.php`
**Correctif :** Ajout de `@if (!$activeTag) aria-current="page" @endif` sur le lien "Tous" et `@if ($activeTag === $tag->slug) aria-current="page" @endif` sur chaque tag.

### #13 — Messages d'erreur non lies aux champs (Majeur)
**Critere WCAG :** 3.3.1 Error Identification
**Fichiers :** `article-comments.blade.php`, `contact-form.blade.php`
**Correctif :** Ajout de `aria-describedby="error-{field}"` sur chaque input/textarea/select et de `id="error-{field}" role="alert"` sur chaque `<p>` d'erreur. Champs concernes : author_name, content (commentaires), name, subject, email, message (contact).

### #14 — Hierarchie des headings incoherente (Majeur)
**Critere WCAG :** 1.3.1 Info and Relationships
**Fichiers :** `home.blade.php`, `about.blade.php`
**Correctif :**
- **home.blade.php** : h3 "Articles Recents" → h2, h4 titre article → h3
- **about.blade.php** : h3 "Pierre" → h2, h4 "Pourquoi ce blog" → h2

### #15 — Melange de langues sans lang attribute (Mineur)
**Critere WCAG :** 3.1.2 Language of Parts
**Fichier :** `layouts/auth/simple.blade.php`
**Correctif :** Ajout de `lang="en"` sur le `<div>` wrapper du slot contenant les formulaires d'authentification en anglais.

### #16 — Select sans option placeholder (Mineur)
**Critere WCAG :** 3.3.2 Labels or Instructions
**Fichier :** `contact-form.blade.php`
**Correctif :** Ajout de `<option value="" disabled selected>Choisir un sujet...</option>` en premiere option du select "Sujet".

### #17 — Landmarks manquants (Majeur)
**Critere WCAG :** 4.1.2 Name, Role, Value
**Fichiers :** `home.blade.php`, `about.blade.php`, `articles/show.blade.php`
**Correctif :** Ajout de `aria-labelledby` sur les sections principales avec des `id` correspondants sur les headings :
- `aria-labelledby="recent-articles-heading"` (home)
- `aria-labelledby="contact-heading"` (about)
- `aria-labelledby="related-articles-heading"` (show)

### #18 — Navigation tags sans landmark (Mineur)
**Critere WCAG :** 4.1.2 Name, Role, Value
**Fichier :** `articles/index.blade.php`
**Correctif :** Remplacement de `<div>` par `<nav aria-label="Filtrer par sujet">` autour des filtres de tags.

### #19 — Footer sans aria-label (Majeur)
**Critere WCAG :** 4.1.2 Name, Role, Value
**Fichier :** `footer.blade.php`
**Correctif :** Ajout de `aria-label="Pied de page"` sur la balise `<footer>`.

---

## Recommandations complementaires (hors scope)

Ces points n'ont pas ete traites car ils necessitent des tests manuels ou des decisions produit :

1. **Test avec VoiceOver/NVDA** — Valider les corrections avec un vrai lecteur d'ecran
2. **Test de zoom a 200%** — Verifier que le layout ne casse pas
3. **Contenu des articles** — Les images dans le contenu WYSIWYG (TinyMCE) doivent avoir des attributs `alt` pertinents. Cela depend du contenu saisi par l'auteur
4. **Reduire les animations** — Ajouter `@media (prefers-reduced-motion: reduce)` pour desactiver les animations (compteurs, transitions) pour les utilisateurs qui le souhaitent
