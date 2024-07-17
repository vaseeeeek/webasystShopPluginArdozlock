<?php   
class shopArdozlockPluginLinkShoppageModel extends waModel
{
    protected $table = 'ardozlock_link_shoppage';

    public function getPagesByLinkId($linkId)
    {
        $sql = "SELECT p.id, p.name, p.full_url FROM shop_page p
                JOIN {$this->table} lp ON p.id = lp.page_id
                WHERE lp.link_id = i:link_id";
        return $this->query($sql, ['link_id' => $linkId])->fetchAll();
    }
}

