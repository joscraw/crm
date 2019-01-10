'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import RecordListTopBar from './RecordListTopBar';
import PropertyList from "./PropertyList";
import PropertyGroupFormModal from "./PropertyGroupFormModal";


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
        const $topBar = this.$wrapper.find('.js-top-bar');
        new RecordListTopBar($topBar, this.globalEventDispatcher, this.portal, this.customObject, this.customObjectLabel);

        /*const $div = $("<div>", {"class": "js-property-list"});
        this.$wrapper.find('.js-main-content').append($div);
        new PropertyList($div, this.globalEventDispatcher, this.portal, this.customObject);*/
    }

}

export default RecordList;