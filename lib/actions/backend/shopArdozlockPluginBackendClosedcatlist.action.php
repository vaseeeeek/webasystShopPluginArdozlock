<?php

class shopArdozlockPluginBackendClosedcatlistAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new shopBackendLayout());
        $this->layout->assign('no_level2', true);
        $this->view->assign('plugin_url', wa()->getPlugin('ardozlock')->getPluginStaticUrl());

        $this->view->assign('categories', $this->getCategoriesWithHierarchy());// Создаем объект модели для работы с заблокированными страницами

        
        $globalBlockedPagesModel = new shopArdozlockGlobalblockedpagesModel();

        // Получаем список всех заблокированных страниц
        $globalBlockedPages = $globalBlockedPagesModel->getAllBlockedPages();

        // Передаем данные в шаблон
        $this->view->assign('globalBlockedPages', $globalBlockedPages);

        
        // Инициализация сервиса покупателей
        $buyerService = new shopArdozlockBuyerService();

        // Получаем список покупателей с их заблокированными страницами
        $buyersData = $buyerService->getAllBuyersWithBlockedPages();

        // Передаем данные в шаблон
        $this->view->assign('buyersData', $buyersData);
        // $this->view->assign('pages', $this->getPagesWithHierarchy());

        // Fetch and assign links, including their multiple categories and email
        // $linksModel = new shopArdozlockPluginLinksModel();
        // $this->view->assign('links', $linksModel->getLinks());
        // Загрузка содержимого шаблона письма
        $template_path = wa()->getAppPath('plugins/ardozlock/templates/email_template.html', 'shop');
        $emailTemplateContent = file_exists($template_path) ? file_get_contents($template_path) : '';
        $this->view->assign('emailTemplateContent', $emailTemplateContent);
    }

    protected function getCategoriesWithHierarchy()
    {
        $model = new waModel();
        $sql = "SELECT * FROM shop_category ORDER BY left_key ASC";
        $categories = $model->query($sql)->fetchAll();
        return $this->prepareCategoriesForDisplay($categories);
    }

    protected function prepareCategoriesForDisplay($categories)
    {
        $preparedCategories = [];
        foreach ($categories as $category) {
            $prefix = str_repeat('— ', $category['depth']);
            $preparedCategories[] = [
                'id' => $category['id'],
                'name' => $prefix . $category['name'],
                'url' => "/" . $category['full_url'] . "/"
            ];
        }
        return $preparedCategories;
    }

    protected function getPagesWithHierarchy()
    {
        $model = new waModel();
        $sql = "SELECT * FROM shop_page ORDER BY sort ASC";
        $pages = $model->query($sql)->fetchAll();
        return $this->preparePagesForDisplay($pages);
    }

    protected function preparePagesForDisplay($pages)
    {
        $preparedPages = [];
        foreach ($pages as $page) {
            $prefix = ''; // Здесь можно добавить префикс, если у вас есть иерархия
            $preparedPages[] = [
                'id' => $page['id'],
                'name' => $prefix . $page['name'],
                'url' => $page['url']
            ];
        }
        return $preparedPages;
    }
}

