'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeletePropertyFormModal from "./DeletePropertyFormModal";

class DeletePropertyButton {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyInternalName, label) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyInternalName= propertyInternalName;
        this.label = label;

        this.$wrapper.on(
            'click',
            '.js-open-delete-property-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick() {
        console.log("Delete Property Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_PROPERTY_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_PROPERTY_BUTTON_CLICKED}`);
        new DeletePropertyFormModal(this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.propertyInternalName);

    }

    render() {
        this.$wrapper.html(DeletePropertyButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-property-modal-btn btn btn-primary btn-sm">${label}</button>
    `;
    }
}

export default DeletePropertyButton;