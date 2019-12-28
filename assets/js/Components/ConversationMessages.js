'use strict';

import $ from 'jquery';
import Settings from '../Settings';


class ConversationMessages {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }

    render() {
        this.$wrapper.html(ConversationMessages.markup());

/*        new RecordEditForm($('.js-forms-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.recordId);
        new EditRecordTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.recordId);
        new EditDefaultPropertiesWidget(this.$wrapper.find('.js-edit-default-properties-widget'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);*/
    }

    static markup() {

        return `
      <div class="js-record-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content">
                <div class="row">
                    <div class="col-md-4 js-edit-default-properties-widget"></div>
                    <div class="col-md-8 js-forms-container">Messages</div>
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

export default ConversationMessages;