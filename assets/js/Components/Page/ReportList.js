'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import RecordListTopBar from './../RecordListTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import RecordTable from "../RecordTable";
import FilterWidget from "../FilterWidget";
import ReportListTopBar from "../ReportListTopBar";
import SideNavigationMenu from "../SideNavigationMenu";

class ReportList {

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
        this.$wrapper.html(ReportList.markup(this));

        new ReportListTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier);


/*        new RecordListTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new RecordTable(this.$wrapper.find('.js-record-table'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new FilterWidget(this.$wrapper.find('.js-record-filter-widget'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);*/
    }

    static markup() {

        return `
      <div class="js-report-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content">
                <div class="row">
                    <div class="col-md-3 js-report-filter-widget"></div>
                    <div class="col-md-9 js-report-table"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                </div>            
            </div> 
        </div>
      </div>
    `;
    }

}

export default ReportList;