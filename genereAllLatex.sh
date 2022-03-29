#echo "Mise à jour de la codification"
#bin/console app:update-codification-ref

echo "Mise  à jour des fichier Latex"
bin/console app:genere-latex GEA
bin/console app:genere-latex GEII
bin/console app:genere-latex GMP
bin/console app:genere-latex TC
bin/console app:genere-latex CJ
bin/console app:genere-latex CS
bin/console app:genere-latex Info-Com
bin/console app:genere-latex Chimie
bin/console app:genere-latex GB
bin/console app:genere-latex GCGP
bin/console app:genere-latex GCCD
bin/console app:genere-latex INFO
bin/console app:genere-latex MP
bin/console app:genere-latex MT2E
bin/console app:genere-latex MMI
bin/console app:genere-latex GACO
bin/console app:genere-latex PEC
bin/console app:genere-latex QLIO
bin/console app:genere-latex MLT
bin/console app:genere-latex STID
bin/console app:genere-latex GIM
bin/console app:genere-latex HSE
bin/console app:genere-latex RT
bin/console app:genere-latex SGM
echo "Fin  de la mise  à jour des fichier Latex"
