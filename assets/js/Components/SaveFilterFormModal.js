'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import SaveFilterForm from "./SaveFilterForm";

class SaveFilterFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     * @param customFilters
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName, customFilters) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.customFilters = customFilters;
        this.render();
    }

    render() {
        swal({
            title: 'Save Filter',
            showConfirmButton: false,
            html: SaveFilterFormModal.markup()
        });

        new SaveFilterForm($('#js-save-filter-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.customFilters);
    }

    static markup() {
        return `
      <div id="js-save-filter-modal-container"></div>
    `;
    }
}

export default SaveFilterFormModal;