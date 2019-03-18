'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteUserFormModal from "./DeleteUserFormModal";

class DeleteUserButton {

    constructor($wrapper, globalEventDispatcher, portal, userId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.userId = userId;
        this.label = label;

        this.$wrapper.on(
            'click',
            '.js-open-delete-user-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        console.log("Delete User Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_USER_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_USER_BUTTON_CLICKED}`);
        new DeleteUserFormModal(this.globalEventDispatcher, this.portal, this.userId);

    }

    render() {
        this.$wrapper.html(DeleteUserButton.markup(this));
    }

    static markup({label}) {
        return `
       <button type="button" class="btn btn-primary btn-sm js-open-delete-user-modal-btn">${label}</button>
    `;
    }
}

export default DeleteUserButton;