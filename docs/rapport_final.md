# Rapport Final — Knowledge Learning
## Projet de fin de formation — Développement Web

**Auteur :** Elea De Sousa  
**Formation :** Développeur Web et Web Mobile — CEF  
**Date de rendu :** 11 mai 2026  
**Dépôt GitHub :** https://github.com/EleaDSB/CEF-knowledge-learning

---

## 1. Présentation du projet

Knowledge Learning est une plateforme d'e-learning et d'e-commerce développée en Symfony 7. Elle permet à des apprenants de découvrir un catalogue de formations organisées par thèmes, d'acheter des cursus ou des leçons à l'unité via Stripe, de suivre leur progression leçon par leçon, et d'obtenir automatiquement une certification lorsque toutes les leçons d'un thème ont été validées.

L'application a été construite selon une architecture MVC stricte, avec une séparation claire entre la logique métier (services, voters, repositories) et la présentation (templates Twig). Le développement a suivi un workflow Git structuré en branches de fonctionnalités fusionnées dans `develop`, puis en `main` via pull requests.

---

## 2. Stack technique

| Couche | Technologie | Version |
|---|---|---|
| Langage | PHP | 8.5 |
| Framework | Symfony | 8.0 |
| ORM | Doctrine | 3.6 |
| Base de données | MySQL | 9.6 |
| Paiement | Stripe PHP SDK | 20.1 |
| Moteur de templates | Twig | 3.x |
| Tests | PHPUnit | 13.1 |
| Gestion de versions | Git / GitHub | — |

---

## 3. Architecture de l'application

### 3.1 Structure des sources

```
src/
├── Controller/
│   ├── AdminController.php        # Backoffice (ROLE_ADMIN)
│   ├── CatalogController.php      # Catalogue public
│   ├── CertificationController.php
│   ├── HomeController.php
│   ├── RegistrationController.php
│   ├── SecurityController.php
│   └── ShopController.php         # Paiement Stripe
├── Entity/
│   ├── Traits/TimestampableTrait.php
│   ├── User.php
│   ├── Theme.php
│   ├── Cursus.php
│   ├── Lesson.php
│   ├── Purchase.php
│   ├── LessonProgress.php
│   └── Certification.php
├── EventSubscriber/
│   └── EntityLifecycleSubscriber.php  # Champs d'audit (created_by / updated_by)
├── Form/Admin/                        # Formulaires backoffice
├── Repository/                        # 7 repositories Doctrine
├── Security/
│   └── LessonVoter.php                # Contrôle d'accès aux leçons
└── Service/
    └── MailerService.php              # Envoi des emails transactionnels
```

### 3.2 Modèle physique de données

Le MPD complet (diagramme PNG + PDF vectoriel) est disponible dans `docs/mpd.png` et `docs/mpd.pdf`. Il comprend 7 tables :

| Table | Rôle |
|---|---|
| `user` | Comptes utilisateurs (ROLE_USER / ROLE_ADMIN) |
| `theme` | Thèmes pédagogiques (Musique, Développement, Jardinage, Cuisine) |
| `cursus` | Formations rattachées à un thème |
| `lesson` | Leçons d'un cursus, ordonnées par `position` |
| `purchase` | Historique d'achat (cursus ou leçon) avec référence Stripe |
| `lesson_progress` | Suivi de complétion par utilisateur et par leçon |
| `certification` | Certifications obtenues (1 par thème, déclenchement automatique) |

Toutes les tables disposent des colonnes d'audit : `created_at`, `updated_at`, `created_by`, `updated_by`.

### 3.3 Relations principales

- `theme` **1 → N** `cursus`
- `cursus` **1 → N** `lesson`
- `user` **1 → N** `purchase` (cursus ou leçon, FK nullable)
- `user` **1 → N** `lesson_progress`
- `user` **1 → N** `certification` (unique par thème)

---

## 4. Fonctionnalités implémentées

### 4.1 Authentification et gestion des comptes

- **Inscription** avec validation côté serveur : email unique, mot de passe ≥ 8 caractères (lettre + chiffre), prénom/nom obligatoires.
- **Activation par email** : un token signé (64 caractères, valide 24 h) est envoyé à l'inscription via `MailerService`. La route `app_verify_email` active le compte et invalide le token.
- **Connexion / déconnexion** via le pare-feu Symfony (`form_login`).
- **Rôles** : `ROLE_USER` (client) et `ROLE_ADMIN` (gestionnaire).
- Seuls les comptes **vérifiés** peuvent procéder à un achat.

### 4.2 Catalogue

- Page d'accueil listant les thèmes avec hero section et appel à l'action.
- Page thème listant les cursus avec prix.
- Page cursus listant les leçons ordonnées (`position`).
- Page leçon : contenu textuel + vidéo — accessible uniquement aux utilisateurs ayant acheté la leçon ou son cursus parent (contrôle via `LessonVoter`).

### 4.3 E-commerce — intégration Stripe

- Achat d'un **cursus** entier ou d'une **leçon** à l'unité.
- Redirection vers Stripe Checkout (mode sandbox) avec `price_data` dynamique.
- Route de retour (`/paiement/succes/{type}/{id}`) : création de l'enregistrement `Purchase` avec le `stripe_payment_id` de la session.
- Vérification de double-achat avant la création de session (idempotence).

### 4.4 Progression et certification

- Le bouton **"Marquer comme terminée"** sur chaque leçon crée ou met à jour un `LessonProgress`.
- Après chaque validation, le système vérifie si toutes les leçons du thème sont complétées.
- Si oui, une `Certification` est automatiquement créée (contrainte `UNIQUE(user_id, theme_id)`).
- La page `/mes-certifications` affiche les certifications obtenues et la progression en pourcentage pour les thèmes en cours.

### 4.5 Backoffice administrateur

Accessible uniquement avec `ROLE_ADMIN` (`/admin`). Toutes les routes sont protégées par `#[IsGranted('ROLE_ADMIN')]`.

| Section | Actions disponibles |
|---|---|
| Tableau de bord | Compteurs utilisateurs / achats, 10 derniers inscrits |
| Utilisateurs | Liste, modification du rôle et des informations, suppression (token CSRF) |
| Thèmes | CRUD complet avec génération automatique du slug |
| Cursus | CRUD complet avec sélection du thème parent |
| Leçons | CRUD complet avec ordre, contenu, URL vidéo |
| Achats | Liste en lecture seule |

---

## 5. Sécurité

### 5.1 Contrôle d'accès

- **`LessonVoter`** : vote sur l'attribut `lesson_view`. Vérifie via `PurchaseRepository::userHasLesson()` si l'utilisateur a acheté la leçon directement ou via son cursus parent. Retourne `false` si l'utilisateur n'est pas authentifié.
- **`#[IsGranted]`** sur tous les controllers sensibles.
- **Vérification de compte** avant tout achat : un utilisateur non vérifié est redirigé avec un message d'erreur.

### 5.2 Protection des formulaires

- Tokens CSRF sur tous les formulaires de suppression (admin).
- Validation Symfony Validator côté serveur sur tous les formulaires.
- Hashage des mots de passe via `PasswordHasherInterface` (algorithme natif PHP argon2id).

### 5.3 Audit trail

Un `TimestampableTrait` est utilisé sur les 7 entités pour gérer automatiquement `created_at` et `updated_at` via les lifecycle callbacks Doctrine (`#[ORM\PrePersist]` / `#[ORM\PreUpdate]`).

Un `EntityLifecycleSubscriber` (`#[AsDoctrineListener]`) renseigne `created_by` et `updated_by` avec l'email de l'utilisateur courant, en utilisant `TokenStorageInterface` pour éviter la dépendance circulaire avec Doctrine.

---

## 6. Tests automatisés

La suite de tests couvre **50 tests pour 92 assertions**, tous au vert.

### 6.1 Tests de Repository (29 tests)

Tests d'intégration sur base de données réelle (`knowledge_learning_test`), rechargée par les fixtures avant chaque classe.

| Classe testée | Tests |
|---|---|
| `UserRepository` | findByActivationToken, findByEmail |
| `ThemeRepository` | findAll, findBySlug, relation cursus |
| `CursusRepository` | findAll (6 cursus), findBySlug, relation lessons |
| `LessonRepository` | findAll (12 leçons), findBySlug, contenu |
| `PurchaseRepository` | userHasCursus, userHasLesson, findByUser, montant |
| `LessonProgressRepository` | findOneByUserAndLesson, countCompletedForCursus |
| `CertificationRepository` | userHasCertification |

### 6.2 Tests Fonctionnels (21 tests)

Tests HTTP de bout en bout via `WebTestCase` (client intégré Symfony).

| Scénario | Tests |
|---|---|
| **Inscription** | Page accessible, inscription réussie, email existant (422), mot de passe trop court (422) |
| **Activation email** | Token valide active le compte, token invalide, token expiré, utilisateur non vérifié ne peut pas acheter |
| **Connexion** | Page accessible, connexion réussie, mauvais mot de passe, email inconnu, admin accède au backoffice, utilisateur bloqué, déconnexion |
| **Achat** | Non authentifié redirigé, non vérifié bloqué, achat cursus crée un Purchase, achat leçon crée un Purchase, accès leçon achetée, accès leçon non achetée bloqué |

### 6.3 Exécution

```bash
# Base de données de test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Lancer la suite complète
php bin/phpunit

# Résultat attendu
OK (50 tests, 92 assertions)
```

---

## 7. Documentation

### 7.1 PHPDoc

L'ensemble des classes PHP (entités, repositories, controllers, services, voters) est documenté en anglais avec des commentaires PHPDoc sur les classes et les méthodes (`@param`, `@return`, `@throws`).

La documentation HTML générée par **phpDocumentor v3.9.1** est disponible dans le dossier `docs/` (32 fichiers PHP analysés).

### 7.2 Modèle Physique de Données

Généré avec **Graphviz** à partir du source `docs/mpd.dot` :
- `docs/mpd.png` — raster 150 dpi (404 Ko)
- `docs/mpd.pdf` — vecteur PDF (52 Ko)

### 7.3 README

Le `README.md` à la racine du projet documente :
- Prérequis (PHP 8.5, Composer, MySQL 9.6, Node.js, Stripe CLI)
- Installation complète (clone → migration → fixtures)
- Comptes de test (admin et client)
- Lancement de la suite de tests
- Architecture globale

---

## 8. Workflow de développement

Le projet a suivi un workflow **GitHub Flow** structuré :

| PR | Branche | Contenu |
|---|---|---|
| #22 | feature/phase-1 | Setup Symfony, entités, migrations, fixtures |
| #23 | feature/phase-2 | Inscription, activation email, connexion |
| #25 | feature/phase-3 | Catalogue complet, LessonVoter |
| #26 | feature/phase-4 | Intégration Stripe (achat cursus + leçon) |
| #27 | feature/phase-5 | Progression des leçons + certification automatique |
| #28 | feature/phase-6 | Backoffice admin complet |
| #29 | feature/phase-7 | Suite PHPUnit — 50 tests, 92 assertions |
| #30 | feature/phase-8 | Logo, favicon, hero section |
| #35 | feature/issue-31 | Colonnes d'audit (TimestampableTrait + EventSubscriber) |
| #36 | feature/issue-32 | README complet |
| #37 | feature/issue-33 | PHPDoc en anglais + documentation générée |
| #38 | feature/issue-34 | MPD Graphviz PNG + PDF |
| #39 | develop → main | Release v1.0 |

Chaque branche a fait l'objet d'une pull request avec description, revue, et merge dans `develop`. La branche `main` ne reçoit que des releases.

---

## 9. Données de démonstration

Les fixtures (`AppFixtures.php`) chargent un jeu de données représentatif :

| Entité | Données |
|---|---|
| Utilisateurs | `admin@knowledge-learning.fr` (ROLE_ADMIN), `client@example.com` (ROLE_USER, vérifié) |
| Thèmes | Musique, Développement Web, Jardinage, Cuisine |
| Cursus | 6 cursus (2 par thème sauf Jardinage/Cuisine à 1 chacun) |
| Leçons | 12 leçons (2 par cursus), avec contenu et URL vidéo |
| Achat exemple | Le compte client possède le cursus Guitare débutant |

---

## 10. Points de conformité au cahier des charges

| Exigence | Statut |
|---|---|
| Catalogue thèmes / cursus / leçons | ✅ Implémenté |
| Inscription avec activation email | ✅ Implémenté |
| Connexion / déconnexion sécurisée | ✅ Implémenté |
| Achat cursus via Stripe | ✅ Implémenté |
| Achat leçon à l'unité via Stripe | ✅ Implémenté |
| Accès conditionnel aux leçons (voter) | ✅ Implémenté |
| Suivi de progression leçon par leçon | ✅ Implémenté |
| Certification automatique par thème | ✅ Implémenté |
| Backoffice admin complet (CRUD) | ✅ Implémenté |
| Tests automatisés | ✅ 50 tests — 92 assertions |
| Documentation PHPDoc | ✅ Générée (phpDocumentor 3.9.1) |
| Modèle physique de données | ✅ PNG + PDF Graphviz |
| Colonnes d'audit sur toutes les entités | ✅ TimestampableTrait + EventSubscriber |
| README d'installation | ✅ Complet |
| Gestion de versions Git / GitHub | ✅ 13 PRs, branches feature/* → develop → main |

---

## 11. Comptes de test

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | `admin@knowledge-learning.fr` | `Admin1234!` |
| Client | `client@example.com` | `Client1234!` |

Pour les paiements : utiliser la carte Stripe sandbox `4242 4242 4242 4242`, date future, CVC quelconque.

---

*Rapport généré le 11 mai 2026.*
