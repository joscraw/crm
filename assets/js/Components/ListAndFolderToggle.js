'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";
import EditDefaultPropertiesModal from "./EditDefaultPropertiesModal";

class ListAndFolderToggle {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, showListFolderTable) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.showListFolderTable = showListFolderTable;

        this.render();
    }

    render() {
        this.$wrapper.html(ListAndFolderToggle.markup(this));

        this.setActiveButton();

    }

    setActiveButton() {

        if(this.showListFolderTable) {

            if(!this.$wrapper.find('.js-folders-button').hasClass('c-toggle__button--active')) {

                this.$wrapper.find('.js-folders-button').addClass('c-toggle__button--active');
            }

            if(this.$wrapper.find('.js-lists-button').hasClass('c-toggle__button--active')) {

                this.$wrapper.find('.js-lists-button').removeClass('c-toggle__button--active');
            }

        } else {

            if(this.$wrapper.find('.js-folders-button').hasClass('c-toggle__button--active')) {

                this.$wrapper.find('.js-folders-button').removeClass('c-toggle__button--active');
            }

            if(!this.$wrapper.find('.js-lists-button').hasClass('c-toggle__button--active')) {

                this.$wrapper.find('.js-lists-button').addClass('c-toggle__button--active');
            }
        }

    }

    static markup({portalInternalIdentifier}) {

        debugger;

        return `
        <div class="c-toggle">
            <a class="btn btn-light c-toggle__button c-toggle__button--left js-lists-button" href="${Routing.generate('list_settings', {internalIdentifier: portalInternalIdentifier})}" role="button">All lists</a>
            <a class="btn btn-light c-toggle__button c-toggle__button--right js-folders-button" href="${Routing.generate('list_settings', {internalIdentifier: portalInternalIdentifier})}/folders" role="button">folders</a>
        </div>
    `;
    }
}

export default ListAndFolderToggle;