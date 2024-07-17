<?php

class shopArdozlockPluginLinksModel extends waModel
{
    protected $table = 'ardozlock_links'; 

    public function getLinks()
    {
        $sql = "SELECT l.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_names, GROUP_CONCAT(c.full_url SEPARATOR ', ') AS category_urls 
                FROM {$this->table} l
                JOIN ardozlock_link_categories lc ON l.id = lc.link_id
                JOIN shop_category c ON lc.category_id = c.id
                GROUP BY l.id
                ORDER BY l.created_at DESC";
        
        return $this->prepareLinksForDisplay($this->query($sql)->fetchAll());
    }
    protected function prepareLinksForDisplay($links)
    {
        $preparedLinks = [];
        foreach ($links as $link) {
            $categories = $this->fetchCategoriesForLink($link['id']);
            
            $link['categories'] = $categories;
            
            $preparedLinks[] = $link;
        }
        return $preparedLinks;
    }
    protected function fetchCategoriesForLink($linkId)
    {
        $linkCategoriesModel = new shopArdozlockPluginLinkCategoriesModel();
        return $linkCategoriesModel->getCategoriesByLinkId($linkId);
    }
    public function getLinkByHashAndCategoryId($uniqueHash, $categoryId) {
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

    
}
