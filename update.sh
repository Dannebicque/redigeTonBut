# Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
# @file /Users/davidannebicque/htdocs/intranetV3/update.sh
# @author davidannebicque
# @project intranetV3
# @lastUpdate 26/05/2021 21:23

echo "Début mise à jour"
echo "Git Pull"
git pull origin main
echo "end git pull"
echo "Nettoyage cache"
rm -R var/cache/prod
mkdir var/cache/prod
#chown intradev:www-data -R var/cache/prod
chmod -R 777 var/cache/prod
bin/console cache:clear
echo "end Nettoyage cache"
echo "Mise à jour les liens js"
bin/console fos:js-routing:dump --format=json --target=public/js/fos_js_routes.json
echo "end Mise à jour les liens js"
echo "Optimisation Composer"
composer dump-autoload --no-dev --classmap-authoritative
echo "end Optimisation Composer"
chmod -R 777 var/
echo "Fin mise à jour"

