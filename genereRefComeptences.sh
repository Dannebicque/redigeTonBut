#echo "Mise à jour de la codification"
#bin/console app:update-codification-ref

echo "Mise  à jour des référentiels de compétences"
bin/console app:genere-ref-competence GEA
bin/console app:genere-ref-competence GEII
bin/console app:genere-ref-competence GMP
bin/console app:genere-ref-competence TC
bin/console app:genere-ref-competence CJ
bin/console app:genere-ref-competence CS
bin/console app:genere-ref-competence Info-Com
bin/console app:genere-ref-competence Chimie
bin/console app:genere-ref-competence GB
bin/console app:genere-ref-competence GCGP
bin/console app:genere-ref-competence GCCD
bin/console app:genere-ref-competence INFO
bin/console app:genere-ref-competence MP
bin/console app:genere-ref-competence MT2E
bin/console app:genere-ref-competence MMI
bin/console app:genere-ref-competence GACO
bin/console app:genere-ref-competence PEC
bin/console app:genere-ref-competence QLIO
bin/console app:genere-ref-competence MLT
bin/console app:genere-ref-competence STID
bin/console app:genere-ref-competence GIM
bin/console app:genere-ref-competence HSE
bin/console app:genere-ref-competence RT
bin/console app:genere-ref-competence SGM
echo "Fin  de la mise  à jour des référentiels de compétences"
