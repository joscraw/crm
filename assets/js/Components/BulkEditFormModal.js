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
     * @param data
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName, data) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.render();
    }

    render() {
        swal({
            title: `Bulk Edit`,
            showConfirmButton: false,
            html: BulkEditFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new BulkEditForm($('#js-bulk-edit-record-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.data);
    }

    static markup() {
        return `
      <div id="js-bulk-edit-record-modal-container"></div>
    `;
    }
}

export default BulkEditFormModal;