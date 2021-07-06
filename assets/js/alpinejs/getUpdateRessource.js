function getUpdateRessource() {
  return {
    acs: [],
    competences: [],
    saes: [],
    parcours: [],
    prerequis: [],
    semestre: null,
   async  init () {
      this.semestre = this.displayRadioValue()
      if (this.semestre !== null) {
       await  this.updateSemestre()
      }
    },
    displayRadioValue () {
      //init le select du semestre
      const ele = document.getElementsByName('apc_ressource[semestre]')

      for (let i = 0; i < ele.length; i++) {
        if (ele[i].checked)
          return ele[i].value
      }
      return null
    },
    getCompetences () {
      //init le select du semestre
      this.competences = []
      const ele = document.getElementsByName('apc_ressource[competences][]')
      for (let i = 0; i < ele.length; i++) {
        if (ele[i].checked)
          this.competences.push({
            id: ele[i].value,
            libelle: 'Compétence ' + ele[i].value
          })
      }
    },
    getAcs (id) {
      if (this.acs !== false && id in this.acs) {
        return this.acs[id]
      }
      return {}
    },
    async getApiAcs() {
      this.acs = await fetch(Routing.generate('formation_apc_ressources_ajax_ac'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          competences: this.competences,
          ressource: ressource
        })
      }).then(r => {
        return r.json()
      })
    },
    async updateSemestre () {
      this.getCompetences()
      await this.getApiAcs()

      this.saes = await fetch(Routing.generate('formation_apc_sae_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          ressource: ressource
        })
      }).then(r => {
        return r.json()
      })

      this.prerequis = await fetch(Routing.generate('formation_apc_prerequis_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          ressource: ressource
        })
      }).then(r => {
        return r.json()
      })

      this.parcours = await fetch(Routing.generate('formation_apc_ressouce_parcours_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          ressource: ressource
        })
      }).then(r => {
        return r.json()
      })
    },
    async changeSemestre (e) {
      //this.loaded = false
      e.stopPropagation()
      await this.updateSemestre()
    },
    async changeCompetence (e) {
      e.stopPropagation()
      this.getCompetences()
      await this.getApiAcs()
    }
  }
}

window.getUpdateRessource = getUpdateRessource;
