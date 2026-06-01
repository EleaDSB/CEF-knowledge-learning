# Dossier de Projet

---

## Page de présentation

**Projet :** Knowledge Learning — Plateforme e-learning / e-commerce

**Auteur :** Elea De Sousa

**Formation :** Développeur Web et Web Mobile

**Organisme de formation :** Centre Européen de Formation (CEF)

**Date de rendu :** Mai 2026

**Repository GitHub :** https://github.com/EleaDSB/CEF-knowledge-learning

---

## Sommaire

1. [Résumé du projet](#1-résumé-du-projet)
2. [Conception du site](#2-conception-du-site)
3. [Documentation du code](#3-documentation-du-code)
4. [Informations complémentaires](#4-informations-complémentaires)

---

## 1. Résumé du projet

Knowledge Learning est une plateforme d'e-learning et d'e-commerce développée dans le cadre d'un projet de fin de formation. La société fictive Knowledge, spécialisée dans l'édition de supports de formation, souhaitait proposer ses contenus en ligne afin de permettre à ses clients d'étudier en toute autonomie.

L'application a été développée en PHP 8.5 avec le framework Symfony 8, selon une architecture MVC stricte. Elle s'organise autour d'une hiérarchie à trois niveaux : des thèmes de formation (Musique, Informatique, Jardinage, Cuisine) contenant des cursus, eux-mêmes composés de leçons.

Les fonctionnalités principales sont : l'inscription avec activation du compte par email via un token sécurisé valable 24 heures, la connexion avec gestion des rôles administrateur et client, l'achat de cursus ou de leçons à l'unité via Stripe Checkout en mode sandbox, l'accès conditionnel aux leçons selon les achats effectués grâce à un voter Symfony, le suivi de progression leçon par leçon, et l'obtention automatique d'une certification lorsque toutes les leçons d'un thème ont été validées.

La base de données comprend sept tables dotées de colonnes d'audit (created_at, updated_at, created_by, updated_by). Les mots de passe sont hashés avec l'algorithme argon2id. Le projet est couvert par une suite de 50 tests unitaires et fonctionnels (92 assertions, zéro échec) réalisés avec PHPUnit, et le code est intégralement documenté en anglais avec des commentaires PHPDoc générés via phpDocumentor. Le versioning a été assuré avec Git et GitHub, selon un workflow structuré en branches de fonctionnalités.

---

## 2. Conception du site

### 2.1 Repository GitHub

Le code source de l'application est disponible publiquement à l'adresse suivante :

**https://github.com/EleaDSB/CEF-knowledge-learning**

Le dépôt contient :
- Le code source complet de l'application Symfony
- Un fichier `README.md` à la racine avec les prérequis, les instructions d'installation et de lancement, les comptes de test, et la procédure d'exécution des tests
- Les migrations de base de données (`migrations/`)
- Les fixtures de démonstration (`src/DataFixtures/`)
- La suite de tests complète (`tests/`)
- Le dossier de documentation (`docs/`)

### 2.2 Modèle Physique de Données

Le schéma de la base de données est disponible dans le dossier `docs/` :

- `docs/mpd.png` — diagramme PNG (150 dpi)
- `docs/mpd.pdf` — version vectorielle PDF

Il représente les 7 tables de l'application et leurs relations : `user`, `theme`, `cursus`, `lesson`, `purchase`, `lesson_progress`, `certification`.

### 2.3 Architecture

L'application suit le pattern MVC imposé par Symfony :

| Couche | Contenu |
|--------|---------|
| **Model** | 7 entités Doctrine + 7 repositories |
| **View** | 21 templates Twig |
| **Controller** | 7 controllers (Home, Security, Registration, Catalog, Shop, Certification, Admin) |

Des composants transverses complètent l'architecture : un `LessonVoter` pour le contrôle d'accès aux leçons, un `MailerService` pour l'envoi des emails d'activation, et un `EntityLifecycleSubscriber` pour les colonnes d'audit.

---

## 3. Documentation du code

### 3.1 Documentation générée

L'ensemble du code PHP est documenté avec des commentaires PHPDoc en anglais (`@param`, `@return`). La documentation HTML a été générée avec **phpDocumentor v3** et est disponible dans le dossier `docs/` :

- `docs/index.html` — point d'entrée de la documentation (32 fichiers PHP analysés)

### 3.2 Commentaires dans le code

Chaque classe et méthode dispose d'un bloc PHPDoc décrivant son rôle, ses paramètres et sa valeur de retour. Les commentaires inline expliquent les choix techniques non évidents (gestion du double-achat, déclenchement automatique de la certification, etc.).

### 3.3 Tests comme documentation

Les noms des méthodes de test sont auto-documentés et décrivent explicitement le comportement attendu :

- `testUnverifiedUserCannotPurchase()`
- `testPurchaseSuccessCreatesPurchaseRecord()`
- `testAccessToUnpurchasedLessonIsBlocked()`
- `testValidTokenActivatesAccount()`

---

## 4. Informations complémentaires

### 4.1 Sécurité

- Protection CSRF sur tous les formulaires
- Validation du mot de passe : minimum 8 caractères, avec au moins une majuscule, une minuscule, un chiffre et un caractère spécial
- Hashage des mots de passe (argon2id via Symfony PasswordHasher)
- Contrôle d'accès granulaire via `LessonVoter`
- Vérification du compte avant tout achat

### 4.2 Workflow Git

Le projet a été développé avec un workflow GitHub structuré :

- Branches `feature/*` pour chaque fonctionnalité
- Intégration dans `develop` via pull requests
- Release dans `main` après validation

### 4.3 Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | `admin@knowledge-learning.fr` | `Admin1234!` |
| Client | `client@example.com` | `Client1234!` |

Pour les paiements : carte Stripe sandbox `4242 4242 4242 4242`, date future, CVC quelconque.

### 4.4 Stack technique

| Couche | Technologie | Version |
|--------|-------------|---------|
| Langage | PHP | 8.5 |
| Framework | Symfony | 8.0 |
| ORM | Doctrine | 3.6 |
| Base de données | MySQL | 9.6 |
| Paiement | Stripe PHP SDK | 20.1 |
| Templates | Twig | 3.x |
| Tests | PHPUnit | 13.1 |
| Versioning | Git / GitHub | — |
