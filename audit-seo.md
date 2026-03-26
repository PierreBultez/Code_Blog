# Audit SEO Complet — <Code_Blog>

**Date :** 26 mars 2026
**Cible :** Référencement national France
**Site :** Blog technique de Pierre Bultez, développeur web freelance Laravel

---

## Résumé Exécutif

Le blog a une **structure technique propre** (URLs avec slugs, pagination, tags) mais il manque **pratiquement toute la couche SEO** : aucune meta description, aucune donnée structurée, pas de sitemap, pas de balises Open Graph, et — problème critique — la **langue est déclarée en anglais** alors que le contenu est en français. Ces lacunes empêchent Google de comprendre, indexer et mettre en avant correctement le contenu.

**Top 3 des priorités :**

1. Corriger la langue (`lang="fr"` + `APP_LOCALE=fr`) — impact immédiat sur le ciblage France
2. Ajouter meta descriptions + Open Graph sur toutes les pages — visibilité SERP et partage social
3. Implémenter sitemap.xml + données structurées JSON-LD — indexation et rich snippets

**Évaluation globale :** Fondations solides, mais SEO à construire quasi entièrement.

---

## Tableau des Opportunités de Mots-Clés

| Mot-clé | Difficulté | Score Opportunité | Intent | Type de contenu recommandé |
|---------|-----------|-------------------|--------|---------------------------|
| `tutoriel laravel français` | Modérée | **Élevé** | Informationnel | Articles tutoriels pas-à-pas |
| `laravel livewire tutoriel` | Modérée | **Élevé** | Informationnel | Guide pratique avec code |
| `déployer application laravel VPS` | Facile | **Élevé** | Informationnel | Guide complet |
| `laravel 13 nouveautés` | Facile | **Élevé** | Informationnel | Article d'actualité |
| `tailwind css dark mode` | Modérée | Moyen | Informationnel | Tutoriel technique |
| `livewire 4 migration` | Facile | **Élevé** | Informationnel | Guide migration |
| `formulaire contact laravel livewire` | Facile | **Élevé** | Informationnel | Tutoriel |
| `pest php tests laravel` | Facile | Moyen | Informationnel | Tutoriel |
| `github actions laravel déploiement` | Facile | **Élevé** | Informationnel | Guide CI/CD |
| `freelance développeur laravel france` | Difficile | Moyen | Commercial | Page "À propos" optimisée |
| `laravel eloquent bonnes pratiques` | Modérée | Moyen | Informationnel | Article best practices |
| `flux ui composants livewire` | Facile | **Élevé** | Informationnel | Showcase + tutoriel |
| `configurer brevo smtp laravel` | Facile | **Élevé** | Informationnel | Tutoriel |
| `blog laravel de zéro` | Modérée | Moyen | Informationnel | Série d'articles |
| `alpine js exemples pratiques` | Modérée | Moyen | Informationnel | Tutoriel |
| `sécuriser application laravel` | Modérée | Moyen | Informationnel | Guide sécurité |
| `laravel queue jobs tutoriel` | Facile | Moyen | Informationnel | Tutoriel |
| `développeur web vaucluse` | Facile | Moyen | Commercial | Page "À propos" + local SEO |
| `php 8.4 nouvelles fonctionnalités` | Modérée | Moyen | Informationnel | Article |
| `vite laravel configuration` | Facile | Moyen | Informationnel | Tutoriel |

---

## Problèmes On-Page

| Page | Problème | Sévérité | Correction recommandée |
|------|----------|----------|----------------------|
| **Toutes** | `<html lang="en">` au lieu de `fr` | **Critique** | Changer `APP_LOCALE=fr` dans `.env` |
| **Toutes** | Aucune `<meta name="description">` | **Critique** | Ajouter des descriptions uniques par page |
| **Toutes** | Aucune balise Open Graph (og:title, og:description, og:image) | **Élevé** | Ajouter les OG tags dans le layout |
| **Toutes** | Aucune balise Twitter Card | **Élevé** | Ajouter `twitter:card`, `twitter:title`, etc. |
| **Toutes** | Aucune URL canonique (`<link rel="canonical">`) | **Élevé** | Ajouter `<link rel="canonical" href="...">` |
| **Toutes** | Aucune donnée structurée JSON-LD | **Élevé** | Schema Article, WebSite, Person, BreadcrumbList |
| **Accueil** | Title = `Just another <code_blog>` — pas de mot-clé cible | **Élevé** | Title orienté : "Blog Dev Laravel &#124; Pierre Bultez" |
| **Articles** | Title = `Manuscrits <Code_Blog>` — pas descriptif pour le SEO | **Élevé** | "Articles & Tutoriels Laravel &#124; <Code_Blog>" |
| **Article show** | Pas de `meta_description` dynamique (champ `excerpt`) | **Élevé** | Utiliser `excerpt` comme meta description |
| **Toutes** | Pas de breadcrumbs (ni visuels, ni en JSON-LD) | Moyen | Ajouter un fil d'Ariane |
| **Toutes** | Pas de `<link rel="alternate" type="application/rss+xml">` | Moyen | Créer et lier le flux RSS |
| **Footer** | Liens RSS et GitHub pointent vers `#` | Moyen | Liens fonctionnels ou suppression |
| **Accueil** | H1 = "Notes d'un dev en freelance" — bon, mais pas de mot-clé Laravel | Bas | Envisager d'inclure "Laravel" |
| **Article show** | Pas de liens articles connexes / navigation prev/next | Bas | Ajouter pour le maillage interne |
| **Pagination** | Pas de `rel="next"` / `rel="prev"` | Bas | Bien que déprécié par Google, reste utile |

---

## Analyse des Lacunes de Contenu

| Thème / Mot-clé | Pourquoi c'est important | Format recommandé | Priorité | Effort |
|-----------------|------------------------|-------------------|----------|--------|
| Série "Créer un blog Laravel de A à Z" | Ton propre projet comme cas d'étude — très engageant | Série d'articles (pillar page) | **Élevé** | Substantiel |
| Déploiement Laravel sur VPS (OVH) | Tu as l'expertise, peu de contenu FR sur ce sujet précis | Guide complet | **Élevé** | Modéré |
| Guide Livewire 4 en français | Quasi-absent en FR, forte demande | Pillar page + articles | **Élevé** | Substantiel |
| Flux UI : composants pratiques | Nouveau produit, très peu de contenu FR | Showcase / tutoriel | **Élevé** | Quick win |
| CI/CD Laravel avec GitHub Actions | Ton workflow actuel comme contenu | Tutoriel | Moyen | Modéré |
| Configurer Brevo SMTP avec Laravel | Sujet très spécifique, peu de concurrence | Tutoriel | Moyen | Quick win |
| Comparatif frameworks PHP 2026 | Trafic informationnel, awareness | Article d'opinion | Moyen | Modéré |
| Page glossaire / lexique Laravel | SEO longue traîne, autorité thématique | Glossaire | Bas | Modéré |

---

## Checklist SEO Technique

| Vérification | Statut | Détails |
|-------------|--------|---------|
| `lang` HTML correct | **Fail** | `lang="en"` au lieu de `"fr"` — `APP_LOCALE=en` dans `.env` |
| HTTPS | **Warning** | `APP_URL=http://localhost` — à vérifier en production |
| Sitemap XML | **Fail** | Aucun fichier `sitemap.xml`, aucune génération dynamique |
| robots.txt | **Warning** | Existe mais ne référence pas le sitemap |
| Meta descriptions | **Fail** | Absentes sur toutes les pages |
| Balises canoniques | **Fail** | Absentes sur toutes les pages |
| Open Graph | **Fail** | Aucune balise OG |
| Données structurées | **Fail** | Aucun JSON-LD (Article, WebSite, Person, Breadcrumb) |
| Balise `<time>` avec `datetime` | **Pass** | Présente dans les articles avec format ISO |
| URLs propres (slugs) | **Pass** | `/articles/{slug}` — bien |
| Responsive / Mobile | **Pass** | Viewport correct, design responsive Tailwind |
| Trailing slash redirect | **Pass** | .htaccess supprime les trailing slashes (301) |
| Performances fonts | **Warning** | Google Fonts + Material Symbols chargés en externe (~500KB+) |
| Alt text images | **Warning** | Photo de profil a un alt, mais pas vérifié pour le contenu des articles |
| Flux RSS | **Fail** | Lien dans le footer mais pointe vers `#` |
| Pagination SEO | **Warning** | Pagination présente mais sans `rel="next/prev"` |
| Maillage interne | **Warning** | Pas de liens entre articles, pas d'articles connexes |

---

## Comparaison avec les Concurrents

| Dimension | <Code_Blog> | Laravel France | Grafikart.fr | Dev.to (FR) |
|-----------|------------|----------------|-------------|-------------|
| Meta descriptions | Non | Oui | Oui | Oui |
| Open Graph | Non | Oui | Oui | Oui |
| Sitemap XML | Non | Oui | Oui | Oui |
| Données structurées | Non | Oui | Oui | Oui |
| RSS Feed | Non | Oui | Oui | Oui |
| Fréquence publication | Faible | Régulière | Très régulière | Continue |
| Profondeur contenu | Courte | Moyenne | Élevée | Variable |
| Fil d'Ariane | Non | Oui | Oui | Non |
| Niche/Spécialisation | Laravel FR freelance | Laravel FR | Dev web FR | Général |

**Avantage concurrentiel potentiel :** La niche "freelance Laravel en France, retour d'expérience terrain" est peu couverte. Les concurrents sont soit trop généralistes (Dev.to), soit orientés tutoriel pur (Grafikart). L'angle "carnet de bord" est différenciant.

---

## Plan d'Action Priorisé

### Quick Wins (cette semaine)

| # | Action | Impact | Effort |
|---|--------|--------|--------|
| 1 | Changer `APP_LOCALE=fr` + `APP_FAKER_LOCALE=fr_FR` | **Élevé** | 5 min |
| 2 | Ajouter un champ `meta_description` au modèle Article | **Élevé** | 30 min |
| 3 | Créer un partial `seo.blade.php` (meta desc, canonical, OG, Twitter) | **Élevé** | 1h |
| 4 | Ajouter les données structurées JSON-LD (WebSite + Article) | **Élevé** | 1h |
| 5 | Corriger les titres de page pour inclure des mots-clés | **Élevé** | 30 min |
| 6 | Corriger les liens RSS et GitHub dans le footer | Moyen | 10 min |
| 7 | Mettre à jour `robots.txt` avec référence au sitemap | Moyen | 5 min |

### Investissements Stratégiques (ce trimestre)

| # | Action | Impact | Effort |
|---|--------|--------|--------|
| 8 | Générer un sitemap XML dynamique | **Élevé** | 2h |
| 9 | Créer un flux RSS fonctionnel | Moyen | 2h |
| 10 | Ajouter un fil d'Ariane visuel + JSON-LD | Moyen | 1h |
| 11 | Optimiser le chargement des fonts (self-host ou subset) | Moyen | 3h |
| 12 | Ajouter une section "Articles connexes" sur la page article | Moyen | 2h |
| 13 | Créer une pillar page "Guide Laravel" avec topic cluster | **Élevé** | Multi-jours |
| 14 | Mettre en place Google Search Console + soumettre le sitemap | **Élevé** | 30 min |
| 15 | Produire 2 articles/mois ciblant les mots-clés identifiés | **Élevé** | Continu |

---

## Sources

- [Structured Data & Schema Markup for SEO in 2026](https://doesinfotech.com/the-role-of-structured-data-schema-markup-in-seo/)
- [SEO Best Practices That Actually Work In 2026](https://bostoninstituteofanalytics.org/blog/seo-best-practices-that-actually-work-in-2026/)
- [Sitemap Best Practices 2026](https://respona.com/blog/sitemap-best-practices/)
- [The Complete SEO Guide for 2026](https://seo-io.com/blog/seo-complete-guide-2026.html)
- [Laravel France](https://laravel-france.com/posts/apprendre-laravel-12)
