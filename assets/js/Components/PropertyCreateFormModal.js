'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import PropertyCreateForm from './PropertyCreateForm';

class PropertyCreateFormModal {

    /**
     * @param globalEventDispatcher
     */
    constructor(globalEventDispatcher) {
        debugger;
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
            title: 'Create Property',
            showCancelButton: true,
            customClass: 'swal-wide',
            html: PropertyCreateFormModal.markup()
        });

        new PropertyCreateForm($('#js-create-property-modal-container'), this.globalEventDispatcher);
    }

    static markup() {
        return `
      <div id="js-create-property-modal-container"></div>
    `;
    }
}

export default PropertyCreateFormModal;