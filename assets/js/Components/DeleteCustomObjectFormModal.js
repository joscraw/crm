'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";

class DeleteCustomObjectFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectId
     */
    constructor(globalEventDispatcher, portal, customObjectId) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectId = customObjectId;
        this.render();
    }

    render() {
        swal({
            title: 'Delete Custom Object',
            showConfirmButton: false,
            html: DeleteCustomObjectFormModal.markup()
        });

        new DeleteCustomObjectForm($('#js-delete-custom-object-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectId);
    }

    static markup() {
        return `
      <div id="js-delete-custom-object-modal-container"></div>
    `;
    }
}

export default DeleteCustomObjectFormModal;