'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";
import DeleteListFormModal from "./DeleteListFormModal";
import MoveListToFolderFormModal from "./MoveListToFolderFormModal";
import ReportConnectObjectFormModal from "./ReportConnectObjectFormModal";
import ReportFilterNavigationModal from "./ReportFilterNavigationModal";

class ReportAllFiltersButton {

    constructor($wrapper, globalEventDispatcher, portal, customObjectInternalName, data = {}) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;
        this.customObjectInternalName = customObjectInternalName;
        this.data = data;

        this.globalEventDispatcher.subscribe(
            Settings.Events.REPORT_FILTER_ITEM_ADDED,
            this.handleReportFilterItemAdded.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-report-all-filters-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    handleReportFilterItemAdded(data) {
        debugger;
        this.data = data;
    }

    handleButtonClick() {
        debugger;
        new ReportFilterNavigationModal(this.globalEventDispatcher, this.portal, this.customObjectInternalName, this.data);
    }

    render() {
        this.$wrapper.html(ReportAllFiltersButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="js-report-all-filters-btn btn btn-link">All Filters</button>
    `;
    }
}

export default ReportAllFiltersButton;