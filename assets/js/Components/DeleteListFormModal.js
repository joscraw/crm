'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";
import DeleteListForm from "./DeleteListForm";

class DeleteListFormModal {

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
            title: 'Delete List',
            showConfirmButton: false,
            html: DeleteListFormModal.markup()
        });

        new DeleteListForm($('#js-delete-list-modal-container'), this.globalEventDispatcher, this.portal, this.listId);
    }

    static markup() {
        return `
      <div id="js-delete-list-modal-container"></div>
    `;
    }
}

export default DeleteListFormModal;