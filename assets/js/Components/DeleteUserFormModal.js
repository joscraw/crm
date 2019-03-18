'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteUserForm from "./DeleteUserForm";

class DeleteUserFormModal {

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
            title: 'Delete User',
            showConfirmButton: false,
            html: DeleteUserFormModal.markup()
        });

        new DeleteUserForm($('#js-delete-user-modal-container'), this.globalEventDispatcher, this.portal, this.userId);
    }

    static markup() {
        return `
      <div id="js-delete-user-modal-container"></div>
    `;
    }
}

export default DeleteUserFormModal;