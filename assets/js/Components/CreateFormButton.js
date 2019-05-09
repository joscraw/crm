'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import Routing from '../Routing';
import ReportWidget from "./ReportWidget";

class CreateFormButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }


    render() {
        this.$wrapper.html(CreateFormButton.markup(this));
    }

    static markup({portalInternalIdentifier}) {

        return `
        <a class="btn btn-secondary" data-bypass="true" href="${Routing.generate('form_object', {internalIdentifier: portalInternalIdentifier})}" role="button">Create Form</a>
    `;
    }
}

export default CreateFormButton;