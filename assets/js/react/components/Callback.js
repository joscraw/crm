// assets/js/components/Callback.js

import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
import auth0Client from '../utils/Auth';


class Callback extends Component {
    async componentDidMount() {
        debugger;
        await auth0Client.handleAuthentication();
        this.props.history.replace('/');
    }

    render() {
        return (
            <p>Loading profile...</p>
        );
    }
}

export default withRouter(Callback);