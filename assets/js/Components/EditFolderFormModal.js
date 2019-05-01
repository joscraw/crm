'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import CreateFolderForm from "./CreateFolderForm";
import EditFolderForm from "./EditFolderForm";

class EditFolderFormModal {

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
            title: 'Edit Folder',
            showConfirmButton: false,
            html: EditFolderFormModal.markup()
        });

        new EditFolderForm($('#js-edit-folder-modal-container'), this.globalEventDispatcher, this.portal, this.folderId);
    }

    static markup() {
        return `
      <div id="js-edit-folder-modal-container"></div>
    `;
    }
}

export default EditFolderFormModal;