import axios from "axios";

const api = axios.create({
    baseURL: 'https://bitrix24.reutskiy-a.ru/api/',
    timeout: 600000,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json'}
})

export default api
