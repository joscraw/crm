'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import BulkEditForm from "./BulkEditForm";

class BulkEditFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     * @param records
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName, records) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.records = records;
        this.render();
    }

    render() {
        swal({
            title: `Bulk edit ${this.records.length} records`,
            showConfirmButton: false,
            html: BulkEditFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new BulkEditForm($('#js-bulk-edit-record-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.records);
    }

    static markup() {
        return `
      <div id="js-bulk-edit-record-modal-container"></div>
    `;
    }
}

export default BulkEditFormModal;