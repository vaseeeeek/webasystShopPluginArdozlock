<?php

class shopArdozlockPluginBackendClosedcatlistAction extends waViewAction
{
    public function execute()
    {   
        
        $this->setLayout(new shopBackendLayout());
        $this->layout->assign('no_level2', true);
        
        $this->view->assign('categories', $this->getCategoriesWithHierarchy());

        // Fetch and assign links, including their multiple categories and email
        $linksModel = new shopArdozlockPluginLinksModel();
        // $links = $this->prepareLinksForDisplay($linksModel->getLinks());
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
}

