import $ from 'jquery';
import CustomObjectSettings from './Components/CustomObjectSettings';

$(document).ready(function() {
    new CustomObjectSettings($('.js-custom-object-settings'), window.globalEventDispatcher);
});