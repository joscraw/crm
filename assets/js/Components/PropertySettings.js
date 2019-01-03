'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import PropertySettingsTopBar from './PropertySettingsTopBar';


class PropertySettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param children
     */
    constructor($wrapper, globalEventDispatcher, children = {}) {
        debugger;
        this.customObject = $wrapper.data('customObject');
        this.portal = $wrapper.data('portal');
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        children.propertySettings = this;
        this.children = children;

        this.render();
    }

    render() {
        const $topBar = this.$wrapper.find('.js-top-bar');
        new PropertySettingsTopBar($topBar, this.globalEventDispatcher, this.children);
    }

}

export default PropertySettings;