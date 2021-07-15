function getUpdateVolumesHoraires (parcours = null) {
  return {
    parcours: parcours,
    donnees: {
      1: {'ressources': {}},
      2: {'ressources': {}},
      3: {'ressources': {}},
      4: {'ressources': {}},
      5: {'ressources': {}},
      6: {'ressources': {}}
    },
    async init () {
      this.donnees = await fetch(Routing.generate('tableau_api_volumes_horaires', {parcours: this.parcours})).then(r => {
        return r.json()
      })
    },
    updateSemestre (e) {
      fetch(Routing.generate('semestre_heure_update_ajax', {
        semestre: e.target.dataset.semestre,
        type: e.target.dataset.field
      }), {
        method: 'POST',
        body: JSON.stringify({
          valeur: e.target.value
        })
      }).then(async () => {
        this.donnees = await fetch(Routing.generate('tableau_api_volumes_horaires', {parcours: this.parcours})).then(r => {
          return r.json()
        })
      })
    },
    updateHeuresRessource (e) {
       fetch(Routing.generate('formation_apc_ressource_heure_update_ajax', {
        ressource: e.target.dataset.ressource,
        type: e.target.dataset.type
      }), {
        method: 'POST',
        body: JSON.stringify({
          valeur: e.target.value
        })
      }).then(async () => {
        this.donnees = await fetch(Routing.generate('tableau_api_volumes_horaires', {parcours: this.parcours})).then(r => {
          return r.json()
        })
      })

    },
    getVolumeTotalRessource (semestre, ressource) {
      if ('ressources' in this.donnees[semestre] && ressource in this.donnees[semestre].ressources) {
        return this.donnees[semestre].ressources[ressource].totalEnseignement;
      }
      return '-erreur-'
    },
    getVolumeDontTpRessource (semestre, ressource) {
      if ('ressources' in this.donnees[semestre] && ressource in this.donnees[semestre].ressources) {
        return this.donnees[semestre].ressources[ressource].dontTp;
      }
      return '-erreur-'
    },
    numberFormat (valeur) {
      if (typeof valeur !== 'undefined') {
        return valeur.toFixed(2)
      }
    },
    badgeEgalite (valeur, seuil) {
      if (valeur === seuil) {
        return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }

      return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
    }
  }
}

window.getUpdateVolumesHoraires = getUpdateVolumesHoraires
