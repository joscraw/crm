'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import ColumnsForm from "./ColumnsForm";
import DefaultPropertiesForm from "./DefaultPropertiesForm";

class EditDefaultPropertiesModal {

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
            title: `Edit default properties`,
            showConfirmButton: false,
            html: EditDefaultPropertiesModal.markup(),
            customClass: "swal2-modal--left-align swal2-modal--swal-wide"
        });

        new DefaultPropertiesForm($('#js-edit-default-properties-modal'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-edit-default-properties-modal"></div>
    `;
    }
}

export default EditDefaultPropertiesModal;