import { sendRequest } from '../utils.js';
import BlockedPagesSelector from './BlockedPagesSelector.js';
import AccessDurationSelector from './AccessDurationSelector.js';
import EditBuyerProfile from './EditBuyerProfile.js';
import ResetAllActivationDates from './ResetAllActivationDates.js';

export default {
    delimiters: ['[[', ']]'],
    components: {
        'blocked-pages-selector': BlockedPagesSelector,
        'access-duration-selector': AccessDurationSelector,
        'edit-buyer-profile': EditBuyerProfile,
        'reset-all-users-activated-date': ResetAllActivationDates,
    },
    data() {
        return {
            searchQuery: '',
            buyers: [],
            showCreateBuyerModal: false,
            newBuyer: { name: '', email: '' },
            errors: {},
            sortBy: 'name', // Критерий сортировки: 'name' или 'startDate'
            sortOrder: 'asc',
            editBuyer: null
        };
    },
    created() {
        this.fetchBuyers();
    },
    computed: {
        filteredBuyers() {
            let filtered = this.buyers.filter(buyer =>
                buyer.name.toLowerCase().includes(this.searchQuery.toLowerCase())
            );

            return filtered.sort((a, b) => {
                let sortFieldA, sortFieldB;
                if (this.sortBy === 'name') {
                    sortFieldA = a.name.toLowerCase();
                    sortFieldB = b.name.toLowerCase();
                } else if (this.sortBy === 'startDate') {
                    sortFieldA = new Date(a.access_start_date);
                    sortFieldB = new Date(b.access_start_date);
                } else if (this.sortBy === 'remainingDays') {
                    sortFieldA = a.remainingDays !== null ? a.remainingDays : Infinity;
                    sortFieldB = b.remainingDays !== null ? b.remainingDays : Infinity;
                }

                if (sortFieldA < sortFieldB) return this.sortOrder === 'asc' ? -1 : 1;
                if (sortFieldA > sortFieldB) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
        }
    },
    methods: {
        openEditBuyer(buyer) {
            this.editBuyer = { ...buyer };
        },
        closeEditBuyer() {
            this.editBuyer = null;
        },
        updateBuyerProfile(updatedData) {
            // Обновление данных покупателя в списке
            const buyer = this.buyers.find(b => b.id === this.editBuyer.id);
            if (buyer) {
                buyer.name = updatedData.name;
                buyer.email = updatedData.email;
            }
            this.closeEditBuyer();
        },
        setSortBy(sortBy) {
            if (this.sortBy === sortBy) {
                // Если уже сортируем по этому полю, меняем порядок сортировки
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = sortBy;
                this.sortOrder = 'asc'; // По умолчанию по возрастанию
            }
        },
        fetchBuyers() {
            sendRequest('/ardozlock/getbuyers/', {})
                .then(result => {
                    this.buyers = result.data.buyers.map(buyer => {
                        // Вычисляем оставшиеся дни
                        if (buyer.access_start_date && buyer.access_duration_days) {
                            const startDate = new Date(buyer.access_start_date);
                            const endDate = new Date(startDate);
                            endDate.setDate(startDate.getDate() + parseInt(buyer.access_duration_days, 10));

                            const currentDate = new Date();
                            const remainingTime = endDate - currentDate;

                            buyer.remainingDays = remainingTime > 0 ? Math.ceil(remainingTime / (1000 * 60 * 60 * 24)) : 0;
                        } else {
                            buyer.remainingDays = null;
                        }
                        return buyer;
                    });
                })
                .catch(error => {
                    console.error('Ошибка при загрузке покупателей:', error);
                });
        },
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
                            this.fetchBuyers();
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
        validateForm() {
            this.errors = {};
            if (!this.newBuyer.name) this.errors.name = 'Имя покупателя обязательно';
            if (!this.newBuyer.email) this.errors.email = 'Email обязателен';
            return Object.keys(this.errors).length === 0;
        },
        deleteBuyer(buyerId) {
            sendRequest(`/ardozlock/deletebuyer/${buyerId}`, {}, 'DELETE')
                .then(result => {
                    if (result.status === 'ok') {
                        alert('Покупатель удален!');
                        this.fetchBuyers();
                    } else {
                        alert('Ошибка при удалении покупателя');
                    }
                })
                .catch(error => {
                    alert('Ошибка при удалении покупателя');
                });
        },
        toggleBuyerInfo(buyerId) {
            const buyer = this.buyers.find(b => b.id === buyerId);
            buyer.showInfo = !buyer.showInfo;
        },
        openCreateBuyerForm() {
            this.newBuyer = { name: '', email: '' };
            this.showCreateBuyerModal = true;
        },
        closeCreateBuyerForm() {
            this.showCreateBuyerModal = false;
        },
        sendEmailToBuyer(buyerId) {
            if (confirm('Вы уверены, что хотите отправить письмо этому клиенту?')) {
                sendRequest(`/ardozlock/sendemail/${buyerId}/`, {}, 'POST')
                .then(response => {
                    if (response.status === 'ok') {
                    alert('Письмо успешно отправлено!');
                    } else {
                    alert('Ошибка при отправке письма');
                    }
                })
                .catch(error => {
                    alert('Ошибка при отправке запроса');
                    console.error(error);
                });
            }
        }
    },
    template: `
        <div class="ardozlock-tab__contents__item">
            <div v-if="editBuyer" class="edit-buyer-modal">
                <edit-buyer-profile 
                    :buyer="editBuyer" 
                    @profile-updated="updateBuyerProfile" 
                    @close="closeEditBuyer"
                />
            </div>
            <div class="ardozlock-buyer__buyer-management">
                <div class="ardozlock-buyer__header">
                    <input class="ardozlock-buyer__search" type="text" v-model="searchQuery" placeholder="Поиск покупателя...">
                    <button class="ardozlock-buyer__button ardozlock-buyer__button--create" @click="openCreateBuyerForm">Создать покупателя</button>
                </div>

                <!-- Кнопки сортировки -->
                <div class="ardozlock-sort-buttons">
                    <span>
                        Сортировка по: 
                    </span>
                    <button 
                        @click="setSortBy('name')" 
                        :class="{ active: sortBy === 'name' }">
                        Имени
                        <span v-if="sortBy === 'name'">[[ sortOrder === 'asc' ? '↑' : '↓' ]]</span>
                    </button>
                    <button 
                        @click="setSortBy('startDate')" 
                        :class="{ active: sortBy === 'startDate' }">
                        Дате активации
                        <span v-if="sortBy === 'startDate'">[[ sortOrder === 'asc' ? '↑' : '↓' ]]</span>
                    </button>
                    <button 
                        @click="setSortBy('remainingDays')" 
                        :class="{ active: sortBy === 'remainingDays' }">
                        Оставшемуся времени
                        <span v-if="sortBy === 'remainingDays'">[[ sortOrder === 'asc' ? '↑' : '↓' ]]</span>
                    </button>
                    
                    <reset-all-users-activated-date>
                </div>



                <ul id="buyer-list">
                    <li class="ardozlock-buyer__buyer-item" v-for="buyer in filteredBuyers" :key="buyer.id" @click="toggleBuyerInfo(buyer.id)">
                        <span class="ardozlock-buyer__buyer-name">[[ buyer.name ]]</span>
                        <button class="ardozlock-buyer__button ardozlock-buyer__button--delete" @click.stop="deleteBuyer(buyer.id)">Удалить</button>
                        <button @click.stop="openEditBuyer(buyer)" class="ardozlock-buyer__button ardozlock-buyer__button--edit">
                            Редактировать
                        </button>
                        <button class="ardozlock-buyer__button ardozlock-buyer__button--mail" @click.stop="sendEmailToBuyer(buyer.id)">
                            Отправить письмо
                        </button>


                        <div class="ardozlock-buyer__buyer-info" v-if="buyer.showInfo">
                            <div class="ardozlock-buyer__app-card" v-for="app in buyer.apps" :key="app.name">
                                <h5>[[ app.name ]]</h5>
                                <label v-for="page in app.pages" :key="page.id">
                                    <input type="checkbox" v-model="page.access" @click.stop> [[ page.name ]]
                                </label>
                            </div>
                            <access-duration-selector 
                                :buyer-id="buyer.id"
                                :access-duration-days.number="Number(buyer.access_duration_days)"
                                :start-date="buyer.access_start_date"
                                @click.stop
                            />

                            <!-- Включаем компонент выбора заблокированных страниц -->
                            <blocked-pages-selector 
                                :buyer-id="buyer.id" 
                                :buyer-hash="buyer.hash" 
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
