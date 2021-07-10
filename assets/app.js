/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)

import './styles/app.scss';
import 'bootstrap-icons/font/bootstrap-icons.css'

// start the Stimulus application
import './bootstrap'
window.bootstrap = require('bootstrap/dist/js/bootstrap.bundle.min')

import './js/vendor/OverlayScrollbars.min'
import './js/vendor/clamp.min'

import './js/base/init'
import './js/common'
import './js/scripts'
import './js/base/loader'

var toastElList = [].slice.call(document.querySelectorAll('.toast'))
var toastList = toastElList.map(function (toastEl) {
  return new bootstrap.Toast(toastEl)
})

/* AlpineJs */
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

import './js/alpinejs/getUpdateRessource'
import './js/alpinejs/getUpdateSae'
import './js/alpinejs/getUpdatePreconisation'
import './js/alpinejs/getUpdateStructure'

// Without jQuery
// Define a convenience method and use it
const ready = (callback) => {
  if (document.readyState != "loading") callback();
  else document.addEventListener("DOMContentLoaded", callback);
}

ready(() => {
  /* Do things after DOM has fully loaded */
  toastList.forEach((toast) => {
    toast.show()
  })
});



