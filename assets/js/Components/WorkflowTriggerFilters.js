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

class WorkflowTriggerFilters {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, trigger) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.trigger = trigger;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            WorkflowTriggerFilters._selectors.removeFilterIcon,
            this.handleRemoveFilterIconPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerFilters._selectors.addAndFilterButton,
            this.handleAddAndFilterButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerFilters._selectors.addFilterButton,
            this.handleAddFilterButtonPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerFilters._selectors.filter,
            this.handleFilterPressed.bind(this)
        );

        this.$wrapper.on(
            'click',
            WorkflowTriggerFilters._selectors.newTrigger,
            this.handleNewTriggerButtonPressed.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.WORKFLOW_TRIGGER_FILTER_REMOVED,
            this.reportFilterItemRemovedHandler.bind(this)
        );


        this.render(this.trigger);
    }

    static get _selectors() {
        return {

            addFilterButton: '.js-add-filter-button',
            addOrFilterButton: '.js-add-or-filter-button',
            selectedCustomFilters: '.js-selected-custom-filters',
            filterContainer: '.js-filter-container',
            removeFilterIcon: '.js-remove-filter-icon',
            filter: '.js-filter',
            addAndFilterButton: '.js-add-and-filter-button',
            newTrigger: '.js-new-trigger-button'
        }
    }

    unbindEvents() {
        this.$wrapper.off('click', WorkflowTriggerFilters._selectors.removeFilterIcon);
        this.$wrapper.off('click', WorkflowTriggerFilters._selectors.addAndFilterButton);
        this.$wrapper.off('click', WorkflowTriggerFilters._selectors.addFilterButton);
        this.$wrapper.off('click', WorkflowTriggerFilters._selectors.filter);
        this.$wrapper.off('click', WorkflowTriggerFilters._selectors.newTrigger);
    }

    handleFilterPressed(e) {

        const $filter = $(e.currentTarget);
        let uid = $filter.attr('data-uid');
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_EDIT_FILTER_CLICKED, uid);
    }

    handleRemoveFilterIconPressed(e) {

        debugger;
        e.stopPropagation();

        const $removeIcon = $(e.currentTarget);

        let uid = $removeIcon.attr('data-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_REMOVE_FILTER_BUTTON_PRESSED, uid);
    }

    handleAddFilterButtonPressed() {
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_ADD_FILTER_BUTTON_PRESSED);
    }

    handleNewTriggerButtonPressed() {
        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_NEW_TRIGGER_BUTTON_PRESSED);
    }

    handleAddAndFilterButtonPressed(e) {

        const $card = $(e.currentTarget);
        let andUid = $card.attr('data-and-uid');

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_TRIGGER_ADD_OR_FILTER_BUTTON_PRESSED, andUid);
    }

    reportFilterItemAddedHandler(workflow, trigger) {
         this.renderCustomFilters(trigger);
    }

    reportFilterItemRemovedHandler(workflow, trigger) {
        this.renderCustomFilters(trigger);
    }

    updateAddFilterButtonText(data) {

        let text = "Add Filter";

        if(this.getCustomFilterCount(data) !== 0) {

            text = 'Add "OR" Filter';
        }

        this.$wrapper.find(WorkflowTriggerFilters._selectors.addFilterButton).html('<i class="fa fa-plus"></i> ' + text);

    }

    renderCustomFilters(trigger) {

        debugger;
        let filters = trigger.filters;

        this.$wrapper.find(WorkflowTriggerFilters._selectors.selectedCustomFilters).html("");

        let i = 1;
        for(let filter of filters) {
            debugger;

            let text = "";

            // if the filter doesn't reference another filter than the filter is a parent filter
            if(!filter.referencedFilterPath) {

                if(i !== 1) {
                    let conditionalHtml = conditionalTemplate("Or");
                    let $conditionalTemplate = $($.parseHTML(conditionalHtml));
                    this.$wrapper.find(WorkflowTriggerFilters._selectors.selectedCustomFilters).append($conditionalTemplate);
                }

                debugger;
                text = FilterHelper.getFilterTextFromCustomFilter(filter);
                const html = filterContainerTemplate(filter.uid);
                const $filterContainerTemplate = $($.parseHTML(html));
                const $filters = $filterContainerTemplate.find('.js-filters');
                const filterHtml = filterTemplate(text, filter.uid);
                const $filterTemplate = $($.parseHTML(filterHtml));
                $filters.append($filterTemplate);

                // let's go ahead and add any child filters
                debugger;
                if(_.has(filter, 'andFilters')) {
                    let andFilters = filter.andFilters;
                    for (let andFilter of andFilters) {

                        debugger;
                        let index = this.trigger.filters.findIndex(filter => filter.uid === andFilter);
                        let filter = filters[index];

                        let conditionalHtml = conditionalTemplate("And");
                        let $conditionalTemplate = $($.parseHTML(conditionalHtml));

                        $filters.append($conditionalTemplate);
                        text = FilterHelper.getFilterTextFromCustomFilter(filter);

                        const filterHtml = filterTemplate(text, filter.uid);
                        const $filterTemplate = $($.parseHTML(filterHtml));
                        $filters.append($filterTemplate);
                    }
                }
                this.$wrapper.find(WorkflowTriggerFilters._selectors.selectedCustomFilters).append($filterContainerTemplate);
            }

            i++;
        }

    }

    getCustomFilterCount(data) {

        debugger;
        let customFilters = {};
        function search(data) {

            for(let key in data) {

                if(isNaN(key) && key !== 'filters') {

                    search(data[key]);

                } else if(key === 'filters'){

                    for(let uID in data[key]) {

                        // only add the custom filter to the array if it is not an "OR" condition
                        if(_.size(_.get(data, `${key}.${uID}.referencedFilterPath`, [])) === 0) {

                            _.set(customFilters, uID, data[key][uID]);

                        }

                    }
                }
            }
        }

        debugger;
        search(data);

        return Object.entries(customFilters).length;
    }

    render(trigger) {
        this.$wrapper.html(WorkflowTriggerFilters.markup());
        this.renderCustomFilters(trigger);
    }

    static markup() {

        return `
            <ul class="nav nav-pills flex-column">
              <li class="nav-item">
                <a class="nav-link active js-all-records-button" href="javascript:void()">All Filters</a>
              </li>
              <li class="nav-item">
                <div class="js-selected-custom-filters c-report-widget__selected-filters"></div>
              </li>
              <li class="nav-item">
                <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>
                <button type="button" class="btn btn-link js-new-trigger-button float-right"><i class="fa fa-plus"></i> New Trigger</button>
              </li>
             </ul>
    `;
    }
}

const filterContainerTemplate = (uid) => `
    <div class="card">
        <div class="card-body js-filter-container">
        
        <div class="js-filters"></div>
        
        <button type="button" class="btn btn-link js-add-and-filter-button" data-and-uid="${uid}"><i class="fa fa-plus"></i> Add Filter</button>
        </div>
    </div>
`;

const filterTemplate = (text, uid) => `
    <div class="card js-filter" data-uid="${uid}">
        <div class="card-body">
        <h5 class="card-title">${text}</h5>     
        <span><i class="fa fa-times js-remove-filter-icon c-report-widget__filter-remove-icon" aria-hidden="true" data-uid="${uid}"></i></span>
        </div>
    </div>
`;

const conditionalTemplate = (text) => `
    <p style="margin-top: 10px; margin-bottom: 10px">${text}</p>
`;



export default WorkflowTriggerFilters;