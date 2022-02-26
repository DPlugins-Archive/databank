import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['token']

    copypaste() {
        prompt("Copy & Paste the following token into the plugin's setting to provide account access", this.tokenTarget.innerText);
    }
}