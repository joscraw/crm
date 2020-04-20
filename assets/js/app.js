// assets/js/app.js

import React, { Component } from 'react';
import ReactDom from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import Home from "./react/components/Home";

class App extends Component {
    render() {
        return (
            <BrowserRouter>
                <div>
                    <Home/>
                </div>
            </BrowserRouter>
        )
    }
}

ReactDom.render(<App />, document.getElementById('root'));
