#echo "Mise à jour de la codification"
#bin/console app:update-codification-ref

echo "Mise  à jour des tableaux"
bin/console app:genere-tableaux GEA
bin/console app:genere-tableaux GEII
bin/console app:genere-tableaux GMP
bin/console app:genere-tableaux TC
bin/console app:genere-tableaux CJ
bin/console app:genere-tableaux CS
bin/console app:genere-tableaux Info-Com
bin/console app:genere-tableaux Chimie
bin/console app:genere-tableaux GB
bin/console app:genere-tableaux GCGP
bin/console app:genere-tableaux GCCD
bin/console app:genere-tableaux INFO
bin/console app:genere-tableaux MP
bin/console app:genere-tableaux MT2E
bin/console app:genere-tableaux MMI
bin/console app:genere-tableaux GACO
bin/console app:genere-tableaux PEC
bin/console app:genere-tableaux QLIO
bin/console app:genere-tableaux MLT
bin/console app:genere-tableaux STID
bin/console app:genere-tableaux GIM
bin/console app:genere-tableaux HSE
bin/console app:genere-tableaux RT
bin/console app:genere-tableaux SGM
echo "Fin  de la mise  à jour des tableaux"
