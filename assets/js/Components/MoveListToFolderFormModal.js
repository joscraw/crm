'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";
import DeleteListForm from "./DeleteListForm";
import MoveListToFolderForm from "./MoveListToFolderForm";

class MoveListToFolderFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param listId
     */
    constructor(globalEventDispatcher, portal, listId) {

        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.listId = listId;
        this.render();
    }

    render() {
        swal({
            title: 'Move To Folder',
            showConfirmButton: false,
            html: MoveListToFolderFormModal.markup()
        });

        new MoveListToFolderForm($('#js-move-list-to-folder-modal-container'), this.globalEventDispatcher, this.portal, this.listId);
    }

    static markup() {
        return `
      <div id="js-move-list-to-folder-modal-container"></div>
    `;
    }
}

export default MoveListToFolderFormModal;