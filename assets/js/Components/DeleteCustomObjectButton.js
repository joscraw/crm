'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";

class DeleteCustomObjectButton {

    constructor($wrapper, globalEventDispatcher, portal, customObjectId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.label = label;
        this.portal = portal;
        this.customObjectId = customObjectId;
        debugger;

        this.$wrapper.on(
            'click',
            '.js-open-delete-custom-object-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        console.log("Delete Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_CUSTOM_OBJECT_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_CUSTOM_OBJECT_BUTTON_CLICKED}`);
        new DeleteCustomObjectFormModal(this.globalEventDispatcher, this.portal, this.customObjectId);

    }

    render() {
        this.$wrapper.html(DeleteCustomObjectButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-custom-object-modal-btn btn btn-primary btn-sm">${label}</button>
    `;
    }
}

export default DeleteCustomObjectButton;