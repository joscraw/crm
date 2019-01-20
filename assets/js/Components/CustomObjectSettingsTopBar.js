'use strict';

import $ from 'jquery';
import Settings from '../Settings';

import OpenCreateCustomObjectModalButton from './OpenCreateCustomObjectModalButton';


class CustomObjectSettingsTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, portal) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;

        this.render();
    }

    render() {
        this.$wrapper.html(CustomObjectSettingsTopBar.markup());

        new OpenCreateCustomObjectModalButton(this.$wrapper.find('.js-top-bar-button-container'), this.globalEventDispatcher, this.portal);
    }

        static markup() {
            return `
            <div class="row">
                <div class="col-md-4 offset-md-8 text-right js-top-bar-button-container"></div>
            </div>
        `;
        }
}

export default CustomObjectSettingsTopBar;