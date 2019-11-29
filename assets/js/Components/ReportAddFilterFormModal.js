'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import EditCustomObjectForm from "./EditCustomObjectForm";
import DeleteCustomObjectForm from "./DeleteCustomObjectForm";
import DeleteReportForm from "./DeleteReportForm";
import DeleteListForm from "./DeleteListForm";
import MoveListToFolderForm from "./MoveListToFolderForm";
import ConnectObjectForm from "./ConnectObjectForm";
import ReportFilters from "./ReportFilters";

class ReportAddFilterFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     * @param property
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName, property) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.property = property;
        this.render();
    }

    render() {
        swal({
            title: 'Add filter',
            showConfirmButton: false,
            html: ReportAddFilterFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new ReportFilters($('#js-add-filter-form-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.property);
    }

    static markup() {
        return `
      <div id="js-add-filter-form-modal-container">
</div>
    `;
    }
}

export default ReportAddFilterFormModal;