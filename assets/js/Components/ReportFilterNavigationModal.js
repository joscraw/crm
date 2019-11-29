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
import ReportFilterNavigation from "./ReportFilterNavigation";

class ReportFilterNavigationModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     * @param data
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName, data) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.render();
    }

    render() {
        swal({
            title: 'All filters',
            showConfirmButton: false,
            html: ReportFilterNavigationModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new ReportFilterNavigation($('#js-all-filters-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.data);
    }

    static markup() {
        return `
      <div id="js-all-filters-modal-container">
</div>
    `;
    }
}

export default ReportFilterNavigationModal;