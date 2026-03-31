# Code Blog

Un blog technique personnel construit avec Laravel et Livewire. Conçu pour documenter des apprentissages, solutions et réflexions de développeur.

## Stack technique

- **Backend** : Laravel 13, PHP 8.4+
- **Composants temps réel** : Livewire 4.1 + Flux UI
- **Authentification** : Laravel Fortify (2FA inclus)
- **Base de données** : SQLite (configurable MySQL/PostgreSQL)
- **Frontend** : Tailwind CSS 4, Alpine.js, Vite
- **Éditeur** : TinyMCE (WYSIWYG)
- **Coloration syntaxique** : PrismJS
- **Tests** : PestPHP

## Fonctionnalités

**Partie publique**
- Page d'accueil avec article mis en avant et statistiques
- Liste des articles avec filtrage par tag
- Temps de lecture estimé par article
- Page À propos

**Dashboard (authentifié)**
- Création et édition d'articles (titre, extrait, contenu riche, tags, statut)
- Gestion des tags
- Génération automatique de slugs
- Publication avec horodatage
- Mode sombre

## Installation

### Prérequis

- PHP 8.4+
- Composer
- Node.js 20+

### Mise en place

```bash
git clone <repo-url> code-blog
cd code-blog

# Installation complète (dépendances, clé, migrations, assets)
composer run setup
```

### Développement

```bash
composer run dev
```

Lance en parallèle : serveur PHP, queue worker, log watcher et serveur Vite.

L'application est accessible sur [http://localhost:8000](http://localhost:8000).

## Commandes utiles

```bash
# Lancer les tests
composer run test

# Formater le code (Laravel Pint)
composer run lint

# Vérifier le formatage sans modifier
composer run lint:check

# Compiler les assets pour la production
npm run build
```

## Structure du projet

```
app/
├── Http/Controllers/   # Contrôleurs publics (Home, Article, Page)
├── Livewire/Dashboard/ # Composants Livewire (ArticleForm, ArticleList, TagForm, TagList)
├── Models/             # Article, Tag, User
resources/views/
├── articles/           # Vue liste et détail
├── livewire/dashboard/ # Vues des composants Livewire
├── layouts/            # Layouts public, app, auth
database/
├── migrations/         # articles, tags, article_tag (pivot)
tests/Feature/          # Tests PestPHP (CRUD articles, auth, settings)
```

## Variables d'environnement

Copier `.env.example` en `.env` et ajuster selon l'environnement :

```bash
cp .env.example .env
```

Les variables principales :

| Variable | Description |
|---|---|
| `APP_URL` | URL de l'application |
| `DB_CONNECTION` | Driver de base de données (`sqlite` par défaut) |
| `MAIL_*` | Configuration e-mail (vérification, reset) |

## Licence

Usage personnel autorisé avec crédit.
