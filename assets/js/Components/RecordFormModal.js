'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';

class RecordFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor(globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.render();
    }

    render() {
        swal({
            title: `Create Record`,
            showConfirmButton: false,
            html: RecordFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new RecordForm($('#js-create-record-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-create-record-modal-container"></div>
    `;
    }
}

export default RecordFormModal;