'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeletePropertyForm from "./DeletePropertyForm";

class DeletePropertyFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectId
     * @param propertyId
     */
    constructor(globalEventDispatcher, portal, customObjectId, propertyId) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectId = customObjectId;
        this.propertyId = propertyId;
        this.render();
    }

    render() {
        swal({
            title: 'Delete Property',
            showConfirmButton: false,
            html: DeletePropertyFormModal.markup()
        });

        new DeletePropertyForm(
            $('#js-delete-property-modal-container'),
            this.globalEventDispatcher,
            this.portal,
            this.customObjectId,
            this.propertyId
        );
    }

    static markup() {
        return `
      <div id="js-delete-property-modal-container"></div>
    `;
    }
}

export default DeletePropertyFormModal;