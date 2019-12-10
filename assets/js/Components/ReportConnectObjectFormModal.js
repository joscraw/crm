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

class ReportConnectObjectFormModal {

    /**
     * @param globalEventDispatcher
     * @param portal
     * @param customObjectInternalName
     * @param parentConnectionUid
     */
    constructor(globalEventDispatcher, portal, customObjectInternalName, parentConnectionUid = null) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.parentConnectionUid = parentConnectionUid;
        this.render();
    }

    render() {
        swal({
            /*title: 'Connect an Object',*/
            showConfirmButton: false,
            html: ReportConnectObjectFormModal.markup()
        });

        new ConnectObjectForm($('#js-connect-object-form-modal-container'), this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.parentConnectionUid);
    }

    static markup() {
        return `
      <div id="js-connect-object-form-modal-container">
</div>
    `;
    }
}

export default ReportConnectObjectFormModal;