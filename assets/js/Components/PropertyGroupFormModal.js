'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyGroupForm from './PropertyGroupForm';

class PropertyGroupFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalId
     * @param customObjectId
     */
    constructor(globalEventDispatcher, portalId, customObjectId) {
        this.portalId = portalId;
        this.customObjectId = customObjectId;
        this.globalEventDispatcher = globalEventDispatcher;
        this.render();
    }

    render() {
        swal({
            title: 'Create Property Group',
            showConfirmButton: false,
            html: PropertyGroupFormModal.markup()
        });

        new PropertyGroupForm($('#js-create-property-group-modal-container'), this.globalEventDispatcher, this.portalId, this.customObjectId);
    }

    static markup() {
        return `
      <div id="js-create-property-group-modal-container"></div>
    `;
    }
}

export default PropertyGroupFormModal;