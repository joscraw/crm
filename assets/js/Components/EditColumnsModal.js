'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import ColumnsForm from "./ColumnsForm";

class EditColumnsModal {

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
            title: `Edit table columns`,
            showConfirmButton: false,
            html: EditColumnsModal.markup(),
            customClass: "swal2-modal--left-align swal2-modal--swal-wide"
        });

        new ColumnsForm($('#js-edit-columns-modal'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-edit-columns-modal"></div>
    `;
    }
}

export default EditColumnsModal;