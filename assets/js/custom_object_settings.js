import $ from 'jquery';
import CustomObjectSettings from './Components/Page/CustomObjectSettings';
import SideNavigationMenu from "./Components/SideNavigationMenu";

$(document).ready(function() {
    new CustomObjectSettings($('#app'), window.globalEventDispatcher);
    new SideNavigationMenu($('#side-nav'), window.globalEventDispatcher, 9874561920);
});