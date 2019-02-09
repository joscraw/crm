'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyGroupForm from './PropertyGroupForm';

class PropertyGroupFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor(globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.render();
    }

    render() {
        swal({
            title: 'Create Property Group',
            showConfirmButton: false,
            html: PropertyGroupFormModal.markup()
        });

        new PropertyGroupForm($('#js-create-property-group-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-create-property-group-modal-container"></div>
    `;
    }
}

export default PropertyGroupFormModal;