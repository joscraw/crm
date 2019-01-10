'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyCreateForm from './PropertyCreateForm';

class PropertyCreateFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObject
     */
    constructor(globalEventDispatcher, portal, customObject) {
        this.portal = portal;
        this.customObject = customObject;
        this.globalEventDispatcher = globalEventDispatcher;
        this.render();
    }

    /**
     * @param globalEventDispatcher
     */
    init(globalEventDispatcher) {

    }

    render() {
        debugger;
        swal({
            title: 'Create Property',
            showConfirmButton: false,
            customClass: 'swal2-modal--swal-wide',
            html: PropertyCreateFormModal.markup()
        });

        new PropertyCreateForm($('#js-create-property-modal-container'), this.globalEventDispatcher, this.portal, this.customObject);
    }

    static markup() {
        return `
      <div id="js-create-property-modal-container"></div>
    `;
    }
}

export default PropertyCreateFormModal;