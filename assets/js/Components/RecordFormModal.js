'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';

class RecordFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObject
     * @param customObjectLabel
     */
    constructor(globalEventDispatcher, portal, customObject, customObjectLabel) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObject = customObject;
        this.customObjectLabel = customObjectLabel;
        this.render();
    }

    render() {
        swal({
            title: `Create ${this.customObjectLabel}`,
            showConfirmButton: false,
            html: RecordFormModal.markup()
        });

        new RecordForm($('#js-create-record-modal-container'), this.globalEventDispatcher, this.customObject, this.portal);
    }

    static markup() {
        return `
      <div id="js-create-record-modal-container"></div>
    `;
    }
}

export default RecordFormModal;