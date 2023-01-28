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

  static targets = ['critereList', 'listeCriteres']

  static values = {
    urlApi: String,
    urlPost: String,
  }

  connect() {
    console.log('hello from qapes_controller.js')
    this._updateListeCritere()
  }

  async _updateListeCritere() {
    this.listeCriteresTarget.innerHTML = ''
    const response = await fetch(this.urlApiValue + '?action=listeCritere')
    this.listeCriteresTarget.innerHTML = await response.text()
  }

  async addCritere(event) {
    event.preventDefault()
    this.critereListTarget.innerHTML = ''
    const response = await fetch(this.urlApiValue + '?action=afficheFormCritere&critereId=' + document.getElementById('critere').value)
    this.critereListTarget.innerHTML = await response.text()
  }

  async sauvegardeCritere(event) {
    event.preventDefault()

    const body = {
      method: 'POST',
      body: JSON.stringify({
        critereId: document.getElementById('critere').value,
        reponse: document.getElementById('reponse').value,
        commentaire: document.getElementById('commentaire').value,
      }),
    }
    await fetch(this.urlPostValue, body).then((response) => {
      this.critereListTarget.innerHTML = ''
      document.getElementById('critere').value = ''
      this._updateListeCritere()
    })

  }

}
