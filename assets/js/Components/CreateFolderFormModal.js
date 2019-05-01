'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import CreateFolderForm from "./CreateFolderForm";

class CreateFolderFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param folderId
     */
    constructor(globalEventDispatcher, portal, folderId) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.folderId = folderId;
        this.render();
    }

    render() {
        swal({
            title: 'Create Folder',
            showConfirmButton: false,
            html: CreateFolderFormModal.markup()
        });

        new CreateFolderForm($('#js-create-folder-modal-container'), this.globalEventDispatcher, this.portal, this.folderId);
    }

    static markup() {
        return `
      <div id="js-create-folder-modal-container"></div>
    `;
    }
}

export default CreateFolderFormModal;