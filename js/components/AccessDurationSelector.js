import { sendRequest } from '../utils.js';

export default {
    delimiters: ['[[', ']]'],
    props: {
        buyerId: {
            type: String,
            required: true
        },
        accessDurationDays: {
            type: Number,
            default: 0
        },
        startDate: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            accessDuration: this.accessDurationDays,
            startAccessDate: this.formatDate(this.startDate),
            remainingDays: this.calculateRemainingDays() 
        };
    },
    watch: {
        accessDuration(newVal) {
            this.remainingDays = this.calculateRemainingDays();
        },
        startDate(newVal) {
            this.startAccessDate = this.formatDate(newVal);
            this.remainingDays = this.calculateRemainingDays();
        }
    },
    created() {
        this.remainingDays = this.calculateRemainingDays();
    },
    methods: {
        calculateRemainingDays() {
            if (!this.startDate || !this.accessDuration) {
                console.log('Ошибка: Отсутствует дата начала или срок доступа.');
                return null;
            }
    
            const startDate = new Date(this.startDate);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + parseInt(this.accessDuration, 10));
    
            const currentDate = new Date();
            const remainingTime = endDate - currentDate;
    
            console.log(`Дата начала: ${startDate}`);
            console.log(`Дата окончания: ${endDate}`);
            console.log(`Текущая дата: ${currentDate}`);
            console.log(`Оставшееся время (мс): ${remainingTime}`);
    
            if (remainingTime < 0) {
                console.log('Срок доступа истек.');
                return 0;  // Срок истек
            }
    
            const remainingDays = Math.ceil(remainingTime / (1000 * 60 * 60 * 24));
            console.log(`Оставшиеся дни: ${remainingDays}`);
            return remainingDays;  // Количество оставшихся дней
        },
        saveAccessDuration() {
            // Проверка валидности введенных данных
            if (!this.accessDuration || this.accessDuration <= 0) {
                alert('Введите корректное количество дней доступа.');
                return;
            }

            sendRequest(`/ardozlock/updatebuyeraccess/${this.buyerId}/`, {
                access_duration_days: this.accessDuration
            })
                .then(result => {
                    if (result.status === 'ok') {
                        alert('Срок доступа успешно обновлен!');
                    } else {
                        alert('Ошибка при обновлении срока доступа');
                    }
                })
                .catch(error => {
                    alert('Ошибка при сохранении срока доступа');
                });
        },

        formatDate(dateString) {
            if (!dateString) {
                return null;
            }
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        }
    },
    template: `
        <div class="access-duration-selector">
            <label>Срок доступа (дни):</label>
            <input type="number" v-model="accessDuration" @change="saveAccessDuration" />
            
            <div v-if="startAccessDate" class="access-start-date">
                <label>Дата начала доступа: [[ startAccessDate ]]</label>
            </div>

            <div v-if="remainingDays !== null" class="remaining-days">
                <label>Осталось дней до окончания: [[ remainingDays ]]</label>
            </div>
        </div>
    `
};
