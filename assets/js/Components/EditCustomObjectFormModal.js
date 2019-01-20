'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";

class EditCustomObjectFormModal {

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
            title: 'Edit Custom Object',
            showConfirmButton: false,
            html: EditCustomObjectFormModal.markup()
        });

        new EditCustomObjectForm($('#js-edit-custom-object-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectId);
    }

    static markup() {
        return `
      <div id="js-edit-custom-object-modal-container"></div>
    `;
    }
}

export default EditCustomObjectFormModal;