'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import PropertySettingsTopBar from './PropertySettingsTopBar';
import PropertyList from "./PropertyList";
import PropertyGroupFormModal from "./PropertyGroupFormModal";


class PropertySettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        debugger;
        this.customObject = $wrapper.data('customObject');
        this.portal = $wrapper.data('portal');
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {
        const $topBar = this.$wrapper.find('.js-top-bar');
        new PropertySettingsTopBar($topBar, this.globalEventDispatcher, this.portal, this.customObject);

        const $div = $("<div>", {"class": "js-property-list"});
        this.$wrapper.find('.js-main-content').append($div);
        new PropertyList($div, this.globalEventDispatcher, this.portal, this.customObject);
    }

}

export default PropertySettings;