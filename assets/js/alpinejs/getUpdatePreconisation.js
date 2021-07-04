function getUpdatePreconisation () {
  return {
    donnees: {
      1: {competences: {}, ressources: {}, saes: {}, semestre: {}},
      2: {competences: {}, ressources: {}, saes: {}, semestre: {}},
      3: {competences: {}, ressources: {}, saes: {}, semestre: {}},
      4: {competences: {}, ressources: {}, saes: {}, semestre: {}},
      5: {competences: {}, ressources: {}, saes: {}, semestre: {}},
      6: {competences: {}, ressources: {}, saes: {}, semestre: {}}
    },
    async init () {
      this.donnees = await fetch(Routing.generate('tableau_api_preconisation')).then(r => {
        return r.json()
      })
    },
    updateSae (e) {
      fetch(Routing.generate('formation_apc_sae_coeff_update_ajax', {
        sae: e.target.dataset.sae, competence: e
          .target.dataset.competence
      }), {
        method: 'POST',
        body: JSON.stringify({
          valeur: e.target.value
        })
      }).then(async r => {
        this.donnees = await fetch(Routing.generate('tableau_api_preconisation')).then(r => {
          return r.json()
        })
      })

    },
    updateRessource (e) {
      fetch(Routing.generate('formation_apc_ressource_coeff_update_ajax', {
        ressource: e.target.dataset.ressource,
        competence: e.target.dataset.competence
      }), {
        method: 'POST',
        body: JSON.stringify({
          valeur: e.target.value
        })
      }).then(async r => {
        this.donnees = await fetch(Routing.generate('tableau_api_preconisation')).then(r => {
          return r.json()
        })
      })

    },
    updateCompetence (e) {
      fetch(Routing.generate('administration_apc_competence_update_ects', {
        semestre: e.target.dataset.semestre,
        competence: e.target.dataset.competence
      }), {
        method: 'POST',
        body: JSON.stringify({
          valeur: e.target.value
        })
      }).then(async r => {
        this.donnees = await fetch(Routing.generate('tableau_api_preconisation')).then(r => {
          return r.json()
        })
      })

    },
    updateHeuresSae (e) {
      fetch(Routing.generate('formation_apc_sae_heure_update_ajax', {
        sae: e.target.dataset.sae,
        type: e.target.dataset.type
      }), {
        method: 'POST',
        body: JSON.stringify({
          valeur: e.target.value
        })
      }).then(async r => {
        this.donnees = await fetch(Routing.generate('tableau_api_preconisation')).then(r => {
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
      }).then(async r => {
        this.donnees = await fetch(Routing.generate('tableau_api_preconisation')).then(r => {
          return r.json()
        })
      })

    },
    afficheValeurCoefficient (tableau, id, competence) {
      if (id in tableau) {
        if (competence in tableau[id]) {
          return tableau[id][competence].coefficient
        }
      }
      return ''
    },
    afficheVolumeHoraire (tableau, id, libelle) {
      if (id in tableau) {
        if (libelle in tableau[id]) {
          return tableau[id][libelle]
        }
      }
      return ''
    },
    afficheVolumeHoraireTotal (tableau, libelle) {
      if (libelle in tableau) {
        return tableau[libelle]
      }
      return ''
    },
    afficheTotalEcts (tableau, libelle) {
      if (libelle in tableau) {
        return tableau[libelle]
      }
      return ''
    },
    afficheValeurTotal (tableau, id, cle = 'total') {
      if (id in tableau) {

        return tableau[id][cle]

      }
      return ''
    },
    numberFormat (valeur) {
      if (typeof valeur !== 'undefined') {
        return valeur.toFixed(2)
      }
    },
    badgeEgalite (valeur, seuil) {
      if (valeur == seuil) {
        return '<span class="badge bg-success text-uppercase">' + this.numberFormat(valeur) + '</span>'
      }

      return '<span class="badge bg-danger text-uppercase">' + this.numberFormat(valeur) + '</span>'
    }
  }
}

window.getUpdatePreconisation = getUpdatePreconisation
