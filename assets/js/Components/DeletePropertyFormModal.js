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
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param propertyInternalName
     */
    constructor(globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyInternalName) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyInternalName = propertyInternalName;
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
            this.portalInternalIdentifier,
            this.customObjectInternalName,
            this.propertyInternalName
        );
    }

    static markup() {
        return `
      <div id="js-delete-property-modal-container"></div>
    `;
    }
}

export default DeletePropertyFormModal;