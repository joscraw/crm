'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeletePropertyGroupFormModal from "./DeletePropertyGroupFormModal";

class DeletePropertyGroupButton {

    constructor($wrapper, globalEventDispatcher, portal, customObject, propertyGroupId, label) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.label = label;
        this.portal = portal;
        this.customObject = customObject;
        this.propertyGroupId = propertyGroupId;
        debugger;

        this.$wrapper.on(
            'click',
            '.js-open-delete-property-group-modal-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleButtonClick(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        e.stopPropagation();

        console.log("Delete Property Group Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.DELETE_PROPERTY_GROUP_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.DELETE_PROPERTY_GROUP_BUTTON_CLICKED}`);
        new DeletePropertyGroupFormModal(this.globalEventDispatcher, this.portal, this.propertyGroupId, this.customObject);

    }

    render() {
        this.$wrapper.html(DeletePropertyGroupButton.markup(this));
    }

    static markup({label}) {
        return `
      <button type="button" class="js-open-delete-property-group-modal-btn btn btn-link">${label}</button>
    `;
    }
}

export default DeletePropertyGroupButton;