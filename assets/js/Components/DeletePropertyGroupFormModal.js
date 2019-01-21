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
     * @param portal
     * @param propertyGroupId
     * @param customObject
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
            title: 'Delete Property Group',
            showConfirmButton: false,
            html: DeletePropertyGroupFormModal.markup()
        });

        new DeletePropertyGroupForm($('#js-delete-property-group-modal-container'), this.globalEventDispatcher, this.portal, this.propertyGroupId, this.customObject);
    }

    static markup() {
        return `
      <div id="js-delete-property-group-modal-container"></div>
    `;
    }
}

export default DeletePropertyGroupFormModal;