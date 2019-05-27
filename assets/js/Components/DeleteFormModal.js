'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";
import DeleteFormForm from "./DeleteFormForm";

class DeleteFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param uid
     */
    constructor(globalEventDispatcher, portal, uid) {

        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.uid = uid;
        this.render();
    }

    render() {
        swal({
            title: 'Delete Form',
            showConfirmButton: false,
            html: DeleteFormModal.markup()
        });

        new DeleteFormForm($('#js-delete-form-modal-container'), this.globalEventDispatcher, this.portal, this.uid);
    }

    static markup() {
        return `
      <div id="js-delete-form-modal-container"></div>
    `;
    }
}

export default DeleteFormModal;