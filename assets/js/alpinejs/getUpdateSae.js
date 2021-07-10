function getUpdateSae() {
  return {
    acs: [],
    competences: [],
    ressources: [],
    parcours: [],
    semestre: null,
    async init () {
      this.semestre = this.displayRadioValue()
      if (this.semestre !== null) {
        await this.updateSemestre()
      }
    },
    displayRadioValue () {
      //init le select du semestre
      const ele = document.getElementsByName('apc_sae[semestre]')

      for (let i = 0; i < ele.length; i++) {
        if (ele[i].checked)
          return ele[i].value
      }
      return null
    },
    getCompetences () {
      //init le select du semestre
      this.competences = []
      const ele = document.getElementsByName('apc_sae[competences][]')
      for (let i = 0; i < ele.length; i++) {
        if (ele[i].checked)
          this.competences.push({
            id: ele[i].value
          })
      }
    },
    getAcs (id) {
      if (this.acs !== false && id in this.acs) {
        return this.acs[id]
      }
      return {}
    },
    getLibelleCompetence (id) {
      if (this.acs !== false && id in this.acs.competences) {
        return '# Coméptence : ' + this.acs.competences[id]
      }
      return '-erreur-'
    },
    async getApiAcs() {
      this.acs = await fetch(Routing.generate('formation_apc_sae_ajax_ac'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          competences: this.competences,
          sae: sae
        })
      }).then(r => {
        return r.json()
      })
    },
    async updateSemestre () {
      this.getCompetences()
      await this.getApiAcs()

      this.ressources = await fetch(Routing.generate('formation_apc_ressources_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          sae: sae
        })
      }).then(r => {
        return r.json()
      })

      this.parcours = await fetch(Routing.generate('formation_apc_sae_parcours_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre,
          sae: sae
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

window.getUpdateSae = getUpdateSae;
