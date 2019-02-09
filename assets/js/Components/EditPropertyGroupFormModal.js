'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import EditPropertyGroupForm from "./EditPropertyGroupForm";

class EditPropertyGroupFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param propertyGroupInternalName
     */
    constructor(globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyGroupInternalName) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroupInternalName= propertyGroupInternalName;
        this.render();
    }

    render() {
        swal({
            title: 'Edit Property Group',
            showConfirmButton: false,
            html: EditPropertyGroupFormModal.markup()
        });

        new EditPropertyGroupForm($('#js-edit-property-group-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.propertyGroupInternalName);
    }

    static markup() {
        return `
      <div id="js-edit-property-group-modal-container"></div>
    `;
    }
}

export default EditPropertyGroupFormModal;