import { sendRequest } from '../utils.js';
import BlockedPagesSelector from './BlockedPagesSelector.js';

export default {
    delimiters: ['[[', ']]'],
    components: {
        'blocked-pages-selector': BlockedPagesSelector
    },
    data() {
        return {
            searchQuery: '',
            buyers: [], // Список покупателей, загружаемый с сервера
            showCreateBuyerModal: false,
            newBuyer: { name: '', email: '' },
            errors: {},
        };
    },
    created() {
        this.fetchBuyers();  // Загрузка списка покупателей при загрузке компонента
    },
    computed: {
        // Фильтрация списка покупателей по введённому поисковому запросу
        filteredBuyers() {
            const query = this.searchQuery.toLowerCase();
            if (!query) {
                return this.buyers;
            }
            return this.buyers.filter(buyer => buyer.name.toLowerCase().includes(query));
        }
    },
    methods: {
        // Получение списка покупателей с сервера
        fetchBuyers() {
            sendRequest('/ardozlock/getbuyers/', {})
                .then(result => {
                    this.buyers = result.data.buyers; // Получаем актуальный список покупателей
                })
                .catch(error => {
                    console.error('Ошибка при загрузке покупателей:', error);
                });
        },
        // Создание нового покупателя
        submitNewBuyer() {
            if (this.validateForm()) {
                const buyerData = {
                    name: this.newBuyer.name,
                    email: this.newBuyer.email
                };
                sendRequest('/ardozlock/savebuyer/', buyerData)
                    .then(result => {
                        if (result.status === 'ok') {
                            alert('Покупатель успешно создан!');
                            this.fetchBuyers();  // Обновляем список покупателей
                            this.closeCreateBuyerForm();
                        } else {
                            alert('Ошибка при создании покупателя');
                        }
                    })
                    .catch(error => {
                        alert('Ошибка при создании покупателя');
                    });
            }
        },
        // Валидация формы создания покупателя
        validateForm() {
            this.errors = {};
            if (!this.newBuyer.name) this.errors.name = 'Имя покупателя обязательно';
            if (!this.newBuyer.email) this.errors.email = 'Email обязателен';
            return Object.keys(this.errors).length === 0;
        },
        // Удаление покупателя
        deleteBuyer(buyerId) {
            sendRequest(`/ardozlock/deletebuyer/${buyerId}`, {}, 'DELETE')
                .then(result => {
                    if (result.status === 'ok') {
                        alert('Покупатель удален!');
                        this.fetchBuyers();  // Обновляем список покупателей
                    } else {
                        alert('Ошибка при удалении покупателя');
                    }
                })
                .catch(error => {
                    alert('Ошибка при удалении покупателя');
                });
        },
        // Показ/скрытие информации о покупателе
        toggleBuyerInfo(buyerId) {
            const buyer = this.buyers.find(b => b.id === buyerId);
            buyer.showInfo = !buyer.showInfo;
        },
        // Открытие формы для создания нового покупателя
        openCreateBuyerForm() {
            this.newBuyer = { name: '', email: '' };
            this.showCreateBuyerModal = true;
        },
        // Закрытие формы создания покупателя
        closeCreateBuyerForm() {
            this.showCreateBuyerModal = false;
        }
    },
    template: `
        <div class="ardozlock-tab__contents__item">
            <div class="ardozlock-buyer__buyer-management">
                <div class="ardozlock-buyer__header">
                    <input class="ardozlock-buyer__search" type="text" v-model="searchQuery" placeholder="Поиск покупателя...">
                    <button class="ardozlock-buyer__button ardozlock-buyer__button--create" @click="openCreateBuyerForm">Создать покупателя</button>
                </div>

                <ul id="buyer-list">
                    <li class="ardozlock-buyer__buyer-item" v-for="buyer in filteredBuyers" :key="buyer.id" @click="toggleBuyerInfo(buyer.id)">
                        <span class="ardozlock-buyer__buyer-name">[[ buyer.name ]]</span>
                        <button class="ardozlock-buyer__button ardozlock-buyer__button--delete" @click.stop="deleteBuyer(buyer.id)">Удалить</button>

                        <div class="ardozlock-buyer__buyer-info" v-if="buyer.showInfo">
                            <div class="ardozlock-buyer__app-card" v-for="app in buyer.apps" :key="app.name">
                                <h5>[[ app.name ]]</h5>
                                <label v-for="page in app.pages" :key="page.id">
                                    <input type="checkbox" v-model="page.access" @click.stop> [[ page.name ]]
                                </label>
                            </div>

                            <!-- Включаем компонент выбора заблокированных страниц -->
                            <blocked-pages-selector 
                                :buyer-id="buyer.id" 
                                @click.stop/>
                        </div>

                    </li>
                </ul>
            </div>
        </div>

        <!-- Модальное окно для создания покупателя -->
        <div v-if="showCreateBuyerModal" class="ardozlock-buyer__modal">
            <div class="ardozlock-buyer__modal-content">
                <span class="ardozlock-buyer__close" @click="closeCreateBuyerForm">&times;</span>
                <h2>Создать покупателя</h2>
                <form @submit.prevent="submitNewBuyer">
                    <div class="ardozlock-buyer__form-group">
                        <label for="buyer-name">Имя покупателя:</label>
                        <input type="text" id="buyer-name" v-model="newBuyer.name" required>
                        <span v-if="errors.name">[[ errors.name ]]</span>
                    </div>
                    <div class="ardozlock-buyer__form-group">
                        <label for="buyer-email">Email:</label>
                        <input type="text" id="buyer-email" v-model="newBuyer.email" required>
                        <span v-if="errors.email">[[ errors.email ]]</span>
                    </div>
                    <div class="ardozlock-buyer__form-actions">
                        <button type="submit" class="ardozlock-buyer__button ardozlock-buyer__button--save">Сохранить</button>
                        <button type="button" class="ardozlock-buyer__button ardozlock-buyer__button--cancel" @click="closeCreateBuyerForm">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    `
};
