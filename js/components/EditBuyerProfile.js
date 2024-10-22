// EditBuyerProfile.js
import { sendRequest } from '../utils.js';

export default {
    props: {
        buyer: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            updatedName: this.buyer.name,
            updatedEmail: this.buyer.email,
            errors: {}
        };
    },
    methods: {
        saveChanges() {
            // Валидация полей
            if (!this.updatedName) {
                this.errors.name = 'Имя обязательно';
            } else {
                this.errors.name = '';
            }
            if (!this.updatedEmail) {
                this.errors.email = 'Email обязателен';
            } else {
                this.errors.email = '';
            }

            if (this.errors.name || this.errors.email) {
                return;
            }

            // Отправка обновленных данных на сервер
            sendRequest(`/ardozlock/updatebuyer/${this.buyer.id}/`, {
                name: this.updatedName,
                email: this.updatedEmail
            })
                .then(response => {
                    if (response.status === 'ok') {
                        alert('Профиль покупателя обновлен!');
                        this.$emit('profile-updated', {
                            name: this.updatedName,
                            email: this.updatedEmail
                        });
                    } else {
                        alert('Ошибка при обновлении профиля');
                    }
                })
                .catch(error => {
                    alert('Ошибка при отправке данных');
                    console.error(error);
                });
        }
    },
    template: `
        <div class="edit-buyer-profile">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" v-model="updatedName" />
                <span v-if="errors.name">[[ errors.name ]]</span>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" v-model="updatedEmail" />
                <span v-if="errors.email">[[ errors.email ]]</span>
            </div>
            <button @click="saveChanges" class="save-button">Сохранить изменения</button>
        </div>
    `
};
