'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";
import DeleteListFormModal from "./DeleteListFormModal";
import DeleteFolderFormModal from "./DeleteFolderFormModal";

class DeleteFolderButton {

    constructor($wrapper, globalEventDispatcher, portal, listId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.label = label;
        this.portal = portal;
        this.listId = listId;
        debugger;

        this.$wrapper.on(
            'click',
            '.js-open-delete-folder-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {

        new DeleteFolderFormModal(this.globalEventDispatcher, this.portal, this.listId);

    }

    render() {
        this.$wrapper.html(DeleteFolderButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-folder-modal-btn dropdown-item">${label}</button>
    `;
    }
}

export default DeleteFolderButton;