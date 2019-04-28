'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import RecordListTopBar from './../RecordListTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import RecordTable from "../RecordTable";
import FilterWidget from "../FilterWidget";
import SideNavigationMenu from "../SideNavigationMenu";
import ReportSettingsTopBar from "../ReportSettingsTopBar";
import CustomObjectList from "../CustomObjectList";
import ReportList from "../ReportList";
import ListSettingsTopBar from "../ListSettingsTopBar";
import ListTable from "../ListTable";
import ListAndFolderToggle from "../ListAndFolderToggle";
import ListFolderTable from "../ListFolderTable";
import ListSettingsSubBar from "../ListSettingsSubBar";

class ListSettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param folderId
     * @param showListFolderTable
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, folderId = null, showListFolderTable = false) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.folderId = folderId;
        this.showListFolderTable = showListFolderTable;

        this.render();
    }

    render() {

        debugger;

        this.$wrapper.html(ListSettings.markup(this));

        new ListSettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier);

        new ListSettingsSubBar(this.$wrapper.find('.js-sub-bar'), this.globalEventDispatcher, this.portalInternalIdentifier, this.showListFolderTable, this.folderId);

        if(this.showListFolderTable) {

            new ListFolderTable(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portalInternalIdentifier, this.folderId);

        } else {

            new ListTable(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portalInternalIdentifier);
        }

    }

    static markup() {

        return `
      <div class="js-list-settings-page p-list">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar clearfix"></div>
            <br>
            <div class="l-grid__main-content js-main-content" style="min-height: 700px; position: relative"></div> 
        </div>
      </div>
    `;
    }

}

export default ListSettings;