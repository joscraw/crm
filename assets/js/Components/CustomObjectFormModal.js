'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';

class CustomObjectFormModal {

    /**
     * @param globalEventDispatcher
     */
    constructor(globalEventDispatcher) {
        this.init(globalEventDispatcher);
    }

    /**
     * @param globalEventDispatcher
     */
    init(globalEventDispatcher) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.render();
    }

    render() {
        swal({
            title: 'Create Custom Object',
            showConfirmButton: false,
            html: CustomObjectFormModal.markup()
        });

        new CustomObjectForm($('#js-create-custom-object-modal-container'), this.globalEventDispatcher);
    }

    static markup() {
        return `
      <div id="js-create-custom-object-modal-container"></div>
    `;
    }
}

export default CustomObjectFormModal;