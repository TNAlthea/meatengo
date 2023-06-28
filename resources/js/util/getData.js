import api from "../api.js";
import store from "./store.js";

export const getAllInventory = async (entity, signature, route) => {
    const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login
    if (Date.now() < tokenExpiry) {
        try {
            const response = await api.get("api/inventory/getAll", signature);

            return response.data.data;
        } catch (error) {
            if (error.response) {
                const errorMessage = error.response.data;
                alert(
                    `Error terjadi ketika memuat inventori. ${errorMessage.message}, alasan: ${errorMessage.reason}`
                );
            } else {
                alert(error);
            }
        }
    } else {
        alert(`token expired, please login again.`);
        sessionStorage.removeItem("user_data");
        store.dispatch("clearTokenExpirationTimer");
        route.push(`/${entity}/login`);
    }
};

export const getAllMember = async (entity, signature, route) => {
    const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login
    if (Date.now() < tokenExpiry) {
        try {
            const response = await api.get(
                `api/user_management/get_as_${entity}`,
                signature
            );

            return response.data.data;
        } catch (error) {
            if (error.response) {
                const errorMessage = error.response.data;
                alert(
                    `Error terjadi ketika memuat daftar member. ${errorMessage.message}, alasan: ${errorMessage.reason}`
                );
            } else {
                alert(error);
            }
        }
    } else {
        alert(`token expired, please login again.`);
        sessionStorage.removeItem("user_data");
        store.dispatch("clearTokenExpirationTimer");
        route.push(`/${entity}/login`);
    }
};

export const getAllTransaction = async (entity, signature, route, page) => {
    const tokenExpiry = store.getters.getLoginTime + 6 * 60 * 60 * 1000; //expiration time of token is 6 hours from login
    if (Date.now() < tokenExpiry) {
        try {
            const response = await api.get(
                `api/transaction/get_all_transaction_history?page=${page}`,
                signature
            );
            const rawTransactionList = response.data.data.data;
            console.log(rawTransactionList);
            const concatenatedProductList = {};

            rawTransactionList.forEach((transaction) => {
                if (
                    !concatenatedProductList.hasOwnProperty(
                        transaction.transaction_id
                    )
                ) {
                    concatenatedProductList[transaction.transaction_id] = [];
                }
                concatenatedProductList[transaction.transaction_id].push({
                    product: transaction.product_name,
                    quantity: transaction.quantity,
                });
            });

            const transactionList = Object.keys(concatenatedProductList).map(
                (id_from_concat) => {
                    const transactionData = rawTransactionList.find(
                        ({ transaction_id }) => transaction_id == id_from_concat
                    );

                    return {
                        id: id_from_concat,
                        sold_at: transactionData.sold_at,
                        total_before_discount:
                            transactionData.total_before_discount,
                        discount: transactionData.discount,
                        total: transactionData.total,
                        plastic_bag: transactionData.plastic_bag,
                        payment_method: transactionData.payment_method,
                        customer_name: transactionData.customer_name,
                        cashier_name: transactionData.cashier_name,
                        products: concatenatedProductList[id_from_concat],
                    };
                }
            );

            return transactionList;
        } catch (error) {
            if (error.response) {
                const errorMessage = error.response.data;
                alert(
                    `Error terjadi ketika memuat daftar histori transaksi. ${errorMessage.message}, alasan: ${errorMessage.reason}`
                );
            } else {
                alert(error);
            }
        }
    } else {
        alert(`token expired, please login again.`);
        sessionStorage.removeItem("user_data");
        store.dispatch("clearTokenExpirationTimer");
        route.push(`/${entity}/login`);
    }
};
