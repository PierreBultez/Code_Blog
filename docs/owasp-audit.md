# Audit de securite OWASP - Blog Laravel 13

**Date :** 2026-03-28
**Referentiel :** OWASP Top 10 (2021)
**Stack :** Laravel 13, Livewire 4, Fortify 1, TailwindCSS 4, Alpine.js 3

---

## Tableau de synthese

| Severite    | Nb | Resume                                                                 |
|-------------|:--:|------------------------------------------------------------------------|
| **CRITIQUE** | 2  | Contenu article non-sanitise (XSS), pas de rate limiting sur contact   |
| **HAUTE**    | 3  | Pas de headers de securite, session non chiffree, pas d'audit logging  |
| **MOYENNE**  | 3  | Pas de honeypot sur contact, upload TinyMCE sans rate limit, pas de CSP |
| **FAIBLE**   | 2  | OG image cache longue, pas de soft deletes                             |
| **OK**       | 8  | CSRF, SQL injection, mass assignment, mots de passe, 2FA, honeypot commentaires, auth Fortify, cookies |

---

## CRITIQUE

### 1. XSS via contenu article non-sanitise (OWASP A03/A08)

**Fichier :** `resources/views/articles/show.blade.php:44`

```blade
{!! $article->content !!}
```

Le contenu des articles est affiche sans echappement. TinyMCE filtre cote client, mais ce n'est pas une protection cote serveur. En cas de compromission de la session admin ou d'injection directe en base, du JavaScript arbitraire s'execute chez tous les visiteurs.

**Recommandation :** Sanitiser le HTML cote serveur avec `stevebauman/purify` (wrapper Laravel de HTMLPurifier, supporte Laravel 13) avant le stockage ou l'affichage. Le package fournit des Eloquent casts pour purifier automatiquement au niveau du modele.

---

### 2. Formulaire de contact sans rate limiting (OWASP A04)

**Fichier :** `app/Livewire/ContactForm.php`

Aucun `RateLimiter`, aucun throttle, aucun honeypot. Un bot peut :
- Envoyer des milliers de mails via le SMTP Brevo (epuiser le quota, risque de blacklist)
- Spammer le destinataire
- Surcharger le serveur

**Recommandation :** Ajouter un `Throttle` Livewire et un champ honeypot, comme deja fait pour les commentaires.

---

## HAUTE

### 3. Aucun header de securite HTTP (OWASP A05)

**Fichier :** `bootstrap/app.php:28-30` (middleware vide)

Headers manquants :
- `Content-Security-Policy` (CSP) - protection XSS majeure
- `X-Frame-Options` / `frame-ancestors` - anti-clickjacking
- `X-Content-Type-Options: nosniff` - anti-MIME sniffing
- `Strict-Transport-Security` (HSTS) - force HTTPS
- `Referrer-Policy`
- `Permissions-Policy`

**Recommandation :** Creer un middleware `SecurityHeaders` ou utiliser `bepsvpt/secure-headers`.

---

### 4. Sessions non chiffrees (OWASP A02)

**Fichier :** `.env:32`

```
SESSION_ENCRYPT=false
```

Les donnees de session en base sont en clair. En cas de fuite de la base, les sessions sont exploitables.

**Recommandation :** `SESSION_ENCRYPT=true` en production.

---

### 5. Pas d'audit logging des actions sensibles (OWASP A09)

Aucune trace des actions critiques :
- Connexions reussies/echouees
- Suppressions d'articles/commentaires/tags
- Uploads de fichiers
- Tentatives de brute force

**Recommandation :** Logger les evenements de securite (au minimum les auth events et les suppressions).

---

## MOYENNE

### 6. Upload TinyMCE sans rate limiting (OWASP A04)

**Fichier :** `app/Http/Controllers/TinyMceUploadController.php`

L'endpoint `POST /tinymce/upload` est protege par `auth`, mais aucun throttle. Un compte compromis pourrait remplir le disque.

**Recommandation :** Ajouter un `throttle:10,1` en middleware de route.

---

### 7. Formulaire de contact sans honeypot (OWASP A04)

Contrairement au formulaire de commentaires (qui a un honeypot bien implemente), le formulaire de contact n'en a pas.

**Recommandation :** Ajouter un champ honeypot identique a celui des commentaires.

---

### 8. Rendu des commentaires avec regex + {!! (OWASP A03)

**Fichier :** `resources/views/livewire/article-comments.blade.php:24`

```blade
{!! preg_replace('/@(\w+)/', '<span ...>@$1</span>', e($comment->content)) !!}
```

Le pattern est techniquement sur car `e()` est appele en premier (echappement HTML), puis le `preg_replace` ne wrappe que des `\w+` (alphanumeriques). Risque faible mais le pattern `{!! !!}` avec du contenu utilisateur merite une attention particuliere.

**Recommandation :** Surveiller ce pattern lors des evolutions. Envisager de deplacer cette logique dans un accesseur du modele `Comment`.

---

## FAIBLE

### 9. Cache OG image longue duree

**Fichier :** `app/Http/Controllers/OgImageController.php`

Les images OG sont cachees 1 an sans mecanisme d'invalidation en cas de modification du contenu.

**Recommandation :** Ajouter un cache-buster base sur le `updated_at` de l'article.

---

### 10. Pas de soft deletes

Les modeles `Article`, `Comment`, `Tag` utilisent des hard deletes. Une suppression accidentelle est irreversible.

**Recommandation :** Envisager `SoftDeletes` sur `Article` et `Comment`.

---

## CE QUI EST BIEN FAIT

| Domaine                    | Detail                                                                 |
|----------------------------|------------------------------------------------------------------------|
| **CSRF**                   | Toutes les routes web protegees, Livewire gere automatiquement         |
| **Injection SQL**          | Eloquent partout, aucun `DB::raw` avec input utilisateur               |
| **Mass assignment**        | `$fillable` defini sur tous les modeles                                |
| **Mots de passe**          | Bcrypt 12 rounds, regles strictes en prod (12 chars, mixte, uncompromised) |
| **2FA**                    | Fortify TOTP active avec confirmation                                  |
| **Rate limit auth**        | 5 tentatives/min sur login et 2FA                                      |
| **Honeypot commentaires**  | Champ cache anti-bot                                                   |
| **Cookies**                | `httpOnly: true`, `sameSite: lax`, serialisation JSON                  |
| **Registration desactivee**| Pas d'inscription publique, surface d'attaque reduite                  |
| **Validation**             | Presente sur tous les formulaires avec des regles strictes             |
| **Fichiers .env**          | Non trackes par git                                                    |
| **Upload images**          | Validation type MIME, dimensions, taille max, noms aleatoires          |

---

## Plan d'action recommande (par priorite)

| # | Action                                                              | Effort  | Impact   | Statut |
|---|---------------------------------------------------------------------|---------|----------|--------|
| 1 | Installer `stevebauman/purify` et sanitiser le contenu articles      | ~30 min | Critique | Fait   |
| 2 | Ajouter rate limiting + honeypot au formulaire de contact            | ~15 min | Critique | Fait   |
| 3 | Creer un middleware `SecurityHeaders` (CSP, HSTS, X-Frame...)       | ~30 min | Haut     | Fait   |
| 4 | Passer `SESSION_ENCRYPT=true` en prod                               | 1 min   | Haut     | Fait   |
| 5 | Ajouter throttle sur l'upload TinyMCE                               | 5 min   | Moyen    | Fait   |
| 6 | Implementer du logging sur les actions admin                         | ~45 min | Haut     | Fait   |
| 7 | Deplacer la logique regex @mention dans un accesseur du modele Comment | ~10 min | Moyen    | Fait   |

---

## Methodologie

Audit realise en analysant :
- La structure du projet et les dependances (`composer.json`, `package.json`)
- Les routes (`routes/web.php`, `routes/settings.php`, `bootstrap/app.php`)
- Les controllers et composants Livewire
- Les modeles Eloquent et migrations
- Les templates Blade (formulaires, affichage de contenu)
- La configuration (`config/auth.php`, `config/session.php`, `config/fortify.php`, `config/livewire.php`)
- Les variables d'environnement (`.env.example`)
- Le middleware stack et les protections existantes
