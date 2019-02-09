'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyCreateForm from './PropertyCreateForm';

class PropertyCreateFormModal {

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

    /**
     * @param globalEventDispatcher
     */
    init(globalEventDispatcher) {

    }

    render() {
        swal({
            title: 'Create Property',
            showConfirmButton: false,
            customClass: 'swal2-modal--swal-wide swal2-modal--left-align',
            html: PropertyCreateFormModal.markup()
        });

        new PropertyCreateForm($('#js-create-property-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-create-property-modal-container"></div>
    `;
    }
}

export default PropertyCreateFormModal;