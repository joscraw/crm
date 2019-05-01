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
import ListAndFolderToggle from "./ListAndFolderToggle";
import ListFolderBreadcrumbs from "./ListFolderBreadcrumbs";
import CreateFolderButton from "./CreateFolderButton";
import ListCount from "./ListCount";


class ListSettingsSubBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param showListFolderTable
     * @param folderId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, showListFolderTable, folderId) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.showListFolderTable = showListFolderTable;
        this.folderId = folderId;

        this.render();
    }

    render() {

        this.$wrapper.html(ListSettingsSubBar.markup());

        new ListAndFolderToggle(this.$wrapper.find('.js-list-and-folder-toggle-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.showListFolderTable);

        new ListCount(this.$wrapper.find('.js-list-count'), this.globalEventDispatcher, this.portalInternalIdentifier);


        if(this.showListFolderTable) {

            new ListFolderBreadcrumbs(this.$wrapper.find('.js-folder-breadcrumbs'), this.globalEventDispatcher, this.portalInternalIdentifier, this.showListFolderTable, this.folderId);

            new CreateFolderButton(this.$wrapper.find('.js-create-folder-button'), this.globalEventDispatcher, this.portalInternalIdentifier, this.folderId);
        }
    }

    static markup() {
        return `
        <h1>Lists <span class="js-list-count" style="font-size: 2.5rem"></span></h1>
        <hr>
        <div class="row">
            <div class="col-md-6 js-list-and-folder-toggle-container"></div>
            <div class="col-md-6 text-right js-top-bar-button-container">
                <div class="js-create-folder-button d-inline-block"></div>     
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12 js-folder-breadcrumbs"></div>
        </div>
    `;
    }
}

export default ListSettingsSubBar;