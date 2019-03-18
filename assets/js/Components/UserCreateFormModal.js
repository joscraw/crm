'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyCreateForm from './PropertyCreateForm';
import UserCreateForm from "./UserCreateForm";

class UserCreateFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor(globalEventDispatcher, portalInternalIdentifier) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }

    /**
     * @param globalEventDispatcher
     */
    init(globalEventDispatcher) {

    }

    render() {
        swal({
            title: 'Create User',
            showConfirmButton: false,
            className: 'swal2-modal--left-align',
            html: UserCreateFormModal.markup()
        });

        new UserCreateForm($('#js-create-user-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier);
    }

    static markup() {
        return `
      <div id="js-create-user-modal-container"></div>
    `;
    }
}

export default UserCreateFormModal;