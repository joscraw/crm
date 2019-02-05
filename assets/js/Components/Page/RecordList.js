'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import RecordListTopBar from './../RecordListTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import RecordTable from "../RecordTable";
import FilterWidget from "../FilterWidget";

class RecordList {

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
        this.$wrapper.html(RecordList.markup(this));
        new RecordListTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new RecordTable(this.$wrapper.find('.js-record-table'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
        new FilterWidget(this.$wrapper.find('.js-record-filter-widget'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);
    }

    static markup() {

        return `
      <div class="js-record-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content">
                <div class="row">
                    <div class="col-md-3 js-record-filter-widget"></div>
                    <div class="col-md-9 js-record-table"></div>
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

export default RecordList;