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
import ReportPropertyList from "./ReportPropertyList";
import ReportSelectedColumns from "./ReportSelectedColumns";
import ReportSelectedColumnsCount from "./ReportSelectedColumnsCount";
import ReportFilterList from "./ReportFilterList";
import ReportSelectedCustomFilters from "./ReportSelectedCustomFilters";
import FilterHelper from "../FilterHelper";

class ReportFilterNavigation {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, data = {}) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        /**
         * This data object is responsible for storing all the properties and filters that will get sent to the server
         * @type {{}}
         */
        this.data = data;

        this.globalEventDispatcher.addRemovableToken(
            this.globalEventDispatcher.subscribe(
                Settings.Events.REPORT_FILTER_ITEM_REMOVED,
                this.renderCustomFilters.bind(this)
            ));

        this.$wrapper.on(
            'click',
            ReportFilterNavigation._selectors.removeFilterIcon,
            this.handleRemoveFilterIconPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilterNavigation._selectors.addAndFilterButton,
            this.handleAddAndFilterButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            ReportFilterNavigation._selectors.addOrFilterButton,
            this.handleAddOrFilterButtonPressed.bind(this)
        );

        this.render(data);
    }

    static get _selectors() {
        return {
            addOrFilterButton: '.js-add-or-filter-button',
            addAndFilterButton: '.js-add-and-filter-button',
            reportSelectedCustomFilters: '.js-report-selected-custom-filters',
            filterContainer: '.js-filter-container',
            removeFilterIcon: '.js-remove-filter-icon',
            filter: '.js-filter'
        }
    }

    unbindEvents() {
        this.$wrapper.off('click', ReportFilterNavigation._selectors.addOrFilterButton);
        this.$wrapper.off('click', ReportFilterNavigation._selectors.addAndFilterButton);
        this.$wrapper.off('click', ReportFilterNavigation._selectors.removeFilterIcon);
    }

    handleRemoveFilterIconPressed(e) {
        debugger;
        e.stopPropagation();
        const $removeIcon = $(e.currentTarget);
        let filterUid = $removeIcon.attr('data-filter-uid');
        this.globalEventDispatcher.publish(Settings.Events.REPORT_REMOVE_FILTER_BUTTON_PRESSED, filterUid);
    }

    handleAddOrFilterButtonPressed() {
        this.globalEventDispatcher.publish(Settings.Events.REPORT_ADD_FILTER_BUTTON_PRESSED);
    }

    handleAddAndFilterButtonPressed(e) {
        const $card = $(e.currentTarget);
        let parentFilterUid = $card.attr('data-parent-filter-uid');
        this.globalEventDispatcher.publish(Settings.Events.REPORT_ADD_AND_FILTER_BUTTON_PRESSED, parentFilterUid);
    }

    renderCustomFilters(data) {
        let customFilters = data.filters;
        this.$wrapper.find(ReportFilterNavigation._selectors.reportSelectedCustomFilters).html("");
        let i = 1;
        for(let uid in customFilters) {
            let customFilter = _.get(customFilters, uid, false);
            let text = "";
            if(customFilter.hasParentFilter) {
                continue;
            }
            text = FilterHelper.getFilterTextFromCustomFilterForReport(customFilter);
            const html = filterContainerTemplate(uid);
            const $filterContainerTemplate = $($.parseHTML(html));
            const $filters = $filterContainerTemplate.find('.js-filters');
            const filterHtml = filterTemplate(text, uid);
            const $filterTemplate = $($.parseHTML(filterHtml));
            $filters.append($filterTemplate);
            // render the child filters
            if(_.has(customFilter, 'childFilters') && !_.isEmpty(customFilter.childFilters)) {
                let childFilters = _.get(customFilter, 'childFilters');
                for(let uid in childFilters) {
                    let childFilter = childFilters[uid];
                    let conditionalHtml = conditionalTemplate("And");
                    let $conditionalTemplate = $($.parseHTML(conditionalHtml));
                    $filters.append($conditionalTemplate);
                    text = FilterHelper.getFilterTextFromCustomFilterForReport(childFilter);
                    const filterHtml = filterTemplate(text, uid);
                    const $filterTemplate = $($.parseHTML(filterHtml));
                    $filters.append($filterTemplate);
                }
            }
            this.$wrapper.find(ReportFilterNavigation._selectors.reportSelectedCustomFilters).append($filterContainerTemplate);
            if(Object.keys(customFilters).length !== i) {
                let conditionalHtml = conditionalTemplate("Or");
                let $conditionalTemplate = $($.parseHTML(conditionalHtml));
                this.$wrapper.find(ReportFilterNavigation._selectors.reportSelectedCustomFilters).append($conditionalTemplate);
            }
            i++;
        }
    }

    render(data) {
        this.$wrapper.html(ReportFilterNavigation.markup());
        this.renderCustomFilters(data);
    }

    static markup() {
        return `
            <ul class="nav nav-pills flex-column">
              <li class="nav-item">
                <a class="nav-link active js-all-records-button" href="javascript:void()">All Filters</a>
              </li>
              <li class="nav-item">
                <div class="js-report-selected-custom-filters c-report-widget__selected-filters"></div>
              </li>
              <li class="nav-item">
                <button type="button" class="btn btn-link js-add-or-filter-button"><i class="fa fa-plus"></i> Add "OR" Filter</button>
              </li>
             </ul>
    `;
    }
}

const filterContainerTemplate = (uid) => `
    <div class="card">
        <div class="card-body js-filter-container">
        <div class="js-filters"></div>
        <button type="button" class="btn btn-link js-add-and-filter-button" data-parent-filter-uid=${uid}><i class="fa fa-plus"></i> Add "AND" Filter</button>
        </div>
    </div>
`;

const filterTemplate = (text, filterUid) => `
    <div class="card js-filter">
        <div class="card-body">
        <h5 class="card-title">${text}</h5>     
        <span><i class="fa fa-times js-remove-filter-icon c-report-widget__filter-remove-icon" data-filter-uid=${filterUid} aria-hidden="true"></i></span>
        </div>
    </div>
`;

const conditionalTemplate = (text) => `
    <p style="margin-top: 10px; margin-bottom: 10px">${text}</p>
`;



export default ReportFilterNavigation;