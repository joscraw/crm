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
import FormSettingsTopBar from "../FormSettingsTopBar";
import FormList from "../FormList";
import WorkflowSettingsTopBar from "../WorkflowSettingsTopBar";

class WorkflowSettings {

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
        this.$wrapper.html(WorkflowSettings.markup(this));

        new WorkflowSettingsTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier);

        new FormList(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portalInternalIdentifier);

    }

    static markup() {

        return `
      <div class="js-form-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content" style="min-height: 700px; position: relative"></div> 
        </div>
      </div>
    `;
    }

}

export default WorkflowSettings;