'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';
import ColumnsForm from "./ColumnsForm";
import SavedFiltersList from "./SavedFiltersList";

class SavedFiltersModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor(globalEventDispatcher,  portalInternalIdentifier, customObjectInternalName) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.render();
    }

    render() {
        swal({
            title: `Saved Filters`,
            showConfirmButton: false,
            html: SavedFiltersModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new SavedFiltersList($('#js-saved-filters-modal'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {
        return `
      <div id="js-saved-filters-modal"></div>
    `;
    }
}

export default SavedFiltersModal;