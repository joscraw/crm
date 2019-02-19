'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import FilterList from "./FilterList";
import FilterNavigation from "./FilterNavigation";
import EditSingleLineTextFieldFilterForm from "./EditSingleLineTextFieldFilterForm";
import NumberFieldFilterForm from "./NumberFieldFilterForm";
import EditNumberFieldFilterForm from "./EditNumberFieldFilterForm";
import DatePickerFieldFilterForm from "./DatePickerFieldFilterForm";
import SingleCheckboxFieldFilterForm from "./SingleCheckboxFieldFilterForm";
import EditDatePickerFieldFilterForm from "./EditDatePickerFieldFilterForm";
import EditSingleCheckboxFieldFilterForm from "./EditSingleCheckboxFieldFilterForm";
import DropdownSelectFieldFilterForm from "./DropdownSelectFieldFilterForm";
import EditDropdownSelectFieldFilterForm from "./EditDropdownSelectFieldFilterForm";
import MultilpleCheckboxFieldFilterForm from "./MultilpleCheckboxFieldFilterForm";
import EditMultipleCheckboxFieldFilterForm from "./EditMultipleCheckboxFieldFilterForm";
import ArrayHelper from "../ArrayHelper";
import ReportSelectCustomObject from "./ReportSelectCustomObject";

class ReportWidget {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = null;

   /*     this.globalEventDispatcher.subscribe(
            Settings.Events.CUSTOM_OBJECT_FOR_REPORT_SELECTED,
            this.handleCustomObjectForReportSelected.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.BACK_TO_SELECT_CUSTOM_OBJECT_FOR_REPORT_BUTTON_PRESSED,
            this.handleBackToSelectCustomObjectForReportButtonPressed.bind(this)
        );
*/

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {}
    }

    handleCustomObjectForReportSelected(customObjectInternalName) {
        this.customObjectInternalName = customObjectInternalName;
    }

    render() {

        this.$wrapper.html(ReportWidget.markup(this));
        new ReportSelectCustomObject($('.js-report-select-custom-object'), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    static markup() {

        return `
      <div class="js-report-widget">
            <div class="js-report-select-custom-object"></div>
      </div>
    `;
    }
}

export default ReportWidget;