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
import Settings from "../Settings";
import List from "list.js";
import Routing from "../Routing";
import BulkEditForm from "./BulkEditForm";
import ReportSelectPropertyForFilterList from "./ReportSelectPropertyForFilterList";

class ReportSelectPropertyForFilterFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param data
     * @param parentFilterUid
     */
    constructor(globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data, parentFilterUid = null) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;
        this.parentFilterUid = parentFilterUid;
        this.lists = [];
        this.render();
    }

    render() {
        swal({
            title: 'Select Property For Filter',
            showConfirmButton: false,
            html: ReportSelectPropertyForFilterFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });

        new ReportSelectPropertyForFilterList($('#js-property-list-for-filter'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.data, this.parentFilterUid);
    }
    static markup() {
        return `
        <div id="js-property-list-for-filter"></div>
    `;
    }
}

export default ReportSelectPropertyForFilterFormModal;