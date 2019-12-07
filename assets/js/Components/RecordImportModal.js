'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import ColumnsForm from "./ColumnsForm";
import RecordImportForm from "./RecordImportForm";

class RecordImportModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor(globalEventDispatcher,  portalInternalIdentifier, customObjectInternalName) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.render();
    }

    render() {
        swal({
            title: `Import Tool`,
            showConfirmButton: false,
            html: RecordImportModal.markup(),
            customClass: "swal2-modal--left-align swal2-modal--swal-wide"
        });

        new RecordImportForm($('#js-record-import-modal'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-record-import-modal"></div>
    `;
    }
}

export default RecordImportModal;