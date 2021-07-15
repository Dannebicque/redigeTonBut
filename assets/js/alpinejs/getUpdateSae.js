function getUpdateSae() {
  return {
    acs: [],
    listeCompetences: [],
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
    getAcs (id) {
      if (this.acs !== false && id in this.acs) {
        return this.acs[id]
      }
      return {}
    },
    getLibelleCompetence (id) {
      if (this.acs !== false && this.acs.competences !== false && !(typeof this.acs.competences === 'undefined') && id in this.acs.competences) {
        return '# Compétence : ' + this.acs.competences[id]
      }
      return '-chargement-'
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
    async getApiCompetences() {
      this.listeCompetences = await fetch(Routing.generate('competence_apc_competences_sae_semestre_ajax'), {
        method: 'POST',
        body: JSON.stringify({
          semestre: this.semestre, //dépendre du parcours?
          sae: sae
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
      e.stopPropagation()
      await this.updateSemestre()
    },
    async changeCompetence (e) {
      e.stopPropagation()
      await this.getApiAcs()
    }
  }
}

window.getUpdateSae = getUpdateSae;
