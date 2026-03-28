# Audit d'Accessibilite WCAG 2.1 AA — `<Code_Blog>`

**Standard :** WCAG 2.1 AA | **Date :** 2026-03-28

---

## Resume

**Problemes trouves : 19** | **Critiques : 4** | **Majeurs : 9** | **Mineurs : 6**

---

## 1. Perceivable (Perceptible)

| # | Probleme | Critere WCAG | Severite | Fichier(s) | Recommandation |
|---|----------|-------------|----------|------------|----------------|
| 1 | **Bouton dark mode sans label accessible** — Le `<button>` ne contient que des icones Material Symbols, aucun texte pour lecteurs d'ecran | 1.1.1 Non-text Content | Critique | `navbar.blade.php:24-32` | Ajouter `aria-label` dynamique |
| 2 | **Bouton menu mobile sans label** — Meme probleme pour le hamburger | 1.1.1 Non-text Content | Critique | `navbar.blade.php:35-38` | Ajouter `aria-label` + `aria-expanded` |
| 3 | **Icones decoratives non masquees** — Les `<span class="material-symbols-outlined">` dans la page About n'ont pas `aria-hidden="true"` | 1.1.1 Non-text Content | Mineur | `about.blade.php:31-34, 67-68, 102, 112` | Ajouter `aria-hidden="true"` |
| 4 | **Contraste insuffisant `zinc-400` sur fond clair** — `#a3a3a3` sur `#fff8f7` = ~2.7:1 (requis 4.5:1). Utilise pour les dates d'articles | 1.4.3 Contrast | Critique | `home.blade.php:31,84`, `articles/index.blade.php:67` | Passer a `zinc-500` ou `on-surface-variant` |
| 5 | **Contraste `outline` borderline** — `#8c716e` sur `#fff8f7` ~ 4.5:1, au seuil pour le texte normal | 1.4.3 Contrast | Majeur | `articles/show.blade.php:24,37` | Utiliser `on-surface-variant` |
| 6 | **Texte a `text-[10px]`** — En dessous de la taille minimale recommandee (12px) | 1.4.4 Resize Text | Majeur | `home.blade.php:27,31,82-84` | Augmenter a `text-xs` (12px) |
| 7 | **Compteurs animes affichent "0"** — Les stats montrent `x-text="count"` initialise a 0 ; un lecteur d'ecran ne captera que "0" | 1.3.1 Info and Relationships | Majeur | `home.blade.php:54-62` | Ajouter `sr-only` avec les vraies valeurs |

---

## 2. Operable (Utilisable)

| # | Probleme | Critere WCAG | Severite | Fichier(s) | Recommandation |
|---|----------|-------------|----------|------------|----------------|
| 8 | **Pas de lien "Skip to content"** — Les utilisateurs clavier doivent traverser toute la nav | 2.4.1 Bypass Blocks | Critique | `layouts/public.blade.php` | Ajouter un lien skip-to-content |
| 9 | **Liens overlay invisibles au focus clavier** — `<a class="absolute inset-0 z-10">` sans contenu textuel | 2.4.7 Focus Visible / 2.4.4 Link Purpose | Majeur | `home.blade.php:22,76`, `articles/index.blade.php:38` | Ajouter `aria-label` + `focus:ring` |
| 10 | **Menu mobile sans `aria-expanded`** — Le bouton ne communique pas l'etat ouvert/ferme | 2.4.3 Focus Order | Majeur | `navbar.blade.php:35` | Ajouter `x-bind:aria-expanded` + `aria-controls` |
| 11 | **Liens externes sans `rel="noopener noreferrer"`** — Les liens sociaux de la page About | Best Practice | Mineur | `about.blade.php:65,71,77,83` | Ajouter `rel="noopener noreferrer"` |
| 12 | **Filtres tags sans indication d'etat actif** — Le tag selectionne n'est pas annonce | 2.4.4 Link Purpose | Mineur | `articles/index.blade.php:21-31` | Ajouter `aria-current="page"` |

---

## 3. Understandable (Comprehensible)

| # | Probleme | Critere WCAG | Severite | Fichier(s) | Recommandation |
|---|----------|-------------|----------|------------|----------------|
| 13 | **Messages d'erreur non lies aux champs** — Les `@error` ne sont pas associes via `aria-describedby` | 3.3.1 Error Identification | Majeur | `article-comments.blade.php:56,69`, `contact-form.blade.php:26,39,51,62` | Ajouter `aria-describedby` + `role="alert"` |
| 14 | **Hierarchie des headings incoherente** — h1 -> h3 -> h4 (saute h2) | 1.3.1 Info and Relationships | Majeur | `home.blade.php:71`, `about.blade.php:23,59` | Respecter h1 -> h2 -> h3 |
| 15 | **Melange de langues sans `lang` attribute** — Pages auth en anglais sans `lang="en"` | 3.1.2 Language of Parts | Mineur | `pages/auth/login.blade.php` | Ajouter `lang="en"` ou traduire |
| 16 | **Champ select sans option placeholder** — "Developpement" pre-selectionne sans "Choisir..." | 3.3.2 Labels or Instructions | Mineur | `contact-form.blade.php:33-38` | Ajouter option disabled selected |

---

## 4. Robust

| # | Probleme | Critere WCAG | Severite | Fichier(s) | Recommandation |
|---|----------|-------------|----------|------------|----------------|
| 17 | **Landmarks manquants** — Pas de `role` ou `aria-label` sur les sections principales | 4.1.2 Name, Role, Value | Majeur | `home.blade.php`, `about.blade.php` | Utiliser `aria-labelledby` |
| 18 | **Navigation par tags sans landmark** — Les filtres ne sont pas dans un `<nav>` | 4.1.2 Name, Role, Value | Mineur | `articles/index.blade.php:20-31` | Envelopper dans `<nav aria-label>` |
| 19 | **Footer sans aria-label** | 4.1.2 Name, Role, Value | Majeur | `footer.blade.php:1` | Ajouter `aria-label="Pied de page"` |

---

## Verification des contrastes

| Element | Avant-plan | Arriere-plan | Ratio | Requis | Passe ? |
|---------|-----------|-------------|-------|--------|---------|
| Body text (`on-surface`) | `#23191b` | `#fff8f7` | **14.8:1** | 4.5:1 | Oui |
| Sous-texte (`on-surface-variant`) | `#58413f` | `#fff8f7` | **~9:1** | 4.5:1 | Oui |
| Dates articles (`zinc-400`) | `#a3a3a3` | `#fff8f7` | **~2.7:1** | 4.5:1 | Non |
| Outline text | `#8c716e` | `#fff8f7` | **~4.5:1** | 4.5:1 | Borderline |
| Primary sur blanc | `#87001e` | `#fff8f7` | **~8.5:1** | 4.5:1 | Oui |
| Footer text (`zinc-500`) | `#737373` | `#fff8f7` | **~4.6:1** | 4.5:1 | Borderline |
| Dark: body (`on-surface`) | `#f1dee0` | `#1a1113` | **~13:1** | 4.5:1 | Oui |
| Dark: primary | `#ffb3b3` | `#1a1113` | **~10:1** | 4.5:1 | Oui |
| Dark: zinc-400 text | `#a3a3a3` | `#171717` | **~7.4:1** | 4.5:1 | Oui |

---

## Navigation clavier

| Element | Tab Order | Entree/Espace | Escape | Probleme |
|---------|-----------|---------------|--------|----------|
| Navbar links | Correct | Navigation | — | — |
| Dark mode toggle | Focusable | Bascule | — | Pas de label, pas de focus visible distinctif |
| Menu mobile | Focusable | Ouvre | Ne ferme pas | Pas de `@keydown.escape` |
| Article cards (overlay) | Focus invisible | Navigation | — | Focus ring absent, label absent |
| Tag filters | Correct | Navigation | — | — |
| Formulaires | Correct | Submit | — | Erreurs non liees |
| Commentaires | Correct | Submit | — | Erreurs non liees |

---

## Lecteur d'ecran

| Element | Annonce comme | Probleme |
|---------|--------------|----------|
| Dark mode button | "button" (sans label) | Aucune indication de fonction |
| Mobile menu button | "button" (sans label) | Aucune indication de fonction |
| Article overlay link | "link" (sans texte) | Lien vide, inutilisable |
| Stats "0 articles" | "0 articles" | Valeur incorrecte avant animation |
| Material icons | Texte de l'icone lu | Devrait etre masque ou labellise |
