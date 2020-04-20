// assets/js/components/SecuredRoute.js

import React from 'react';
import {Route} from 'react-router-dom';
import auth0Client, {Auth}  from '../utils/Auth';

function SecuredRoute(props) {
    debugger;
    const {component: Component, path} = props;
    return (
        <Route path={path} render={() => {
            if (!auth0Client.isAuthenticated()) {
                return <div></div>;
            }
            return <Component />
        }} />
    );
}

export default SecuredRoute;