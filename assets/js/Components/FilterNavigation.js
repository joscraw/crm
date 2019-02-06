'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import FilterList from "./FilterList";

class FilterNavigation {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyGroups = [];
        this.lists = [];
        this.customFilters = [];

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            FilterNavigation._selectors.addFilterButton,
            this.handleAddFilterButtonClicked.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED,
            this.applyCustomFilterButtonPressedHandler.bind(this)
        );

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            addFilterButton: '.js-add-filter-button',
            propertyList: '.js-property-list'
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', FilterNavigation._selectors.addFilterButton);
    }

    applyCustomFilterButtonPressedHandler(customFilter) {

        this.customFilters = $.grep(this.customFilters, function(cf){
            return cf.id !== customFilter.id;
        });

        this.customFilters.push(customFilter);

        this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_ADDED, this.customFilters);

        this.activatePlugins();

    }

    activatePlugins() {
        debugger;

        this.$selectedProperties = $('#js-selected-properties').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        this.$selectedProperties.selectize()[0].selectize.off('item_remove');

        this.$selectedProperties.selectize()[0].selectize.on('item_remove', (key) => {
            debugger;
            this.customFilters.splice(key, 1);
            this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_REMOVED, this.customFilters);

        });

        this.$selectedProperties.selectize()[0].selectize.clear();
        this.$selectedProperties.selectize()[0].selectize.clearOptions();

        for(let i = 0; i < this.customFilters.length; i++) {
            debugger;
            let customFilter = this.customFilters[i];
            switch(customFilter['operator']) {
                case 'EQ':
                    let value = customFilter.value.trim() === '' ? '""' : `"${customFilter.value.trim()}"`;
                    this.$selectedProperties.selectize()[0].selectize.addOption({value:i, text: `${customFilter.label} contains exactly ${value}`});
                    this.$selectedProperties.selectize()[0].selectize.addItem(i);
                    break;
            }
        }

        this.$wrapper.find('.remove').attr('data-bypass', true);


        $('.item').click((e) => {
            debugger;
            let $element = $(e.target);
            // we don't want to trigger this when the exit button is clicked
            if($element.closest('a').length){
                return;
            }

            let index = $element.index();
            let customFilter = this.customFilters[index];

            this.globalEventDispatcher.publish(Settings.Events.EDIT_FILTER_BUTTON_CLICKED, customFilter);

        }) ;
    }

    render() {
        debugger;
        this.$wrapper.html(FilterNavigation.markup(this));
    }

    handleAddFilterButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.ADD_FILTER_BUTTON_CLICKED);
    }

    renderEditFilterForm(property) {
        debugger;

        this.$wrapper.find('.js-property-list').addClass('d-none');
        this.$wrapper.find('.js-search-container').addClass('d-none');

        switch (property.fieldType) {
            case 'single_line_text_field':
                new SingleLineTextFieldFilterForm(this.$wrapper.find('.js-property-form'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, property);
                break;

        }
    }

    static markup() {
        return `
    
        <div class="js-selectized-property-container">
            <input type="text" id="js-selected-properties">
        </div>
        <button type="button" class="btn btn-link js-add-filter-button"><i class="fa fa-plus"></i> Add Filter</button>

    `;
    }
}

export default FilterNavigation;