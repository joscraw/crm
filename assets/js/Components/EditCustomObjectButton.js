'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";

class EditCustomObjectButton {

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
            '.js-open-edit-custom-object-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Edit Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.EDIT_CUSTOM_OBJECT_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.EDIT_CUSTOM_OBJECT_BUTTON_CLICKED}`);
        new EditCustomObjectFormModal(this.globalEventDispatcher, this.portal, this.customObjectId);

    }

    render() {
        this.$wrapper.html(EditCustomObjectButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-edit-custom-object-modal-btn btn btn-primary btn-sm">${label}</button>
    `;
    }
}

export default EditCustomObjectButton;