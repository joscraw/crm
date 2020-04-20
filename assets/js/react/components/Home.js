// assets/js/components/Home.js

import React, { Component } from 'react';
import NavBar from '../components/NavBar';
import { Route, Switch, Link, withRouter } from 'react-router-dom';
import Callback from "./Callback";
import SecuredRoute from "./SecuredRoute";
import PrivateResources from "./privateResources";
import PublicResources from "./publicResources";
import auth0Client from '../utils/Auth';

class Home extends Component {

    async componentDidMount() {
        debugger;
        if (this.props.location.pathname === '/callback') return;
        try {
            debugger;
            await auth0Client.silentAuth();
            debugger;
            this.forceUpdate();
        } catch (err) {
            debugger;
            if (err.error === 'login_required') return;
            console.log(err.error);
        }
    }

    render() {
        return (
            <div>
                <NavBar />
                <Switch>
                    <Route exact path={"/callback"} component={Callback} />
                    <SecuredRoute path={'/private'} component={PrivateResources}/>
                    <Route path={"/"} component={PublicResources} />
                </Switch>
            </div>
        )
    }
}

export default withRouter(Home);