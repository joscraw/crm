'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";
import DeleteListForm from "./DeleteListForm";
import DeleteFolderForm from "./DeleteFolderForm";

class DeleteFolderFormModal {

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
            title: 'Delete Folder',
            showConfirmButton: false,
            html: DeleteFolderFormModal.markup()
        });

        new DeleteFolderForm($('#js-delete-folder-modal-container'), this.globalEventDispatcher, this.portal, this.folderId);
    }

    static markup() {
        return `
      <div id="js-delete-folder-modal-container"></div>
    `;
    }
}

export default DeleteFolderFormModal;