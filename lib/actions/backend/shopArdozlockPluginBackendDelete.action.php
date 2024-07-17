<?php

class shopArdozlockPluginBackendDeleteAction extends waViewAction
{
    public function execute()
    {
        $linkId = waRequest::post('id');
        if (!$linkId) {
            echo json_encode(['success' => false]);
            return;
        }

        $linksModel = new shopArdozlockPluginLinksModel();
        $linkCategoriesModel = new shopArdozlockPluginLinkCategoriesModel();

        // First, attempt to delete the associated categories for the link
        $deleteCategoriesSuccess = $linkCategoriesModel->deleteByField('link_id', $linkId);

        // Then, attempt to delete the link itself
        $deleteLinkSuccess = $linksModel->deleteById($linkId);
    }
}
