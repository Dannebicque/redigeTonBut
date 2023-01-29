import { Controller } from 'stimulus'

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
  static values = {
    urlApi: String,
  }

  connect() {
    console.log('hello from qapes_controller.js')
  }

  changeParcours(event) {
    const parcours = event.target.value
    this._updateSaeFromParcours(parcours)
  }

  changeSpecialite(event) {
    const specialite = event.target.value
    this._updateSaeFromSpecialite(specialite)
  }

  changeIut(event) {
    this._updateSiteIut(event.target.value)
  }

  changeSiteIut(event) {
    const siteIut = event.target.value
    this._updateParcours(siteIut)
    this._updateSpecialite(siteIut)
  }

  async _updateSiteIut(iut) {
    document.getElementById('qapes_sae_part1_iutSite').disabled = false
    await fetch(this.urlApiValue + '?action=siteIut&iut=' + iut).then(response => response.json()).then(
      data => {
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_iutSite')
        selectSites.innerHTML = ''

        let option = document.createElement('option')
        option.value = ''
        option.text = 'Choisir le site de l\'IUT'
        selectSites.appendChild(option)

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      },
    )
  }

  async _updateSpecialite(siteIut) {
    document.getElementById('qapes_sae_part1_specialite').disabled = false
    await fetch(this.urlApiValue + '?action=specialite&siteIut=' + siteIut).then(response => response.json()).then(
      async data => {
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_specialite')
        selectSites.innerHTML = ''

        let option = document.createElement('option')
        option.text = 'Choisir une spécialité (tronc commun) ou un parcours'
        option.value = ''
        selectSites.appendChild(option)

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      },
    )

  }

  async _updateParcours(siteIut) {
    document.getElementById('qapes_sae_part1_parcours').disabled = false
    await fetch(this.urlApiValue + '?action=parcours&siteIut=' + siteIut).then(response => response.json()).then(
      data => {
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_parcours')
        selectSites.innerHTML = ''

        let option = document.createElement('option')
        option.text = 'Choisir une spécialité (tronc commun) ou un parcours'
        option.value = ''
        selectSites.appendChild(option)

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      },
    )
  }

  async _updateSaeFromSpecialite(specialite) {
    document.getElementById('qapes_sae_part1_sae').disabled = false
    await fetch(this.urlApiValue + '?action=saeFromSpecialite&specialite=' + specialite).then(response => response.json()).then(
      data => {
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_sae')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      },
    )
  }

  async _updateSaeFromParcours(parcours) {
    document.getElementById('qapes_sae_part1_sae').disabled = false
    await fetch(this.urlApiValue + '?action=saeFromParcours&parcours=' + parcours).then(response => response.json()).then(
      data => {
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_sae')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      },
    )
  }
}
