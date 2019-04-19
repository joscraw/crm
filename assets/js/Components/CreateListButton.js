'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import Routing from "../Routing";

class CreateListButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.render();
    }

    render() {
        this.$wrapper.html(CreateListButton.markup(this));
    }

    static markup({portalInternalIdentifier}) {

        return `
        <a class="btn btn-secondary" data-bypass="true" href="${Routing.generate('create_list', {internalIdentifier: portalInternalIdentifier})}" role="button">Create List</a>
    `;
    }
}

export default CreateListButton;