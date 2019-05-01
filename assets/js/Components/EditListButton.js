'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";

class EditListButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, listId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.listId = listId;

        this.render();
    }

    render() {
        this.$wrapper.html(EditListButton.markup(this));
    }

    static markup({portalInternalIdentifier, listId}) {

        return `
        <a class="dropdown-item" href="${Routing.generate('edit_list', {'listId' : listId, 'internalIdentifier' : portalInternalIdentifier})}" data-bypass="true">Edit</a>
    `;
    }
}

export default EditListButton;