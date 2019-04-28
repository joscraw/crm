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

        if(this.showListFolderTable) {

            new ListFolderBreadcrumbs(this.$wrapper.find('.js-folder-breadcrumbs'), this.globalEventDispatcher, this.portalInternalIdentifier, this.showListFolderTable, this.folderId);

        }
    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-12 js-list-and-folder-toggle-container"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12 js-folder-breadcrumbs"></div>
        </div>
    `;
    }
}

export default ListSettingsSubBar;