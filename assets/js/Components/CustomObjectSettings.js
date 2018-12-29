'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import CustomObjectFormModal from './CustomObjectFormModal';
import CustomObjectList from './CustomObjectList';


class CustomObjectSettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.init($wrapper, globalEventDispatcher);
    }

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    init($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'click',
            '.js-custom-object-settings-container__create-custom-object-btn',
            this.handleCreateObjectBtnClick.bind(this)
        );

        this.render();
    }

    handleCreateObjectBtnClick() {
        console.log("Create Custom Object Button Clicked");
        this.globalEventDispatcher.publish(Settings.Events.CREATE_CUSTOM_OBJECT_BUTTON_CLICKED);
        console.log(`Event Dispatched: ${Settings.Events.CREATE_CUSTOM_OBJECT_BUTTON_CLICKED}`);
        new CustomObjectFormModal(this.globalEventDispatcher);
    }

    render() {
        this.$wrapper.html(CustomObjectSettings.markup(this));
        new CustomObjectList(this.$wrapper.find('.js-custom-object-settings-container__list'), this.globalEventDispatcher);
    }

    static markup() {
        return `
        <div class="js-top-bar custom-object-settings-container__top-bar">
            <div class="row">
                <div class="col-lg-10">
                    <h1>Test h1</h1>
                </div>
                <div class="col-lg-2">
                    <button class="js-custom-object-settings-container__create-custom-object-btn
                    custom-object-settings-container__create-custom-object-btn btn btn-secondary">Create Object</button>
                </div>
            </div>
        </div>
        <div class="custom-object-settings-container__main-content">
        <div class="js-custom-object-settings-container__list custom-object-settings-container__list"></div>
        </div>
    `;
    }
}

export default CustomObjectSettings;