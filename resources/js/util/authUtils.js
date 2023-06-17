import api from "../api.js";

export const logout = async (route, entity, signature) => {
    try {
        const response = await api.post(`api/${entity}/logout`, null, signature);
        sessionStorage.removeItem("user_data");
        route.push({ path: `/${entity}/login` });
    } catch (error) {
        alert(error);
        console.error(error);
    }
};
