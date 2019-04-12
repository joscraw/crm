'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CustomObjectFormModal from "./CustomObjectFormModal";
import EditCustomObjectFormModal from "./EditCustomObjectFormModal";
import DeleteCustomObjectFormModal from "./DeleteCustomObjectFormModal";
import DeleteReportFormModal from "./DeleteReportFormModal";

class ListPreviewResultsButton {

    constructor($wrapper, globalEventDispatcher) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        debugger;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            '.js-list-preview-results-btn',
            this.handleButtonClick.bind(this)
        );
        this.render();
    }

    unbindEvents() {
        this.$wrapper.off('click', '.js-list-preview-results-btn');
    }

    handleButtonClick() {
        console.log("List Preview Results Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.LIST_PREVIEW_RESULTS_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.LIST_PREVIEW_RESULTS_BUTTON_CLICKED}`);
    }

    render() {
        this.$wrapper.html(ListPreviewResultsButton.markup(this));
    }

    static markup() {
        return `
      <button type="button" class="btn btn-primary js-list-preview-results-btn c-report-widget__preview-results-btn">Preview Results</button>
    `;
    }
}

export default ListPreviewResultsButton;