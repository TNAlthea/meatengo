import api from "../api.js";
import store from "./store.js";

export const logout = async (entity, signature, route) => {
    try {
        const response = await api.post(
            `api/${entity}/logout`,
            null,
            signature
        );
        sessionStorage.removeItem("user_data");
        console.log(store.getters.getLoginTime.toString());
        store.dispatch("clearTokenExpirationTimer");
        if (entity !== "user") route.push({ path: `/${entity}/login` });
        else route.push({ path: `/login` });
    } catch (error) {
        alert(error);
        console.error(error);
    }
};
