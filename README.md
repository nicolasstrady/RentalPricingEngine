# Rental Pricing Engine

Back-end Symfony d'une future API de location de matériel. L'application calculera le prix minimal d'une location selon le modèle tarifaire de l'équipement.

## État du projet

Le projet contient actuellement le socle Symfony. L'environnement Docker, le modèle de données, le moteur de tarification et l'API seront ajoutés progressivement.

## Prérequis temporaires

- PHP 8.2 ou supérieur
- Composer 2

Ces prérequis locaux seront remplacés par un environnement Docker reproductible lors de la prochaine étape.

## Installation

Les fichiers `.env*` sont volontairement locaux et ne sont jamais versionnés, à l'exception de `.env.example`. Avant la première installation, copier ce modèle :

```bash
cp .env.example .env
```

Renseigner ensuite les valeurs locales dans `.env`. La variable `APP_SECRET` doit recevoir une valeur aléatoire propre à l'environnement et ne doit jamais être réutilisée en production.

Installer ensuite les dépendances :

```bash
composer install
```

## Lancement temporaire

```bash
php -S localhost:8000 -t public public/index.php
```

L'application est alors accessible à l'adresse <http://localhost:8000>.

## Console Symfony

```bash
php bin/console about
```

## Documentation à venir

Le README sera complété avec :

- les commandes Docker ;
- la création et la migration de la base de données ;
- le chargement des fixtures ;
- les commandes de test et de qualité ;
- les hypothèses métier ;
- l'architecture du moteur de tarification ;
- un exemple d'appel de l'API.
