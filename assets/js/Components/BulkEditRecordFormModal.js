'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import BulkEditRecord from "./BulkEditRecord";

class BulkEditRecordFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.render();
    }

    render() {
        swal({
            title: 'Bulk edit record',
            showConfirmButton: false,
            html: BulkEditRecordFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new BulkEditRecord($('#js-bulk-edit-record-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-bulk-edit-record-modal-container"></div>
    `;
    }
}

export default BulkEditRecordFormModal;