import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

class Example extends React.Component {
    constructor(props) {
        super(props);
        this.state = {stocks: []};
    }

    componentDidMount() {
        const token = $('meta[name=token]')[0].content;

        axios.get('/api/stocks', {
            headers: {'Authorization': `Bearer ${token}`}
        }).then(response => {
            const stocks = response.data;
            this.setState({ stocks });
        });
    }

    render() {
        return (
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-md-8">
                        <div className="card">
                            <div className="card-header">Example Component</div>

                            <div className="card-body">
                                <ul>
                                { this.state.stocks.map((item, index) => <li key={index}>{item.quantity}x {item.product.name}, Invoice: {item.invoice.name}</li>) }
                                </ul>
                            </div>
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
