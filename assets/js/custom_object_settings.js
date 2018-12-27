import $ from 'jquery';
import CreateCustomObjectButton from './Components/CreateCustomObjectButton';

$(document).ready(function() {
    debugger;
    /*new CreateCustomObjectForm($('.js-top-bar'));*/
    new CreateCustomObjectButton($('.js-create-custom-object-button-container'), window.globalEventDispatcher);
});