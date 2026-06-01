# Rapport de tests unitaires — Knowledge Learning

**Projet :** Knowledge Learning — Plateforme e-learning / e-commerce
**Auteur :** Elea De Sousa
**Framework de tests :** PHPUnit 13.1.8
**Date :** Juin 2026
**Résultat global :** 50 tests — 92 assertions — 0 échec

---

## Tests de Repository (29 tests)

Tests d'intégration sur base de données MySQL réelle (`knowledge_learning_test`), rechargée via les fixtures avant chaque suite.

### CertificationRepository (4 tests)

- ✅ Find by user returns empty when no certification
- ✅ Find by user returns certifications
- ✅ User has certification returns false when none
- ✅ User has certification returns true when exists

### CursusRepository (4 tests)

- ✅ Find all returns six cursus
- ✅ Find by slug returns cursus
- ✅ Find by slug returns null for unknown slug
- ✅ Cursus has lessons

### LessonProgressRepository (4 tests)

- ✅ Find one by user and lesson returns null when not started
- ✅ Find one by user and lesson returns progress when exists
- ✅ Count completed for cursus returns zero when none completed
- ✅ Count completed for cursus counts correctly

### LessonRepository (4 tests)

- ✅ Find all returns twelve lessons
- ✅ Find by slug returns lesson
- ✅ Find by slug returns null for unknown slug
- ✅ Lesson has content

### PurchaseRepository (6 tests)

- ✅ User has cursus returns true for purchased
- ✅ User has cursus returns false for unpurchased
- ✅ User has lesson returns true when cursus purchased
- ✅ User has lesson returns false when not purchased
- ✅ Find by user returns user purchases
- ✅ Purchase has correct amount

### ThemeRepository (4 tests)

- ✅ Find all returns themes
- ✅ Find by slug returns theme
- ✅ Find by slug returns null for unknown slug
- ✅ Theme has cursus

### UserRepository (3 tests)

- ✅ Find by activation token returns user
- ✅ Find by activation token returns null for unknown token
- ✅ Find by email returns correct user

---

## Tests Fonctionnels (21 tests)

Tests HTTP de bout en bout via `WebTestCase` (client HTTP intégré Symfony).

### Registration (4 tests)

- ✅ Registration page is accessible
- ✅ Successful registration
- ✅ Registration with existing email fails
- ✅ Registration with short password fails

### Email Activation (4 tests)

- ✅ Valid token activates account
- ✅ Invalid token shows error
- ✅ Expired token shows error
- ✅ Unverified user cannot purchase

### Login (7 tests)

- ✅ Login page is accessible
- ✅ Successful login
- ✅ Login with wrong password fails
- ✅ Login with unknown email fails
- ✅ Admin can access backoffice
- ✅ User cannot access admin
- ✅ Logout

### Purchase (6 tests)

- ✅ Unauthenticated user is redirected to login
- ✅ Unverified user cannot purchase
- ✅ Purchase success creates purchase record
- ✅ Purchase lesson success creates purchase record
- ✅ Access to purchased lesson
- ✅ Access to unpurchased lesson is blocked

---

## Résultat

```
OK (50 tests, 92 assertions)
```

| Catégorie | Tests | Assertions |
|-----------|-------|------------|
| Repository | 29 | 58 |
| Fonctionnels | 21 | 34 |
| **Total** | **50** | **92** |
