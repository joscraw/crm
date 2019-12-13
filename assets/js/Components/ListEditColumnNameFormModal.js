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
import ListEditColumnNameForm from "./ListEditColumnNameForm";

class ListEditColumnNameFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param property
     */
    constructor(globalEventDispatcher, portal, property) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.property = property;
        this.render();
    }

    render() {
        swal({
            title: 'Edit Column Name',
            showConfirmButton: false,
            html: ListEditColumnNameFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new ListEditColumnNameForm($('#js-edit-column-name-form-modal-container'), this.globalEventDispatcher, this.portal, this.property);
    }

    static markup() {
        return `
      <div id="js-edit-column-name-form-modal-container">
</div>
    `;
    }
}

export default ListEditColumnNameFormModal;