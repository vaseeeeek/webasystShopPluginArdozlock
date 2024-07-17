<?php

class shopArdozlockPluginBackendClosedcatlistAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new shopBackendLayout());
        $this->layout->assign('no_level2', true);

        $this->view->assign('categories', $this->getCategoriesWithHierarchy());
        $this->view->assign('pages', $this->getPagesWithHierarchy());

        // Fetch and assign links, including their multiple categories and email
        $linksModel = new shopArdozlockPluginLinksModel();
        $this->view->assign('links', $linksModel->getLinks());
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
                'name' => $prefix . $category['name']
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

