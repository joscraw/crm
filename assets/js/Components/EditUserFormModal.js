'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import EditUserForm from "./EditUserForm";

class EditUserFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param userId
     */
    constructor(globalEventDispatcher, portal, userId) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.userId = userId;
        this.render();
    }

    render() {
        swal({
            title: 'Edit User',
            showConfirmButton: false,
            html: EditUserFormModal.markup()
        });

        new EditUserForm($('#js-edit-user-modal-container'), this.globalEventDispatcher, this.portal, this.userId);
    }

    static markup() {
        return `
      <div id="js-edit-user-modal-container"></div>
    `;
    }
}

export default EditUserFormModal;