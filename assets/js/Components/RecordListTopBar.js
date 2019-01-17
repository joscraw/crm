'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import CreateRecordButton from './CreateRecordButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import DatatableSearch from "./DatatableSearch";


class RecordListTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObject
     * @param customObjectLabel
     */
    constructor($wrapper, globalEventDispatcher, portal, customObject, customObjectLabel) {
        debugger;
        this.portal = portal;
        this.customObject = customObject;
        this.customObjectLabel = customObjectLabel;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    handleKeyupEvent(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();
        const searchObject = {
          searchValue: searchValue
        };

        this.globalEventDispatcher.publish(Settings.Events.PROPERTY_SETTINGS_TOP_BAR_SEARCH_KEY_UP, searchObject);
    }

    render() {

        this.$wrapper.html(RecordListTopBar.markup());
        new CreateRecordButton(this.$wrapper.find('.js-top-bar-button-container'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);
        new DatatableSearch(this.$wrapper.find('.js-top-bar-search-container'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel, "Search for a record...")
    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 js-top-bar-search-container"></div>
        <div class="col-md-6 text-right js-top-bar-button-container"></div>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-md-12 js-custom-object-navigation"></div>
        </div>
    `;
    }
}

export default RecordListTopBar;