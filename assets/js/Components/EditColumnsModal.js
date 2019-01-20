'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import ColumnsForm from "./ColumnsForm";

class EditColumnsModal {

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
            title: `Edit ${this.customObjectLabel} table columns`,
            showConfirmButton: false,
            html: EditColumnsModal.markup(),
            customClass: "swal2-modal--left-align swal2-modal--swal-wide"
        });

        new ColumnsForm($('#js-edit-columns-modal'), this.globalEventDispatcher, this.customObject, this.customObjectLabel, this.portal);
    }

    static markup() {
        return `
      <div id="js-edit-columns-modal"></div>
    `;
    }
}

export default EditColumnsModal;