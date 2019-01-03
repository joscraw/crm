'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import OpenCreatePropertyGroupModalButton from './OpenCreatePropertyGroupModalButton';
import OpenPropertyCreateModalButton from './OpenPropertyCreateModalButton';


class PropertySettingsTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param children
     */
    constructor($wrapper, globalEventDispatcher, children = {}) {
        debugger;
        children.propertySettingsTopBar = this;
        this.children = children;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {
        this.$wrapper.html(PropertySettingsTopBar.markup());
        new OpenCreatePropertyGroupModalButton(this.$wrapper.find('.js-top-bar-button-container'), this.globalEventDispatcher, this.children);
        new OpenPropertyCreateModalButton(this.$wrapper.find('.js-top-bar-button-container'), this.globalEventDispatcher, this.children);

    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 offset-md-6 text-right js-top-bar-button-container"></div>
        </div>
    `;
    }
}

export default PropertySettingsTopBar;