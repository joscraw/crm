'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import CreateRecordButton from './CreateRecordButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import DatatableSearch from "./DatatableSearch";
import Dropdown from "./Dropdown";



class RecordListTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.render();
    }

    render() {

        this.$wrapper.html(RecordListTopBar.markup());
        new Dropdown(this.$wrapper.find('.js-dropdown'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, 'Actions');
        new CreateRecordButton(this.$wrapper.find('.js-create-record-button'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new DatatableSearch(this.$wrapper.find('.js-top-bar-search-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, "Search for a record...")
    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 js-top-bar-search-container"></div>
        <div class="col-md-6 text-right js-top-bar-button-container">
            <div class="js-dropdown d-inline-block"></div>
            <div class="js-create-record-button d-inline-block"></div>     
        </div>
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