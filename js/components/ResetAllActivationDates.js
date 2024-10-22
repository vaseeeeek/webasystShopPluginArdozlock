import { sendRequest } from '../utils.js';

export default {
    delimiters: ['[[', ']]'],
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        resetAllActivationDates() {
            if (confirm('Вы уверены, что хотите сбросить даты активации для всех пользователей?')) {
                this.isLoading = true;
                
                sendRequest('/ardozlock/resetallactivationdates/', {}, 'POST')
                    .then(response => {
                        if (response.status === 'ok') {
                            alert('Даты активации успешно сброшены для всех пользователей.');
                        } else {
                            alert('Ошибка при сбросе дат активации.');
                        }
                    })
                    .catch(error => {
                        alert('Ошибка при отправке запроса.');
                        console.error(error);
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            }
        }
    },
    template: `
        <div class="reset-activation-dates">
            <button @click="resetAllActivationDates" class="reset-all-button" :disabled="isLoading">
                Сбросить даты активации у всех пользователей
            </button>
        </div>
    `
};
