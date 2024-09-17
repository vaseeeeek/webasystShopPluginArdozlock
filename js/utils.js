// utils.js
export function sendRequest(url, data, method = 'POST', headers = {}) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            ...headers,
        },
        body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'ok') {
            return result;
        } else {
            console.error('Ошибка:', result.error || 'Неизвестная ошибка');
            throw new Error(result.error || 'Неизвестная ошибка');
        }
    })
    .catch(error => {
        console.error('Ошибка при отправке данных:', error);
        throw error;
    });
}
