'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";

class EditColumnsButton {

    constructor($wrapper, globalEventDispatcher, portal, customObject, customObjectLabel) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.customObject = customObject;
        this.customObjectLabel = customObjectLabel;
        this.portal = portal;

        this.$wrapper.on(
            'click',
            '.js-edit-columns-button',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Edit Columns Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.EDIT_COLUMNS_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.EDIT_COLUMNS_BUTTON_CLICKED}`);
        new EditColumnsModal(this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);
    }

    render() {
        debugger;
        this.$wrapper.html(EditColumnsButton.markup(this));
    }

    static markup() {

        debugger;

        return `
      <button type="button" class="dropdown-item js-edit-columns-button">Edit Columns</button>
    `;
    }
}

export default EditColumnsButton;