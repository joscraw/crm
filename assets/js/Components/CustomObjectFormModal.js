'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';

class CustomObjectFormModal {
    constructor(globalEventDispatcher) {
        this.init(globalEventDispatcher);
    }

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

        new CustomObjectForm($('#js-custom-object-form-container'));
    }

    static markup() {
        return `
      <div id="js-custom-object-form-container"></div>
    `;
    }
}

export default CustomObjectFormModal;