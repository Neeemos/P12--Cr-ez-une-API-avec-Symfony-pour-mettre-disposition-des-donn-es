# API de Gestion des Utilisateurs, Conseils et Météo

Ce projet implémente une API permettant de gérer les utilisateurs, les conseils saisonniers et les données météorologiques, avec un système de rôles utilisateur.

---

## Entités et Relations

### **Utilisateur (User)**

Représente les utilisateurs de l'application.

| Champ      | Type       | Description                                            |
|------------|------------|--------------------------------------------------------|
| `id`       | UUID       | Identifiant unique.                                    |
| `login`    | string     | Nom d'utilisateur unique.                              |
| `password` | string     | Mot de passe sécurisé (hashé).                         |
| `city`     | string     | Nom ou code postal de la ville associée à l'utilisateur. |
| `role`     | enum       | Rôle de l'utilisateur (`user` ou `admin`).             |

**Relations** :
- Chaque utilisateur est lié à une ville via le champ `city` pour des fonctionnalités liées à la météo.

---

### **Conseil (Advice)**

Contient les conseils saisonniers.

| Champ    | Type               | Description                                        |
|----------|--------------------|----------------------------------------------------|
| `id`     | UUID               | Identifiant unique.                                |
| `text`   | string             | Contenu du conseil.                                |
| `months` | array of integers  | Liste des mois où le conseil s'applique (ex. `[1, 2, 3]`). |

**Relations** :
- Les administrateurs peuvent ajouter, modifier ou supprimer des conseils.
- Les utilisateurs peuvent consulter les conseils.

---

### **Météo (Weather)**

Cache pour les données météorologiques.

| Champ          | Type     | Description                                       |
|-----------------|----------|---------------------------------------------------|
| `id`           | UUID     | Identifiant unique.                               |
| `city`         | string   | Nom de la ville.                                  |
| `temperature`  | float    | Température actuelle.                             |
| `conditions`   | string   | Conditions météo (ex. "ensoleillé").              |
| `last_updated` | timestamp| Date et heure de la dernière mise à jour.         |

**Relations** :
- La météo est reliée à une ville et peut être utilisée directement ou via l'utilisateur.

---

## Routes

| **Route**           | **Méthode** | **Entité Concernée** | **Action ou Relation**                                |
|----------------------|-------------|-----------------------|------------------------------------------------------|
| `/user`             | POST        | User                 | Crée un nouvel utilisateur.                         |
| `/auth`             | POST        | User                 | Authentifie l'utilisateur et génère un token JWT.   |
| `/conseil/{mois}`   | GET         | Advice               | Récupère les conseils pour un mois donné.           |
| `/conseil/`         | GET         | Advice               | Récupère les conseils pour le mois en cours.        |
| `/meteo/{ville}`    | GET         | Weather              | Récupère la météo d'une ville.                      |
| `/meteo`            | GET         | Weather, User        | Récupère la météo pour la ville de l'utilisateur.   |
| `/conseil`          | POST        | Advice               | Ajoute un conseil (admin).                         |
| `/conseil/{id}`     | PUT         | Advice               | Met à jour un conseil existant (admin).            |
| `/conseil/{id}`     | DELETE      | Advice               | Supprime un conseil existant (admin).              |
| `/user/{id}`        | PUT         | User                 | Met à jour un utilisateur (admin).                 |
| `/user/{id}`        | DELETE      | User                 | Supprime un utilisateur (admin).                   |

---

## Gestion des Rôles et Relations

- **Utilisateur -> Ville** : Les utilisateurs sont associés à une ville via `city`, permettant une récupération automatisée de la météo.
- **Utilisateur -> Rôle** : Les rôles (`user` ou `admin`) déterminent l'accès aux fonctionnalités et routes.
- **Mois -> Conseil** : Les conseils sont spécifiques à certains mois, permettant un filtrage adapté.
- **Ville -> Météo** : Les données météorologiques sont liées à une ville, avec un système de cache pour éviter les appels fréquents à l'API externe.

---

## Prérequis

- Une base de données relationnelle pour stocker les entités `User`, `Advice`, et `Weather`.
- Un système d'authentification basé sur JWT.
- Une API météo externe pour les données actualisées (si le cache est obsolète).

---

## Installation et Lancement

- Php.init
```bash
extension=sodium
```