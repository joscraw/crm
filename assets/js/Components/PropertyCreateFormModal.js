'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyCreateForm from './PropertyCreateForm';

class PropertyCreateFormModal {

    /**
     * @param globalEventDispatcher
     * @param children
     */
    constructor(globalEventDispatcher, children) {
        children.propertyCreateFormModal = this;
        this.children = children;
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
            customClass: 'swal-wide',
            html: PropertyCreateFormModal.markup()
        });

        new PropertyCreateForm($('#js-create-property-modal-container'), this.globalEventDispatcher, this.children);
    }

    static markup() {
        return `
      <div id="js-create-property-modal-container"></div>
    `;
    }
}

export default PropertyCreateFormModal;