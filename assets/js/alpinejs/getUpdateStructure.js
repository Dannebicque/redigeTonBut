function getUpdateStructure () {
  return {
    pourcent: 0,
    donnees: {
      1: {},
      2: {},
      3: {},
      4: {},
      5: {},
      6: {},
      departement: {}
    },
    update (e) {
      fetch(Routing.generate('tableau_api_structure_update'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: e.target.dataset.semestre,
          champ: e.target.dataset.field,
          valeur: e.target.value
        })
      }).then(async () => {
        this.donnees = await fetch(Routing.generate('tableau_api_structure')).then(r => {
          return r.json()
        })
      })

    },
    totalAnneeNbHeuresProjet(semestre) {
      return this.numberFormat( Number(this.donnees[semestre].nbHeuresProjet) + Number(this.donnees[semestre + 1].nbHeuresProjet))
    },
    totalAnneeNbHeuresRessourcesSae(semestre) {
      return this.numberFormat(Number(this.donnees[semestre].nbHeuresRessourcesSae) + Number(this.donnees[semestre + 1].nbHeuresRessourcesSae))
    },
    totalAnneeNbHeuresCoursProjet(semestre) {
      return this.numberFormat(Number(this.donnees[semestre].nbHeuresCoursProjet) + Number(this.donnees[semestre + 1].nbHeuresCoursProjet))
    },
    numberFormat (valeur) {
      if (typeof valeur !== 'undefined') {
        return Number(valeur).toFixed(2)
      }
    },
    badgeSeuil (valeur, seuil) {
      if (valeur > seuil) {
        return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }
      return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
    },
    badgeEgalite (valeur, seuil) {
      if (Number(valeur) !== Number(seuil)) {
        return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }
      return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
    },
    badgeSeuilInferieur (valeur, seuil) {
      if (Number(valeur) < Number(seuil)) {
        return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }

      return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
    }
  }
}


window.getUpdateStructure = getUpdateStructure
