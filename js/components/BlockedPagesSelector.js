import { sendRequest } from '../utils.js';

export default {
    delimiters: ['[[', ']]'],
    props: {
        buyerId: {
            type: String,
            required: true
        },
        buyerHash: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            availablePages: [], 
            unlockedPagesByApp: {}, 
            apps: [] 
        };
    },
    created() {
        this.fetchGlobalBlockedPages();
        this.fetchUnlockedPages();
    },
    methods: {
        fetchGlobalBlockedPages() {
            sendRequest('/ardozlock/getglobalblockedpages/', {})
                .then(result => {
                    this.availablePages = result.data.blockedPages;
                    this.initializeApps();
                })
                .catch(error => {
                    console.error('Ошибка при загрузке глобальных заблокированных страниц:', error);
                });
        },
        fetchUnlockedPages() {
            sendRequest(`/ardozlock/getunlockedbuyerspages/${this.buyerId}`, {})
                .then(result => {
                    this.unlockedPages = result.data.unlockedPages;
                    this.synchronizeUnlockedPages(); // Синхронизируем открытые страницы с приложениями
                })
                .catch(error => {
                    console.error('Ошибка при загрузке открытых страниц покупателя:', error);
                });
        },
        initializeApps() {
            this.apps = [
                {
                    id: 'shop-cat',
                    name: 'Категории',
                    application_id: 'shop',
                    page_type: 'category',
                    expanded: false,
                    pages: this.availablePages.filter(page => page.page_type === 'category' && page.application_id === 'shop')
                },
                {
                    id: 'shop-pages',
                    name: 'Страницы приложения "Магазин"',
                    application_id: 'shop',
                    page_type: 'infopage',
                    expanded: false,
                    pages: this.availablePages.filter(page => page.page_type === 'infopage' && page.application_id === 'shop')
                },
                {
                    id: 'site-pages',
                    name: 'Страницы приложения "Сайт"',
                    application_id: 'site',
                    page_type: 'infopage',
                    expanded: false,
                    pages: this.availablePages.filter(page => page.page_type === 'infopage' && page.application_id === 'site')
                }
            ];
        
            // Инициализация массивов для каждой группы страниц
            this.apps.forEach(app => {
                this.unlockedPagesByApp[app.id] = [];
            });
        },
        synchronizeUnlockedPages() {
            this.apps.forEach(app => {
                const unlockedForApp = this.unlockedPagesByApp[app.id];
                unlockedForApp.length = 0; // Очищаем массив перед заполнением
            
                app.pages.forEach(page => {
                    if (this.unlockedPages.some(up => up.page_id === page.page_id && up.application_id === app.application_id && up.page_type === page.page_type)) {
                        unlockedForApp.push(page.page_id);
                    }
                });
            });
        },
        toggleApp(appId) {
            const app = this.apps.find(app => app.id === appId);
            app.expanded = !app.expanded;
        },
        getPageNameById(pageId, pageType, appId) {
            let appPages = [];

            if (pageType === 'category') {
                appPages = window.ardozlock.allCategories || [];
            } else if (appId === 'shop') {
                appPages = Object.values(window.ardozlock.allShopPages) || [];
            } else if (appId === 'site') {
                appPages = Object.values(window.ardozlock.allSitePages) || [];
            }

            const page = appPages.find(p => p.id === pageId);
            const pageName = page ? page.name : 'Неизвестная страница';
            const pageUrl = page ? page.url : ''; // Получаем URL страницы
            return { name: pageName, url: pageUrl }; // Возвращаем имя и URL страницы
        },
        // Копирование ссылки в буфер обмена
        copyToClipboard(pageUrl,pageType) {
            let adjustedUrl = pageUrl;
        
            if (pageType === 'category') {
                adjustedUrl = `/category${pageUrl}`; // Добавляем "/category" в начало URL
            }
        
            const fullUrl = `${window.location.origin}${adjustedUrl}?hash=${this.buyerHash}`;
            navigator.clipboard.writeText(fullUrl)
                .then(() => {
                    alert('Ссылка скопирована: ' + fullUrl);
                })
                .catch(err => {
                    alert('Ошибка при копировании ссылки: ' + err);
                });
        },
        togglePageSelection(appId, pageId) {
            const unlockedForApp = this.unlockedPagesByApp[appId];
            const pageIndex = unlockedForApp.indexOf(pageId);
            if (pageIndex === -1) {
                unlockedForApp.push(pageId); // Добавляем страницу, если она не была выбрана
            } else {
                unlockedForApp.splice(pageIndex, 1); // Убираем страницу, если она была выбрана
            }
        },
        saveUnlockedPages() {
            const unlockedPages = this.apps.reduce((acc, app) => {
                const unlockedForApp = this.unlockedPagesByApp[app.id];
                app.pages.forEach(page => {
                    if (unlockedForApp.includes(page.page_id)) {
                        acc.push({
                            page_id: page.page_id,
                            page_type: page.page_type,
                            application_id: page.application_id
                        });
                    }
                });
                return acc;
            }, []);

            sendRequest(`/ardozlock/saveunlockedbuyerspages/`, {
                buyer_id: this.buyerId,
                unlockedPages: unlockedPages
            })
            .then(result => {
                if (result.status === 'ok') {
                    alert('Открытые страницы успешно сохранены!');
                } else {
                    alert('Ошибка при сохранении изменений');
                }
            })
            .catch(error => {
                alert('Ошибка при сохранении открытых страниц');
            });
        }
    },
    template: `
        <div class="blocked-pages-selector">
            <h4 class="blocked-pages-selector__title">Управление доступом к открытым страницам для покупателя</h4>
            <div class="blocked-pages-selector__content">
                <div 
                    v-for="app in apps.filter(app => app.pages.length > 0)" 
                    :key="app.id" 
                    class="blocked-pages-selector__app-card"
                >
                    <div class="blocked-pages-selector__app-header" @click="toggleApp(app.id)">
                        <h5>[[ app.name ]]</h5>
                        <button class="blocked-pages-selector__toggle-btn">
                            [[ app.expanded ? 'Свернуть' : 'Развернуть' ]]
                        </button>
                    </div>
                    <div v-if="app.expanded" class="blocked-pages-selector__pages-list">
                        <div 
                            v-for="page in app.pages" 
                            :key="page.page_id" 
                            class="blocked-pages-selector__page-item"
                        >
                            <label>
                                <input 
                                    type="checkbox" 
                                    :checked="unlockedPagesByApp[app.id].includes(page.page_id)" 
                                    @change="togglePageSelection(app.id, page.page_id)" 
                                />
                                [[ getPageNameById(page.page_id, page.page_type, page.application_id).name ]]
                            </label>
                            <button 
                                @click="copyToClipboard(getPageNameById(page.page_id, page.page_type, page.application_id).url, page.page_type)" 
                                class="blocked-pages-selector__copy-btn">
                                Скопировать ссылку
                            </button>
                        </div>
                    </div>
                </div>
                <button class="blocked-pages-selector__button blocked-pages-selector__button--save" @click="saveUnlockedPages">Сохранить изменения</button>
            </div>
        </div>
    `
};
