'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import swal from 'sweetalert2';
import ReportProperties from "./ReportProperties";
import ListPropertyList from "./ListPropertyList";
import ListPreviewResultsTable from "./ListPreviewResultsTable";
import ReportConnectObjectButton from "./ReportConnectObjectButton";
import ReportAllFiltersButton from "./ReportAllFiltersButton";
import ReportConnectedObjectsList from "./ReportConnectedObjectsList";

class ListProperties {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, data) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.data = data;
        this.unbindEvents();
        this.bindEvents();
        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            listTablePreview: '.js-list-table-preview',
            listBackToSelectCustomObjectButton: '.js-back-to-select-custom-object-button',
            listConnectableObjectsContainer: '.js-list-connectable-objects',
            listAllFiltersButtonContainer: '.js-list-all-filters-button-container',
            listConnectedObjectsListContainer: '.js-list-connected-objects-list-container',
            saveListButton: '.js-save-list-button',
            listName:  '.js-list-name',
            listPropertyListContainer: '.js-list-property-list-container'
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'click',
            ListProperties._selectors.listBackToSelectCustomObjectButton,
            this.handleListBackToSelectCustomObjectButton.bind(this)
        );

        this.$wrapper.on(
            'click',
            ListProperties._selectors.saveListButton,
            this.handleSaveListButtonClicked.bind(this)
        );

        this.$wrapper.on('change', ListProperties._selectors.listName, this.handleListNameChange.bind(this));

    }

    unbindEvents() {
        this.$wrapper.off('click', ListProperties._selectors.listBackToSelectCustomObjectButton);
        this.$wrapper.off('click', ListProperties._selectors.saveListButton);
        this.$wrapper.off('change', ListProperties._selectors.listName);
    }

    handleListNameChange(e) {
        this.globalEventDispatcher.publish(Settings.Events.REPORT_NAME_CHANGED, $(e.target).val());
    }

    handleSaveListButtonClicked(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        let reportName = this.$wrapper.find(ReportProperties._selectors.reportName).val();
        if(reportName === '') {
            swal("Woahhh snap!!!", "Don't forget a name for your list.", "warning");
            return;
        }
        this.globalEventDispatcher.publish(Settings.Events.REPORT_SAVE_BUTTON_PRESSED, reportName);
    }


    handleListBackToSelectCustomObjectButton(e) {
        debugger;
        this.globalEventDispatcher.publish(Settings.Events.LIST_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED, this.data);
    }

    render() {
        debugger;
        this.$wrapper.html(ListProperties.markup(this));
        new ListPreviewResultsTable($(ListProperties._selectors.listTablePreview), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);
        new ListPropertyList($(ListProperties._selectors.listPropertyListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data);
        new ReportConnectObjectButton($(ListProperties._selectors.listConnectableObjectsContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data.selectedCustomObject.internalName);
        new ReportAllFiltersButton($(ListProperties._selectors.listAllFiltersButtonContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data.selectedCustomObject.internalName, this.data);
        new ReportConnectedObjectsList($(ListProperties._selectors.listConnectedObjectsListContainer), this.globalEventDispatcher, this.portalInternalIdentifier, this.data.selectedCustomObject.internalName, this.data);
    }

    static markup({data: {listName, selectedCustomObject: {internalName}}}) {
        return `

            <nav class="navbar navbar-expand-sm l-top-bar justify-content-end c-report-widget__nav">
                 <div class="container-fluid">
                    <div class="navbar-collapse collapse dual-nav w-50 order-1 order-md-0">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <button type="button" style="color: #FFF" class="btn btn-link js-back-to-select-custom-object-button"><i class="fa fa-angle-left" aria-hidden="true"></i> Back</button>
                            </li>
                        </ul>
                    </div>
                    <input style="width: 200px;" value="${listName}" class="form-control navbar-brand mx-auto d-block text-center order-0 order-md-1 w-25 c-report-widget__report-name js-list-name" type="text" placeholder="List name">
                    <div class="navbar-collapse collapse dual-nav w-50 order-2">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                            <button class="btn btn-lg btn-secondary ml-auto js-save-list-button c-report-widget__report-save">Save</button>
                            </li>
                        </ul>
                    </div>
                </div>               
            </nav> 
            <div class="row container">
                <div class="col-md-4" style="height: 600px;  overflow-y: auto">
                <h2 style="text-decoration: underline">List for ${internalName}</h2>
                    <div class="col-md-12 js-list-property-list-container"></div>
                    <div class="col-md-12 js-list-connectable-objects"></div>
                    <div class="col-md-12 js-list-all-filters-button-container"></div>
                    <div class="col-md-12 js-list-connected-objects-list-container"></div>
                </div>
                <div class="col-md-8">
                    <div class="js-list-table-preview c-column-editor__selected-columns-count"></div>
                </div>  
            </div>
    `;
    }
}

export default ListProperties;