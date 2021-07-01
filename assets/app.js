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
import 'bootstrap/dist/js/bootstrap.bundle.min'
import './js/vendor/OverlayScrollbars.min'
import './js/vendor/autoComplete.min'
import './js/vendor/clamp.min'

import './js/base'
import './js/common'
import './js/scripts'
import './js/base/loader'



/* AlpineJs */
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

import './js/alpinejs/getUpdateRessource'
import './js/alpinejs/getUpdateSae'



