'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';

class CustomObjectFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     */
    constructor(globalEventDispatcher, portal) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.render();
    }

    render() {
        swal({
            title: 'Create Custom Object',
            showConfirmButton: false,
            html: CustomObjectFormModal.markup()
        });

        new CustomObjectForm($('#js-create-custom-object-modal-container'), this.globalEventDispatcher, this.portal);
    }

    static markup() {
        return `
      <div id="js-create-custom-object-modal-container"></div>
    `;
    }
}

export default CustomObjectFormModal;