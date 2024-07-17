<?php

class shopArdozlockPluginLinksModel extends waModel
{
    protected $table = 'ardozlock_links';

    public function getLinks()
    {
        $sql = "SELECT l.*, GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS category_names, GROUP_CONCAT(DISTINCT c.full_url SEPARATOR ', ') AS category_urls, 
                GROUP_CONCAT(DISTINCT p.title SEPARATOR ', ') AS page_titles, GROUP_CONCAT(DISTINCT p.full_url SEPARATOR ', ') AS page_urls 
                FROM {$this->table} l
                LEFT JOIN ardozlock_link_categories lc ON l.id = lc.link_id
                LEFT JOIN shop_category c ON lc.category_id = c.id
                LEFT JOIN ardozlock_link_shoppage lp ON l.id = lp.link_id
                LEFT JOIN shop_page p ON lp.page_id = p.id
                GROUP BY l.id
                ORDER BY l.created_at DESC";

        return $this->prepareLinksForDisplay($this->query($sql)->fetchAll());
    }

    protected function prepareLinksForDisplay($links)
    {
        $preparedLinks = [];
        foreach ($links as $link) {
            $categories = $this->fetchCategoriesForLink($link['id']);
            $pages = $this->fetchPagesForLink($link['id']);

            $link['categories'] = $categories;
            $link['pages'] = $pages;

            $preparedLinks[] = $link;
        }
        return $preparedLinks;
    }

    protected function fetchCategoriesForLink($linkId)
    {
        $linkCategoriesModel = new shopArdozlockPluginLinkCategoriesModel();
        return $linkCategoriesModel->getCategoriesByLinkId($linkId);
    }

    protected function fetchPagesForLink($linkId)
    {
        $linkPagesModel = new shopArdozlockPluginLinkShoppageModel();
        return $linkPagesModel->getPagesByLinkId($linkId);
    }

    public function getLinkByHashAndCategoryId($uniqueHash, $categoryId)
    {
        $sql = "SELECT l.* FROM {$this->table} l
                JOIN ardozlock_link_categories lc ON l.id = lc.link_id
                WHERE l.unique_hash = ? AND lc.category_id = ? AND l.expires_at > NOW()";
        return $this->query($sql, $uniqueHash, $categoryId)->fetchAssoc();
    }

    public function getLinksByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} l
                JOIN ardozlock_link_categories lc ON l.id = lc.link_id
                WHERE lc.category_id = i:category_id";
        return $this->query($sql, ['category_id' => $categoryId])->fetchAll();
    }

    public function getLinkByHashAndPageId($uniqueHash, $pageId)
    {
        $sql = "SELECT l.* FROM {$this->table} l
                JOIN ardozlock_link_shoppage lp ON l.id = lp.link_id
                WHERE l.unique_hash = ? AND lp.page_id = ? AND l.expires_at > NOW()";
        return $this->query($sql, $uniqueHash, $pageId)->fetchAssoc();
    }

    public function getLinksByPageId($pageId)
    {
        $sql = "SELECT * FROM {$this->table} l
                JOIN ardozlock_link_shoppage lp ON l.id = lp.link_id
                WHERE lp.page_id = i:page_id";
        return $this->query($sql, ['page_id' => $pageId])->fetchAll();
    }
}
