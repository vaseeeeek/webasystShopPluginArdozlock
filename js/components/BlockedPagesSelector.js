export default {
    delimiters: ['[[', ']]'],
    props: {
        blockedPages: {
            type: Array,
            required: true
        },
        availablePages: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            apps: [
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
            ]
        };
    },
    methods: {
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
            return page ? page.name : 'Неизвестная страница';
        },
        saveBlockedPages() {
            const blockedPages = this.apps.reduce((acc, app) => {
                app.pages.forEach(page => {
                    if (this.blockedPages.includes(page.page_id)) {
                        acc.push(page.page_id);
                    }
                });
                return acc;
            }, []);
            this.$emit('update-blocked-pages', blockedPages);
        }
    },
    template: `
        <div class="blocked-pages-selector">
            <h4 class="blocked-pages-selector__title">Управление доступом к заблокированным страницам:</h4>
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
                        <select v-model="blockedPages" multiple class="blocked-pages-selector__select">
                            <option 
                                v-for="page in app.pages" 
                                :key="page.page_id" 
                                :value="page.page_id">
                                [[ getPageNameById(page.page_id, page.page_type, page.application_id) ]]
                            </option>
                        </select>
                    </div>
                </div>
                <button class="blocked-pages-selector__button blocked-pages-selector__button--save" @click="saveBlockedPages">Сохранить изменения</button>
            </div>
        </div>
    `
};
