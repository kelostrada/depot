import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

class Example extends React.Component {
    constructor(props) {
        super(props);
        this.state = {user: {}};
    }

    componentDidMount() {
        const token = $('meta[name=token]')[0].content;

        axios.get('/api/user', {
            headers: {'Authorization': `Bearer ${token}`}
        }).then(response => {
            const user = response.data;
            this.setState({ user });
        });
    }

    render() {
        return (
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-md-8">
                        <div className="card">
                            <div className="card-header">Example Component</div>

                            <div className="card-body">{ this.state.user.id } { this.state.user.name }</div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Example;

if (document.getElementById('example')) {
    ReactDOM.render(<Example />, document.getElementById('example'));
}
