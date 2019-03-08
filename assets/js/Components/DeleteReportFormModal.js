'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";

class DeleteReportFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param reportId
     */
    constructor(globalEventDispatcher, portal, reportId) {

        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.reportId = reportId;
        this.render();
    }

    render() {
        swal({
            title: 'Delete Report',
            showConfirmButton: false,
            html: DeleteReportFormModal.markup()
        });

        new DeleteReportForm($('#js-delete-report-modal-container'), this.globalEventDispatcher, this.portal, this.reportId);
    }

    static markup() {
        return `
      <div id="js-delete-report-modal-container"></div>
    `;
    }
}

export default DeleteReportFormModal;