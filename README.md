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

- Php.init
```bash
extension=sodium
extension=openssl
```
- Openssl doit être accèssible en global
```bash
openssl version
```
---

## Installation et Lancement

- Accéder au répertoire du projet
```bash
cd .\ecogarden\
```
- Installer les dépendances avec Composer
```bash
composer install

```
- Configurer les variables d'environnement
```bash
# In all environments, the following files are loaded if they exist,
# the latter taking precedence over the former:
#
#  * .env                contains default values for the environment variables needed by the app
#  * .env.local          uncommitted file with local overrides
#  * .env.$APP_ENV       committed environment-specific defaults
#  * .env.$APP_ENV.local uncommitted environment-specific overrides
#
# Real environment variables win over .env files.
#
# DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
# https://symfony.com/doc/current/configuration/secrets.html
#
# Run "composer dump-env prod" to compile .env files for production use (requires symfony/flex >=1.2).
# https://symfony.com/doc/current/best_practices.html#use-environment-variables-for-infrastructure-configuration

###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=secret_ici
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
#
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
DATABASE_URL="mysql://username:password@127.0.0.1:3306/ecogarden?serverVersion=10.4.32-mariadb&charset=utf8mb4"
charset=utf8"
###< doctrine/doctrine-bundle ###

###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE="votre_mot_de_passe"
OPENWEATHER_API_KEY="votre_api_key"

```

-  Créer et mettre à jour la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

```

-  Créer la configuration SSL pour le JWT
```bash
php bin/console lexik:jwt:generate-keypai
```

-  Lancer le serveur Symfony
```bash
symfony serve -d

```