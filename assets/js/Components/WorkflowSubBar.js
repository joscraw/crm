'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import swal from 'sweetalert2';
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
import ReportPropertyList from "./ReportPropertyList";
import ReportSelectedColumns from "./ReportSelectedColumns";
import ReportSelectedColumnsCount from "./ReportSelectedColumnsCount";
import ReportFilterList from "./ReportFilterList";
import ReportFilters from "./ReportFilters";
import ReportProperties from "./ReportProperties";
import ListPropertyList from "./ListPropertyList";
import ListSelectedColumns from "./ListSelectedColumns";
import ListSelectedColumnsCount from "./ListSelectedColumnsCount";
import FormEditorPropertyList from "./FormEditorPropertyList";
import StringHelper from "../StringHelper";
import FormEditorFormPreview from "./FormEditorFormPreview";
import FormEditorEditFieldForm from "./FormEditorEditFieldForm";

class WorkflowSubBar {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, workflow, page) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.workflow = workflow;
        this.page = page;

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {}
    }

    render() {
        this.$wrapper.html(WorkflowSubBar.markup(this));
        this.underlineLink();
    }

    underlineLink() {
        if(this.page === Settings.PAGES.WORKFLOW_TRIGGERS) {
            this.$wrapper.find('.js-workflow-triggers-underline').addClass('c-private-sub-bar__underline--active');
        } else if(this.page === Settings.PAGES.WORKFLOW_ACTIONS) {
            this.$wrapper.find('.js-workflow-actions-underline').addClass('c-private-sub-bar__underline--active');
        }
    }

    static markup({portalInternalIdentifier, workflow}) {

        return `            
        <div class="c-private-sub-bar">
           <a class="c-private-sub-bar__link" href="${Routing.generate('workflow_trigger', {internalIdentifier: portalInternalIdentifier, uid: workflow.uid})}">Triggers <span class="c-private-sub-bar__underline js-workflow-triggers-underline"></span></a>
           <a class="c-private-sub-bar__link" href="${Routing.generate('workflow_action', {internalIdentifier: portalInternalIdentifier, uid: workflow.uid})}">Actions <span class="c-private-sub-bar__underline js-workflow-actions-underline"></span></a>
        </div>
    `;
    }
}

export default WorkflowSubBar;