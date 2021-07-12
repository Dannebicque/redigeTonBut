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
    numberFormat (valeur) {
      if (typeof valeur !== 'undefined') {
        return valeur.toFixed(2)
      }
    },
    badgeSeuil (valeur, seuil) {
      if (valeur > seuil) {
        return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }

      return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
    },
    badgeSeuilInferieur (valeur, seuil) {
      if (valeur < seuil) {
        return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }

      return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
    }
  }
}


window.getUpdateStructure = getUpdateStructure
