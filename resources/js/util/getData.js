import api from "../api.js";

export const getAllInventory = async (signature) => {
    try {
        const response = await api.get("api/inventory/getAll", signature);

        return response.data.data;
    } catch (error) {
        alert(`Error terjadi ketika memuat inventori produk, alasan: ${error}`);
        console.error(error);
    }
};

export const getAllMember = async (entity, signature) => {
    try {
        const response = await api.get(`api/user_management/get_as_${entity}`, signature);

        return response.data.data;
    } catch (error) {
        alert(`Error terjadi ketika memuat daftar member, alasan: ${error}`);
        console.error(error);
    }
};
