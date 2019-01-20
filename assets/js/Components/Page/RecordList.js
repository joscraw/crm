'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import RecordListTopBar from './../RecordListTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import RecordTable from "../RecordTable";

class RecordList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        debugger;
        this.customObject = $wrapper.data('customObject');
        this.portal = $wrapper.data('portal');
        this.customObjectLabel = $wrapper.data('customObjectLabel');
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {
        this.$wrapper.html(RecordList.markup(this));
        new RecordListTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);
        new RecordTable(this.$wrapper.find('.js-main-content'), this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);
    }

    static markup() {

        return `
      <div class="js-record-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content"></div>
        </div>
      </div>
    `;
    }

}

export default RecordList;