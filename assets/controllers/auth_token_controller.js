import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['token']

    copypaste() {
        navigator.clipboard.writeText(this.tokenTarget.innerText);
        alert('Token copied to clipboard');
    }
}