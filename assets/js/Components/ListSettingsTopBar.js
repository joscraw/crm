'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import CreateRecordButton from './CreateRecordButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import DatatableSearch from "./DatatableSearch";
import Dropdown from "./Dropdown";
import CreateReportButton from "./CreateReportButton";
import CustomObjectSearch from "./CustomObjectSearch";
import ReportSearch from "./ReportSearch";
import CreateListButton from "./CreateListButton";
import ListSearch from "./ListSearch";


class ListSettingsTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }

    render() {

        this.$wrapper.html(ListSettingsTopBar.markup());

        new ListSearch(this.$wrapper.find('.js-top-bar-search-container'), this.globalEventDispatcher, this.portalInternalIdentifier, "Search for a list");


        new CreateListButton(this.$wrapper.find('.js-create-list-button'), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 js-top-bar-search-container"></div>
        <div class="col-md-6 text-right js-top-bar-button-container">
            <div class="js-dropdown d-inline-block"></div>
            <div class="js-create-list-button d-inline-block"></div>     
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

export default ListSettingsTopBar;