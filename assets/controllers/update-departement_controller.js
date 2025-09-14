import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zoneDiff']
  static values = {
    url: String,
  }

  connect () {
    console.log('coucou')
  }

  async changeRefCompetence (event) {
    const selectedValue = event.target.value
    const zoneDiff = this.zoneDiffTarget
    const url = this.urlValue
    const params = new URLSearchParams()
    params.append('departement', selectedValue)

    const response = await fetch(this.urlValue, {
      method: 'POST',
      body: params,
    })
    if (response.ok) {
      zoneDiff.innerHTML = await response.text()
    } else {
      console.error('Error fetching data:', response.statusText)
    }
  }
}
