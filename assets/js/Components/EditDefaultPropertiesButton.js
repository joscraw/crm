'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";
import EditDefaultPropertiesModal from "./EditDefaultPropertiesModal";

class EditDefaultPropertiesButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.$wrapper.on(
            'click',
            '.js-edit-default-properties-button',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick() {
        console.log("Edit Default Properties Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.EDIT_DEFAULT_PROPERTIES_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.EDIT_DEFAULT_PROPERTIES_BUTTON_CLICKED}`);

        new EditDefaultPropertiesModal(this.globalEventDispatcher,  this.portalInternalIdentifier, this.customObjectInternalName);
    }

    render() {
        debugger;
        this.$wrapper.html(EditDefaultPropertiesButton.markup(this));
    }

    static markup() {

        debugger;

        return `
      <button type="button" class="js-edit-default-properties-button btn btn-light btn--full-width">Set default properties</button>
    `;
    }
}

export default EditDefaultPropertiesButton;