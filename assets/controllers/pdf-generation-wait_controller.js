import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['trigger', 'statusContainer']

  static values = {
    url: String,
    initialStatus: String,
    pollDelay: { type: Number, default: 2500 },
  }

  connect () {
    this.polling = false

    if (this.initialStatusValue === 'pending') {
      this.startPolling()
    }
  }

  disconnect () {
    this.stopPolling()
  }

  async generate (event) {
    event.preventDefault()

    if (this.polling) {
      return
    }

    this.setPendingState()
    this.startPolling(true)
  }

  startPolling (openWhenReady = false) {
    if (this.polling) {
      return
    }

    this.polling = true
    this.openWhenReady = openWhenReady
    this.tick()
  }

  stopPolling () {
    this.polling = false

    if (this.timeoutId) {
      clearTimeout(this.timeoutId)
      this.timeoutId = null
    }
  }

  async tick () {
    if (!this.polling) {
      return
    }

    try {
      const response = await fetch(this.urlValue, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
      })

      if (response.status === 202) {
        this.renderStatus('pending')
        this.timeoutId = setTimeout(() => this.tick(), this.pollDelayValue)
        return
      }

      if (response.ok) {
        this.renderStatus('present')

        if (this.openWhenReady) {
          window.open(this.urlValue, '_blank', 'noopener')
        }

        this.stopPolling()
        return
      }

      this.renderStatus('error')
      this.stopPolling()
    } catch (e) {
      this.renderStatus('error')
      this.stopPolling()
    }
  }

  setPendingState () {
    this.renderStatus('pending')

    if (this.hasTriggerTarget) {
      this.triggerTarget.classList.add('disabled')
      this.triggerTarget.setAttribute('aria-disabled', 'true')
    }
  }

  renderStatus (status) {
    if (!this.hasStatusContainerTarget) {
      return
    }

    if (status === 'present') {
      this.statusContainerTarget.innerHTML = '<span class="badge bg-success ms-2">PDF disponible</span>'
      this.restoreTrigger()
      return
    }

    if (status === 'pending') {
      this.statusContainerTarget.innerHTML = '<span class="badge bg-warning text-dark ms-2">Generation en attente</span>'
      return
    }

    if (status === 'error') {
      this.statusContainerTarget.innerHTML = '<span class="badge bg-danger ms-2">Erreur PDF</span>'
      this.restoreTrigger()
      return
    }

    this.statusContainerTarget.innerHTML = '<span class="badge bg-secondary ms-2">PDF non genere</span>'
  }

  restoreTrigger () {
    if (!this.hasTriggerTarget) {
      return
    }

    this.triggerTarget.classList.remove('disabled')
    this.triggerTarget.removeAttribute('aria-disabled')
  }
}

