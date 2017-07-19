Projet de Gestion des FFO pour l'entreprise Cofidur-EMS
==============

### Installation et environnement de développement


Installer composer (voir la  [documentation officielle](https://getcomposer.org/download/))

```sh
$ git clone https://github.com/ezlanguage/website.git
$ cd website
$ composer install # Installation des dépendances
```

Voir si la configuration de l'environnement remplit les prérequis de Symfony :
```sh
$ php app/check.php
```

### Déploiement

Le déploiement est automatisé à l'aide du fichier deploy.rb et utilisera les paramètres du fichier parameters_prod.yml (et non parameters.yml qui corresponds à la configuration de dev).

Installer Capistrano :
```sh
$ gem install capistrano      #-v 2.15.9 ou précisez vous-même la version souhaitée
```
Ensuite se placer à la racine du projet.

Modifier le fichier de configuration du déploiement (deploy.rb), et modifier l'utilisateur et le serveur cible.
```sh
$ nano app/config/deploy.rb
```

Modifier le fichier de configuration de la base de données de production (parameters_prod.yml).
Attention à ne pas commit ce fichier s'il contient le mot de passe de la base de données ou du mailer.
```sh
$ nano app/config/parameters_prod.yml
```

Premier déploiement (structure et fichier de configuration)
Il faudra refaire cette commande si des fichiers partagés sont ajoutés ou si la configuration (Base de données ou serveur de mail) change.
```sh
$ cap deploy:setup
```

Déploiement normal (après la 1ère fois)
```sh
$ cap deploy
```


A Symfony project created on April 27, 2017, 10:47 am.

