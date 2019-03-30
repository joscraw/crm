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


class ForgotPassword {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {

        this.$wrapper.html(ForgotPassword.markup());

        $('#forgot-password-tab').tab('show');

        Pace.start({
            target: '.p-login__right'
        });

        new ForgotPasswordForm(this.$wrapper.find('.js-forgot-password-form'), this.globalEventDispatcher);

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
                
                    <div class="col-md-12">
                        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="home" aria-selected="true">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="forgot-password-tab" data-toggle="tab" href="#forgot-password" role="tab" aria-controls="profile" aria-selected="false">Password Reset</a>
                            </li>
                        </ul>
                    </div>
    
                    <div class="tab-content col-md-12" id="myTabContent">
                        <div class="tab-pane fade show active" id="forgot-password" role="tabpanel" aria-labelledby="forgot-password-tab">
                            <h3 class="p-login__heading">Password Reset</h3>
                            <div class="js-forgot-password-form"></div>
                        </div>
                    </div>
                   
                </div>     
                           
            </div>
            
        </div>
    </div>
    `;
    }

}

export default ForgotPassword;