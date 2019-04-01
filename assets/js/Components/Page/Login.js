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


class Login {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
       /* this.showLogin = showLogin;
        this.showForgotPassword = showForgotPassword;*/

        this.render();

    }

    render() {

        this.$wrapper.html(Login.markup());

        $('#login-tab').tab('show');

        Pace.start({
            target: '.p-login__right'
        });

        new LoginForm(this.$wrapper.find('.js-login-form'), this.globalEventDispatcher);

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
                                <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="home" aria-selected="true">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-forgot-password-tab" id="forgot-password-tab" data-toggle="tab" href="#forgot-password" role="tab" aria-controls="profile" aria-selected="false">Password Reset</a>
                            </li>
                        </ul>
                    </div>
    
                    <div class="tab-content col-md-12" id="myTabContent">
                        <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                            <h3 class="p-login__heading">CRM Login</h3>
                            <div class="js-login-form"></div>
                        </div>
                    </div>
                   
                </div>     
                           
            </div>
            
        </div>
    </div>
    `;
    }

}

export default Login;