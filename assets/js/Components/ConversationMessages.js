'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import Routing from "../Routing";
import ObjectHelper from "../ObjectHelper";


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
        this.loadMessages().then(data => {
            debugger;
            this.renderMessages(data.data);
        })
    }

    render() {
        this.$wrapper.html(ConversationMessages.markup(this));
    }
    renderMessages(threads) {
        debugger;
        for(let threadId in threads) {
            if(threads.hasOwnProperty(threadId)) {
                let messages = threads[threadId];
                for(let message of messages) {
                    const html = messageTemplate(message);
                    const $message = $($.parseHTML(html));
                    this.$wrapper.find('.js-messages').append($message);
                }
                debugger;
            }
        }
        debugger;
    }

    loadMessages() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('gmail_thread_list', {internalIdentifier: this.portalInternalIdentifier});
            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    loadMessage(messageId) {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('google_get_message', {internalIdentifier: this.portalInternalIdentifier, messageId: messageId});
            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    static markup() {

        return `
      <div class="js-record-list-page">
        <div class="l-grid">
            <div class="l-grid__top-bar js-top-bar"></div>
            <div class="l-grid__sub-bar js-sub-bar"></div>
            <div class="l-grid__main-content js-main-content">
                <div class="row">
                    <div class="col-md-12 js-messages" style="min-height: 700px"></div>
                </div>            
            </div> 
        </div>
      </div>
    `;
    }

}

/**
 * @return {string}
 */
const threadTemplate = ({threadId}) => `
       <div data-thread="${threadId}" class="js-thread"></div>
`;

/**
 * @return {string}
 */
const messageTemplate = ({to, from, subject, text}) => `
       <div>${from} ${to}</div>
       <div>${subject}</div>
       <div>${text}</div>
       <div class="js-attachments"></div>
`;

export default ConversationMessages;