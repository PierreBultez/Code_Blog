# Implementation des corrections OWASP

**Date :** 2026-03-28
**Audit de reference :** [owasp-audit.md](owasp-audit.md)
**Tests :** 118 passes, 0 echecs apres implementation

---

## Point 1 — Sanitisation HTML du contenu articles (OWASP A03/A08)

**Probleme :** Le contenu des articles etait affiche avec `{!! $article->content !!}` sans sanitisation serveur. Un contenu malveillant injecte en base s'executait directement chez les visiteurs.

**Solution :** Installation de `stevebauman/purify` (v6.3, wrapper Laravel de HTMLPurifier) avec purification au moment du stockage.

**Fichiers modifies :**
- `composer.json` — ajout de `stevebauman/purify`
- `config/purify.php` — configuration des tags HTML autorises, adaptee au contenu TinyMCE
- `app/Livewire/Dashboard/ArticleForm.php` — appel `Purify::clean()` avant enregistrement

**Configuration HTMLPurifier :**
- Tags autorises : `h1-h6`, `p`, `a`, `img`, `ul/ol/li`, `table/*`, `pre`, `code`, `blockquote`, `figure`, `hr`, `sup`, `sub`, `mark`, formatage inline
- Tags interdits : `script`, `style`, `iframe`, `form`, `input`, `textarea`, `select`, `button`, `object`, `embed`
- Schemas URI : uniquement `http`, `https`, `mailto`
- Liens : `target="_blank"` automatique + `rel="nofollow"`

**Tests ajoutes :** `tests/Feature/ArticlePurifyTest.php`
- Suppression des balises `<script>`
- Suppression des attributs `onmouseover` (event handlers)
- Preservation des tags autorises (h2, strong, a, pre, code, ul, li)
- Suppression des `<iframe>`, `<form>`, `<input>`

---

## Point 2 — Rate limiting et honeypot sur le formulaire de contact (OWASP A04)

**Probleme :** Le formulaire de contact n'avait aucune protection anti-spam ni rate limiting. Un bot pouvait envoyer des milliers de mails via le SMTP Brevo.

**Solution :** Reproduction du pattern deja utilise dans `ArticleComments` : honeypot + `RateLimiter` par IP.

**Fichiers modifies :**
- `app/Livewire/ContactForm.php` — ajout propriete `$honeypot`, verification avant traitement, `RateLimiter` (3 envois / 300s par IP)
- `resources/views/livewire/contact-form.blade.php` — ajout champ honeypot cache (`class="hidden"`, `tabindex="-1"`, `autocomplete="off"`)

**Comportement :**
- Si le honeypot est rempli : le formulaire simule un succes sans envoyer de mail (le bot croit avoir reussi)
- Si le rate limiter est depasse : message d'erreur sur le champ message
- Apres envoi reussi : le compteur IP est incremente avec un TTL de 300 secondes

**Tests ajoutes :** dans `tests/Feature/ContactFormTest.php`
- Les soumissions honeypot n'envoient aucun mail
- Le rate limiter bloque apres 3 envois

---

## Point 3 — Middleware SecurityHeaders (OWASP A05)

**Probleme :** Aucun header de securite HTTP n'etait envoye par le serveur. Le site etait vulnerable au clickjacking, MIME sniffing, et ne forcait pas HTTPS.

**Solution :** Creation d'un middleware `SecurityHeaders` enregistre globalement.

**Fichiers modifies :**
- `app/Http/Middleware/SecurityHeaders.php` — nouveau middleware
- `bootstrap/app.php` — enregistrement global via `$middleware->append()`

**Headers ajoutes :**
| Header | Valeur | Protection |
|--------|--------|------------|
| `X-Content-Type-Options` | `nosniff` | Empeche le MIME sniffing |
| `X-Frame-Options` | `DENY` | Bloque l'inclusion en iframe (anti-clickjacking) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limite les fuites de referrer |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Desactive les API sensibles |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Force HTTPS (uniquement si connexion securisee) |

**Note :** Le Content-Security-Policy (CSP) n'a pas ete ajoute car il necessite un travail dedie avec tests navigateur (compatibilite Livewire, Alpine.js, Google Fonts, inline scripts dark mode).

**Tests ajoutes :** `tests/Feature/SecurityHeadersTest.php`
- Verification des headers sur les pages publiques
- Verification des headers sur les pages authentifiees

---

## Point 4 — Chiffrement des sessions (OWASP A02)

**Probleme :** `SESSION_ENCRYPT=false` dans la configuration. Les donnees de session en base etaient en clair.

**Solution :** Modification de `.env.example` pour definir `SESSION_ENCRYPT=true` comme valeur par defaut.

**Fichiers modifies :**
- `.env.example` — `SESSION_ENCRYPT=true`

**Action manuelle requise :** Modifier le `.env` de production pour appliquer le changement.

---

## Point 5 — Rate limiting sur l'upload TinyMCE (OWASP A04)

**Probleme :** L'endpoint `POST /tinymce/upload` n'avait aucun throttle. Un compte compromis pouvait saturer le disque.

**Solution :** Ajout du middleware `throttle:10,1` (10 requetes par minute) sur la route.

**Fichiers modifies :**
- `routes/web.php` — ajout de `->middleware('throttle:10,1')` sur la route `tinymce.upload`

---

## Point 6 — Audit logging des actions sensibles (OWASP A09)

**Probleme :** Aucune trace des connexions, deconnexions, echecs d'authentification ou suppressions de contenu.

**Solution :** Listeners sur les evenements d'auth Laravel + logging explicite dans les actions de suppression.

**Fichiers modifies :**
- `app/Providers/AppServiceProvider.php` — methode `configureSecurityLogging()` avec listeners sur `Login`, `Failed`, `Logout`
- `app/Livewire/Dashboard/ArticleList.php` — log avant suppression d'article
- `app/Livewire/Dashboard/CommentList.php` — log avant suppression de commentaire
- `app/Livewire/Dashboard/TagList.php` — log avant suppression de tag

**Evenements logges :**
| Evenement | Niveau | Donnees |
|-----------|--------|---------|
| Login reussi | `info` | user_id, email, IP |
| Login echoue | `warning` | email tente, IP |
| Logout | `info` | user_id, IP |
| Suppression article | `info` | article_id, titre, user_id, IP |
| Suppression commentaire | `info` | comment_id, auteur, article_id, user_id, IP |
| Suppression tag | `info` | tag_id, nom, user_id, IP |

**Canal :** `single` (fichier `storage/logs/laravel.log`)

---

## Point 7 — Refactoring @mention dans un accesseur Comment (OWASP A03)

**Probleme :** La logique de rendu des @mentions etait directement dans le template Blade avec `{!! preg_replace(..., e($comment->content)) !!}`. Le pattern fonctionnait mais etait fragile et non reutilisable.

**Solution :** Deplacement dans un accesseur Eloquent `formatted_content` sur le modele `Comment`.

**Fichiers modifies :**
- `app/Models/Comment.php` — ajout de l'accesseur `formattedContent()`
- `resources/views/livewire/article-comments.blade.php` — remplacement par `{!! $comment->formatted_content !!}`

**Securite preservee :** `e()` est toujours appele en premier pour echapper le HTML, puis `preg_replace` ne wrappe que les tokens `\w+` dans des `<span>`.

**Tests ajoutes :** dans `tests/Feature/CommentTest.php`
- Les @mentions sont correctement formatees en HTML
- Le contenu HTML malveillant est echappe (XSS prevention)

---

## Reste a faire

| Action | Priorite |
|--------|----------|
| Configurer un Content-Security-Policy (CSP) adapte a Livewire/Alpine/Google Fonts | Haute |
| Ajouter un cache-buster sur les images OG (point 9 de l'audit) | Faible |
| Evaluer l'ajout de SoftDeletes sur Article et Comment (point 10 de l'audit) | Faible |
