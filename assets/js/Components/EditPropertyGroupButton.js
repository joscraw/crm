'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import EditPropertyGroupFormModal from "./EditPropertyGroupFormModal";

class EditPropertyGroupButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyGroupInternalName, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroupInternalName= propertyGroupInternalName;
        this.label = label;

        this.$wrapper.on(
            'click',
            '.js-open-edit-property-group-modal-btn',
            this.handleButtonClick.bind(this)
        );

        this.render();
    }

    handleButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        // We don't want the collapse panel to open on the property list view
        e.stopPropagation();

        console.log("Edit Property Group Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.EDIT_PROPERTY_GROUP_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.EDIT_PROPERTY_GROUP_BUTTON_CLICKED}`);
        new EditPropertyGroupFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.propertyGroupInternalName);

    }

    render() {
        this.$wrapper.html(EditPropertyGroupButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-edit-property-group-modal-btn btn btn-link">${label}</button>
    `;
    }
}

export default EditPropertyGroupButton;