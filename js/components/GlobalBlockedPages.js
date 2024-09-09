// Регистрация компонента GlobalBlockedPages
window.ardozlock = window.ardozlock || {};
window.ardozlock.components = window.ardozlock.components || {};

window.ardozlock.components.globalblockedpages = {
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
            ],
            globalBlockedPages: window.ardozlock.globalBlockedPages || [],
        };
    },
    created() {
        // Инициализация выбранных страниц для каждого приложения
        this.apps.forEach(app => {
            app.selectedPages = this.globalBlockedPages
                .filter(page => page.application_id === app.application_id && page.page_type === app.page_type)
                .map(page => page.page_id);
        });
    },
    methods: {
        toggleApp(appId) {
            const app = this.apps.find(app => app.id === appId);
            app.expanded = !app.expanded;
        },
        saveGlobalBlockedPages() {
            const blockedPages = this.getGlobalBlockedPages();
            if (blockedPages.length === 0) {
                alert('Нет изменений для сохранения.');
                return;
            }

            // Пример отправки данных на сервер
            this.sendRequest('/ardozlock/saveglobalsblockpages/', { blockedPages })
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
        },
        sendRequest(url, data, method = 'POST', headers = {}) {
            return fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    ...headers,
                },
                body: JSON.stringify(data),
            })
                .then(response => response.json())
                .catch(error => {
                    console.error('Ошибка при отправке данных:', error);
                    throw error;
                });
        }
    },
    template: `
        <div>
            <div v-for="app in apps" :key="app.id" class="ardozlock-closepage__app-card">
                <div class="ardozlock-closepage__app-header" @click="toggleApp(app.id)">
                    <h3>[[ app.name ]]</h3>
                    <button class="ardozlock-closepage__toggle-btn">
                        [[ app.expanded ? 'Свернуть' : 'Развернуть' ]]
                    </button>
                </div>
                <div v-if="app.expanded" class="ardozlock-closepage__pages-list">
                    <select v-model="app.selectedPages" multiple class="ardozlock-closepage__select" size="5">
                        <option v-for="(page, pageIndex) in app.pages" :key="page.id" :value="page.id">
                            [[ page.name ]]
                        </option>
                    </select>
                </div>
            </div>
            <button class="ardozlock-closepage__button ardozlock-closepage__button--save" @click="saveGlobalBlockedPages">
                Сохранить изменения
            </button>
        </div>
    `,
};
