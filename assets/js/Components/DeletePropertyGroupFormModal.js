'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeletePropertyGroupForm from "./DeletePropertyGroupForm";

class DeletePropertyGroupFormModal {

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
            title: 'Delete Property Group',
            showConfirmButton: false,
            html: DeletePropertyGroupFormModal.markup()
        });

        new DeletePropertyGroupForm($('#js-delete-property-group-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.propertyGroupInternalName);
    }

    static markup() {
        return `
      <div id="js-delete-property-group-modal-container"></div>
    `;
    }
}

export default DeletePropertyGroupFormModal;