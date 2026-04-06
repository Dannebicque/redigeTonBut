import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['status', 'updatedAt']
  static values = {
    url: String,
    payload: Object,
  }

  connect () {
    this.applyPayloadToForm()
    this.setStatus('Brouillon', 'bg-secondary')
  }

  saveField (event) {
    const field = event.target

    if (!field.name || !this.shouldTrackField(field)) {
      return
    }

    if (field.type === 'radio' && !field.checked) {
      return
    }

    this.setStatus('Sauvegarde...', 'bg-warning text-dark')
    this.post({
      field: field.name,
      value: this.extractFieldValue(field),
    }, field)
  }

  saveAll () {
    this.setStatus('Sauvegarde...', 'bg-warning text-dark')

    const payload = {}
    const formData = new FormData(this.element)

    for (const [key, value] of formData.entries()) {
      payload[key] = String(value)
    }

    this.post({ mode: 'full', payload })
  }

  shouldTrackField (field) {
    return ['TEXTAREA', 'INPUT', 'SELECT'].includes(field.tagName)
  }

  extractFieldValue (field) {
    if (field.type === 'checkbox') {
      return field.checked ? field.value : ''
    }

    return field.value ?? ''
  }

  applyPayloadToForm () {
    if (!this.hasPayloadValue || typeof this.payloadValue !== 'object') {
      return
    }

    Object.entries(this.payloadValue).forEach(([name, value]) => {
      const fields = this.element.querySelectorAll(`[name="${CSS.escape(name)}"]`)
      if (!fields.length) {
        return
      }

      fields.forEach((field) => {
        if (field.type === 'radio') {
          field.checked = field.value === value
          return
        }

        if (field.type === 'checkbox') {
          field.checked = field.value === value
          return
        }

        field.value = value
      })
    })
  }

  async post (payload, field = null) {
    try {
      const response = await fetch(this.urlValue, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
      })

      if (!response.ok) {
        throw new Error('Erreur de sauvegarde')
      }

      const data = await response.json()
      this.setStatus('Sauvegarde OK', 'bg-success')

      if (field) {
        this.highlightField(field)
      }

      if (data.updatedAt && this.hasUpdatedAtTarget) {
        this.updatedAtTarget.textContent = this.formatDate(data.updatedAt)
      }
    } catch (e) {
      this.setStatus('Erreur de sauvegarde', 'bg-danger')
    }
  }

  highlightField (field) {
    field.classList.add('border', 'border-success')
    setTimeout(() => {
      field.classList.remove('border', 'border-success')
    }, 1200)
  }

  setStatus (label, classes) {
    if (!this.hasStatusTarget) {
      return
    }

    this.statusTarget.textContent = label
    this.statusTarget.className = `badge ${classes}`
  }

  formatDate (isoDate) {
    const date = new Date(isoDate)
    return date.toLocaleString('fr-FR')
  }
}

