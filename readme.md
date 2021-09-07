# ORéBUT

Un outil pour accompagner les ACD dans la rédaction des programmes nationaux de B.U.T.

## Badges

[![License: MPL 2.0](https://img.shields.io/badge/License-MPL%202.0-brightgreen.svg)](https://opensource.org/licenses/MPL-2.0)

[![wakatime](https://wakatime.com/badge/github/Dannebicque/redigeTonBut.svg)](https://wakatime.com/badge/github/Dannebicque/redigeTonBut)

## Documentation

Comming soon...


## Features

- Gestion des tableaux avec vérifications et recalcul en temps réel
- Gestion des fiches SAÉ et Ressources
- Toutes les données sont évidemments liées pour éviter les erreurs de mise à jour ou de recopie.
- Différents niveaux d'accès
 - GT : Toutes les spécialités
 - PACD / CPN : Tous les accès sur sa spécialité
 - Auteurs : Gestion des fiches SAÉ/Ressources
 - Lecteur : Consultation uniquement


## Contributing

Contributions are always welcome!


## Installation

```bash 
git clone https://github.com/Dannebicque/redigeTonBut.git
cd redigeTonBut/
cp .env .env.local
# Mettre à jour les données de la base de données, le serveur SMTP dans .env.local
composer install # les dépendances de Symfony
npm install # les dépendances JS/CSS
npm run build # compilation des assets

# Création de la BDD, création de la structure, chargement des données fictives. Inutile ici si le dump est importé.
bin/console d:d:c
bin/console d:s:u -f
bin/console d:f:l
```

## License

[Mozilla Public License 2.0](https://choosealicense.com/licenses/mpl-2.0/)


## Roadmap

- Gestion des parcours
- Export des documents


## Authors

- [@dannebicque](https://www.github.com/dannebicque)
- [@davidannebicque](https://www.twitter.com/davidannebicque)

