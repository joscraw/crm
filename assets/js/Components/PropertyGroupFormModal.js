'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyGroupForm from './PropertyGroupForm';

class PropertyGroupFormModal {

    /**
     * @param globalEventDispatcher
     * @param children
     */
    constructor(globalEventDispatcher, children = {}) {
        children.propertyGroupFormModal = this;
        this.children = children;
        this.globalEventDispatcher = globalEventDispatcher;
        this.render();
    }

    render() {
        swal({
            title: 'Create Property Group',
            showConfirmButton: false,
            html: PropertyGroupFormModal.markup()
        });

        new PropertyGroupForm($('#js-create-property-group-modal-container'), this.globalEventDispatcher, this.children);
    }

    static markup() {
        return `
      <div id="js-create-property-group-modal-container"></div>
    `;
    }
}

export default PropertyGroupFormModal;