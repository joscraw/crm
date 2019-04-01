'use strict';

import $ from 'jquery';
import Settings from '../../Settings';
import PropertySettingsTopBar from './../PropertySettingsTopBar';
import PropertyList from "./../PropertyList";
import PropertyGroupFormModal from "./../PropertyGroupFormModal";
import CustomObjectSettingsTopBar from "../CustomObjectSettingsTopBar";
import CustomObjectList from "../CustomObjectList";
import UserSettingsTopBar from "../UserSettingsTopBar";
import UserList from "../UserList";
import LoginForm from "../LoginForm";
import ForgotPasswordForm from "../ForgotPasswordForm";
import ResetPasswordForm from "../ResetPasswordForm";
import InvalidOrExpiredPasswordResetRequest from "../InvalidOrExpiredPasswordResetRequest";
import PasswordSuccessfullyReset from "../PasswordSuccessfullyReset";


class ResetPassword {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param token
     */
    constructor($wrapper, globalEventDispatcher, token) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.token = token;

        this.globalEventDispatcher.subscribe(
            Settings.Events.INVALID_OR_EXPIRED_PASSWORD_RESET_REQUEST,
            this.handleInvalidOrExpiredPasswordResetRequest.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PASSWORD_SUCCESSFULLY_RESET,
            this.handlePasswordSuccessfullyReset.bind(this)
        );

        this.render();

    }

    render() {

        this.$wrapper.html(ResetPassword.markup());

        new ResetPasswordForm(this.$wrapper.find('.js-reset-password-form'), this.globalEventDispatcher, this.token);

    }

    handleInvalidOrExpiredPasswordResetRequest() {

        new InvalidOrExpiredPasswordResetRequest(this.$wrapper.find('.js-reset-password-form'), this.globalEventDispatcher);

    }

    handlePasswordSuccessfullyReset() {

        new PasswordSuccessfullyReset(this.$wrapper.find('.js-reset-password-form'), this.globalEventDispatcher);

    }

    static markup() {

        return `
    <div class="register p-login__container">
        <div class="row">
            <div class="col-md-3 p-login__left">
                <img src="https://image.ibb.co/n7oTvU/logo_white.png" alt=""/>
                <h3>Velkommen</h3>
                <p>You are 30 seconds away from an avant-garde, groovy, modernistic, off the wall experience!</p>
            </div>
            
            <div class="col-md-9 p-login__right">
            
                <div class="row">
                
                    <div class="tab-content col-md-12" id="myTabContent">
                        <div class="js-reset-password-form"></div>
                    </div>
                   
                </div>     
                           
            </div>
            
        </div>
    </div>
    `;
    }

}

export default ResetPassword;