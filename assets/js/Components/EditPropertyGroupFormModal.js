'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import EditPropertyGroupForm from "./EditPropertyGroupForm";

class EditPropertyGroupFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param propertyGroupId
     */
    constructor(globalEventDispatcher, portal, propertyGroupId, customObject) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.propertyGroupId = propertyGroupId;
        this.customObject = customObject;
        this.render();
    }

    render() {
        swal({
            title: 'Edit Property Group',
            showConfirmButton: false,
            html: EditPropertyGroupFormModal.markup()
        });

        new EditPropertyGroupForm($('#js-edit-property-group-modal-container'), this.globalEventDispatcher, this.portal, this.propertyGroupId, this.customObject);
    }

    static markup() {
        return `
      <div id="js-edit-property-group-modal-container"></div>
    `;
    }
}

export default EditPropertyGroupFormModal;