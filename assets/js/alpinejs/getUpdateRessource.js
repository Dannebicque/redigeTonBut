function getUpdateRessource () {
  return {
    acs: [],
    listeCompetences: [],
    competences: [],
    saes: [],
    parcours: [],
    prerequis: [],
    semestre: null,
    async init () {
      this.semestre = this.displayRadioValue()
      if (this.semestre !== null) {
        await this.updateSemestre()
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
    getAcs (id) {
      if (this.acs !== false && id in this.acs) {
        return this.acs[id]
      }
      return {}
    },
    async getApiAcs () {
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
    async getApiCompetences () {
      this.listeCompetences = await fetch(Routing.generate('competence_apc_competences_ressource_semestre_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre, //dépendre du parcours?
          ressource: ressource
        })
      }).then(r => {
        return r.json()
      })

      this.listeCompetences.forEach((comp) => {
        if (comp.checked === true) {
          this.competences.push(comp.id)
        }
      })
    },
    async updateSemestre () {

      await this.getApiCompetences().then(async () => {
        //this.getCompetences()
        await this.getApiAcs()
      })

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
      e.stopPropagation()
      await this.updateSemestre()
    },
    async changeCompetence (e) {
      e.stopPropagation()
      await this.getApiAcs()
    },
    selectAll (e) {
      e.preventDefault()
      this.competences = [] //vider le tableau
      document.querySelectorAll('.competence').forEach((elem) => {
        elem.checked = true
        this.competences.push(elem.value)
      })
      this.changeCompetence(e).then(() => {
        document.querySelectorAll('.ac').forEach((elem) => {
          elem.checked = true
        })
      })
    },
    unselectAll (e) {
      e.preventDefault()
      document.querySelectorAll('.ac').forEach((elem) => {
        elem.checked = false
      })
      document.querySelectorAll('.competence').forEach((elem) => {
        elem.checked = false
      })
    }
  }
}

window.getUpdateRessource = getUpdateRessource
