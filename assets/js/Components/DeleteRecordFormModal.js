'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";
import DeleteRecordForm from "./DeleteRecordForm";

class DeleteRecordFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param recordId
     */
    constructor(globalEventDispatcher, portal, recordId) {

        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.recordId = recordId;
        this.render();
    }

    render() {
        swal({
            title: 'Delete Record',
            showConfirmButton: false,
            html: DeleteRecordFormModal.markup()
        });

        new DeleteRecordForm($('#js-delete-record-modal-container'), this.globalEventDispatcher, this.portal, this.recordId);
    }

    static markup() {
        return `
      <div id="js-delete-record-modal-container"></div>
    `;
    }
}

export default DeleteRecordFormModal;