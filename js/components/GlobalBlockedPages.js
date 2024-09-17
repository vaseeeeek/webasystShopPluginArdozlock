import { sendRequest } from '../utils.js';

export default {

    delimiters: ['[[', ']]'],
    data() {
        return {
            apps: [
                {
                    id: 'shop-cat',
                    name: 'Категории',
                    application_id: 'shop',
                    page_type: 'category',
                    expanded: false,
                    pages: window.ardozlock.allCategories.map(category => ({
                        id: category.id,
                        name: category.name,
                        access: false
                    })),
                    selectedPages: []
                },
                {
                    id: 'shop-pages',
                    name: 'Страницы приложения "Магазин"',
                    application_id: 'shop',
                    page_type: 'infopage',
                    expanded: false,
                    pages: Object.values(window.ardozlock.allShopPages).map(page => ({
                        id: page.id,
                        name: page.name,
                        access: false
                    })),
                    selectedPages: []
                },
                {
                    id: 'site-pages',
                    name: 'Страницы приложения "Сайт"',
                    application_id: 'site',
                    page_type: 'infopage',
                    expanded: false,
                    pages: Object.values(window.ardozlock.allSitePages).map(page => ({
                        id: page.id,
                        name: page.name,
                        access: false
                    })),
                    selectedPages: []
                }
            ]
        };
    },
    
    created() {
        // Пример использования глобально заблокированных страниц
        this.globalBlockedPages = window.ardozlock.globalBlockedPages || [];

        // Инициализация выбранных страниц для каждого приложения
        this.apps.forEach(app => {
            app.selectedPages = this.globalBlockedPages
                .filter(page => page.application_id === app.application_id && page.page_type === app.page_type)
                .map(page => page.page_id);
        });
    },
    methods: {
        // Метод для сворачивания/разворачивания списка страниц приложения
        toggleApp(appId) {
            const app = this.apps.find(app => app.id === appId);
            app.expanded = !app.expanded;
        },
        // Метод сохранения заблокированных страниц
        saveGlobalBlockedPages() {
            const blockedPages = this.getGlobalBlockedPages();
            if (blockedPages.length === 0) {
                alert('Нет изменений для сохранения.');
                return;
            }
            sendRequest('/ardozlock/saveglobalsblockpages/', { blockedPages })
                .then(result => {
                    if (result.status === 'ok') {
                        alert('Изменения успешно сохранены');
                    } else {
                        alert('Ошибка при сохранении изменений');
                    }
                })
                .catch(error => {
                    alert('Ошибка при сохранении изменений');
                });
        },
        // Метод для сбора заблокированных страниц
        getGlobalBlockedPages() {
            return this.apps.reduce((acc, app) => {
                if (!app.selectedPages || app.selectedPages.length === 0) return acc;

                app.selectedPages.forEach(pageId => {
                    const page = app.pages.find(p => p.id === pageId);
                    if (page) {
                        acc.push({
                            page_id: page.id,
                            page_type: app.page_type,
                            application_id: app.application_id
                        });
                    }
                });
                return acc;
            }, []);
        }
    },
    template: `
        <div class="ardozlock-tab__contents__item">
            <div class="ardozlock-closepage">
                <!-- Шапка страницы -->
                <div class="ardozlock-closepage__header">
                    <h2>Управление доступом к страницам</h2>
                    <p>Выберите страницы, доступ к которым нужно ограничить</p>
                </div>

                <!-- Основная часть страницы -->
                <div class="ardozlock-closepage__main">
                    <div 
                        v-for="app in apps.filter(app => app.pages.length > 0)" 
                        :key="app.id" 
                        class="ardozlock-closepage__app-card"
                    >
                        <div class="ardozlock-closepage__app-header" @click="toggleApp(app.id)">
                            <h3>[[ app.name ]]</h3>
                            <button class="ardozlock-closepage__toggle-btn">
                                [[ app.expanded ? 'Свернуть' : 'Развернуть' ]]
                            </button>
                        </div>
                        <div v-if="app.expanded" class="ardozlock-closepage__pages-list">
                            <select 
                                v-model="app.selectedPages" 
                                multiple 
                                class="ardozlock-closepage__select"
                                size="5">
                                <option 
                                    v-for="(page, pageIndex) in app.pages" 
                                    :key="page.id" 
                                    :value="page.id">
                                    [[ page.name ]]
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Подвал страницы -->
                <div class="ardozlock-closepage__footer">
                    <button class="ardozlock-closepage__button ardozlock-closepage__button--save" @click="saveGlobalBlockedPages">
                        Сохранить изменения
                    </button>
                </div>
            </div>
        </div>
    `
};
