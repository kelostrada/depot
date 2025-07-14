import React from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import { FaSort, FaSortUp, FaSortDown } from "react-icons/fa";

class Stocks extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            stocks: [],
            sortField: null,
            sortDirection: "asc",
        };
    }

    componentDidMount() {
        this.fetchStocks();
    }

    fetchStocks = () => {
        const token = $("meta[name=token]")[0].content;
        const { sortField, sortDirection } = this.state;

        axios
            .get("api/stocks/all", {
                headers: { Authorization: `Bearer ${token}` },
                params: {
                    sort_by: sortField,
                    sort_direction: sortDirection,
                },
            })
            .then((response) => {
                const stocks = response.data;
                this.setState({ stocks });
            });
    };

    handleSort = (field) => {
        this.setState((prevState) => {
            let direction = "asc";
            if (prevState.sortField === field) {
                direction = prevState.sortDirection === "asc" ? "desc" : "asc";
            }
            return {
                sortField: field,
                sortDirection: direction,
            };
        }, this.fetchStocks);
    };

    getSortIcon = (field) => {
        const { sortField, sortDirection } = this.state;
        if (sortField !== field) {
            return <FaSort />;
        }
        return sortDirection === "asc" ? <FaSortUp /> : <FaSortDown />;
    };

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
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort("id")
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                # {this.getSortIcon("id")}
                                            </th>
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort("quantity")
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                Quantity{" "}
                                                {this.getSortIcon("quantity")}
                                            </th>
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort(
                                                        "rated_price"
                                                    )
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                Price{" "}
                                                {this.getSortIcon(
                                                    "rated_price"
                                                )}
                                            </th>
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort("ref")
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                Ref {this.getSortIcon("ref")}
                                            </th>
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort("name")
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                Name {this.getSortIcon("name")}
                                            </th>
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort("invoice")
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                Invoice{" "}
                                                {this.getSortIcon("invoice")}
                                            </th>
                                            <th
                                                scope="col"
                                                onClick={() =>
                                                    this.handleSort("date")
                                                }
                                                style={{ cursor: "pointer" }}
                                            >
                                                Date {this.getSortIcon("date")}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {this.state.stocks.map(
                                            (item, index) => (
                                                <tr key={index}>
                                                    <th scope="row">
                                                        {item.id}
                                                    </th>
                                                    <td>{item.quantity}</td>
                                                    <td>
                                                        {item.rated_price.toFixed(
                                                            2
                                                        )}
                                                        &nbsp;PLN
                                                    </td>
                                                    <td>{item.ref}</td>
                                                    <td>{item.name}</td>
                                                    <td>{item.invoice}</td>
                                                    <td>{item.date}</td>
                                                </tr>
                                            )
                                        )}
                                    </tbody>
                                </table>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Stocks;

if (document.getElementById("stocks")) {
    ReactDOM.render(<Stocks />, document.getElementById("stocks"));
}
