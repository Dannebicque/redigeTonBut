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

  changeIut(event) {
    this._updateSiteIut(event.target.value)
  }

  async _updateSiteIut(iut) {
    document.getElementById('qapes_sae_part1_iutSite').disabled = false
    await fetch(this.urlApiValue + '?action=siteIut&iut=' + iut).then(response => response.json()).then(
      data => {
        console.log(data)
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_iutSite')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      }
    )
  }

  async _updateSpecialite(siteIut) {
    document.getElementById('qapes_sae_part1_iutSite').disabled = false
    await fetch(this.urlApiValue + '?action=siteIut&iut=' + iut).then(response => response.json()).then(
      data => {
        console.log(data)
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_iutSite')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      }
    )
  }

  async _updateParcours(siteIut, specialite) {
    document.getElementById('qapes_sae_part1_iutSite').disabled = false
    await fetch(this.urlApiValue + '?action=siteIut&iut=' + iut).then(response => response.json()).then(
      data => {
        console.log(data)
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_iutSite')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      }
    )
  }

  async _updateSaeFromSpecialite(specialite) {
    document.getElementById('qapes_sae_part1_iutSite').disabled = false
    await fetch(this.urlApiValue + '?action=siteIut&iut=' + iut).then(response => response.json()).then(
      data => {
        console.log(data)
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_iutSite')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      }
    )
  }

  async _updateSaeFromParcours(specialite) {
    document.getElementById('qapes_sae_part1_iutSite').disabled = false
    await fetch(this.urlApiValue + '?action=siteIut&iut=' + iut).then(response => response.json()).then(
      data => {
        console.log(data)
        const sites = data
        let selectSites = document.getElementById('qapes_sae_part1_iutSite')
        selectSites.innerHTML = ''

        sites.forEach(site => {
          let option = document.createElement('option')
          option.value = site.id
          option.text = site.libelle
          selectSites.appendChild(option)
        })
      }
    )
  }
}
