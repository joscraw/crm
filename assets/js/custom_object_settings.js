import $ from 'jquery';
import CustomObjectSettings from './Components/Page/CustomObjectSettings';

$(document).ready(function() {
    new CustomObjectSettings($('#app'), window.globalEventDispatcher);
});