<?php   
class shopArdozlockPluginLinkShoppageModel extends waModel
{
    protected $table = 'ardozlock_link_shoppage';

    public function getCategoriesByLinkId($linkId)
    {
        $sql = "SELECT c.id, c.name, c.full_url FROM shop_category c
                JOIN {$this->table} lc ON c.id = lc.category_id
                WHERE lc.link_id = i:link_id";
        return $this->query($sql, ['link_id' => $linkId])->fetchAll();
    }
}

