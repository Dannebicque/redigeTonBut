function getUpdateSae() {
  return {
    acs: [],
    competences: [],
    ressources: [],
    semestre: null,
    init () {
      this.semestre = this.displayRadioValue()
      if (this.semestre !== null) {
        this.updateSemestre()
      }
    },
    displayRadioValue () {
      //init le select du semestre
      var ele = document.getElementsByName('apc_sae[semestre]')

      for (i = 0; i < ele.length; i++) {
        if (ele[i].checked)
          return ele[i].value
      }
      return null
    },
    getCompetences () {
      //init le select du semestre
      this.competences = []
      var ele = document.getElementsByName('apc_sae[competences][]')
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
    async updateSemestre () {
      this.getCompetences()
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

      this.ressources = await fetch(Routing.generate('formation_apc_ressources_ajax'), {
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
      await this.updateSemestre()
    }
  }
}

window.getUpdateSae = getUpdateSae;
