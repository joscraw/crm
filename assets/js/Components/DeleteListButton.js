'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";
import DeleteListFormModal from "./DeleteListFormModal";

class DeleteListButton {

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
            '.js-open-delete-list-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        console.log("Delete List Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_LIST_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_LIST_BUTTON_CLICKED}`);
        new DeleteListFormModal(this.globalEventDispatcher, this.portal, this.listId);

    }

    render() {
        this.$wrapper.html(DeleteListButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-list-modal-btn dropdown-item">${label}</button>
    `;
    }
}

export default DeleteListButton;