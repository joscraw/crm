'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import EditUserFormModal from "./EditUserFormModal";

class EditUserButton {

    constructor($wrapper, globalEventDispatcher, portal, userId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.userId = userId;
        this.label = label;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            '.js-open-edit-user-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    unbindEvents() {

        this.$wrapper.off('click', '.js-open-edit-user-modal-btn');

    }

    handleButtonClick() {
        console.log("Edit User Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.EDIT_USER_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.EDIT_USER_BUTTON_CLICKED}`);


        new EditUserFormModal(this.globalEventDispatcher, this.portal, this.userId);

    }

    render() {
        this.$wrapper.html(EditUserButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="btn btn-primary btn-sm js-open-edit-user-modal-btn">${label}</button>
    `;
    }
}

export default EditUserButton;