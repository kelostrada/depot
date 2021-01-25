import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

class Stocks extends React.Component {
    constructor(props) {
        super(props);
        this.state = {stocks: []};
    }

    componentDidMount() {
        const token = $('meta[name=token]')[0].content;

        axios.get('/api/stocks/all', {
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
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">Stocks</div>

                            <div className="card-body">
                                <table className="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Ref</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Invoice</th>
                                            <th scope="col">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        { this.state.stocks.map((item, index) => 
                                        <tr key={index}>
                                            <th scope="row">{item.id}</th>
                                            <td>{item.quantity}</td>
                                            <td>{item.rated_price.toFixed(2)}&nbsp;PLN</td>
                                            <td>{item.ref}</td>
                                            <td>{item.name}</td>
                                            <td>{item.invoice}</td>
                                            <td>{item.date}</td>
                                        </tr>
                                        ) }
                                    </tbody>
                                </table>
                                <ul>
                                
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Stocks;

if (document.getElementById('stocks')) {
    ReactDOM.render(<Stocks />, document.getElementById('stocks'));
}
