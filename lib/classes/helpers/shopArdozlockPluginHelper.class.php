<?php
class shopArdozlockPluginHelper
{
    public static function isValidCatAccess($categoryId)
    {
        $uniqueHash = wa()->getRequest()->get('uniq'); // Get the unique_hash from the URL

        $model = new shopArdozlockPluginLinksModel();

        // First, check if any link has been created for this category
        $linksForCategory = $model->getLinksByCategoryId($categoryId);
        
        // If no links have been created for this category, return true (page is available)
        if (empty($linksForCategory)) {
            return true;
        }

        // If a unique hash is not provided, and links exist for this category, return false
        if (!$uniqueHash) {
            return false;
        }

        // Proceed with the original logic to check for a specific link with the provided hash
        $link = $model->getLinkByHashAndCategoryId($uniqueHash, $categoryId);

        if (!$link) {
            return false; // No specific link found for this hash and category ID
        }

        $currentDateTime = date('Y-m-d H:i:s');
        if ($link['expires_at'] < $currentDateTime) {
            return false; // Specific link has expired
        }

        return true; // Specific link is valid
    }

    public static function isValidPageAccess($pageId)
    {
        $uniqueHash = wa()->getRequest()->get('uniq'); // Get the unique_hash from the URL

        $model = new shopArdozlockPluginLinksModel();

        // First, check if any link has been created for this page
        $linksForPage = $model->getLinksByPageId($pageId);
        
        // If no links have been created for this page, return true (page is available)
        if (empty($linksForPage)) {
            return true;
        }

        // If a unique hash is not provided, and links exist for this page, return false
        if (!$uniqueHash) {
            return false;
        }

        // Proceed with the original logic to check for a specific link with the provided hash
        $link = $model->getLinkByHashAndPageId($uniqueHash, $pageId);

        if (!$link) {
            return false; // No specific link found for this hash and page ID
        }

        $currentDateTime = date('Y-m-d H:i:s');
        if ($link['expires_at'] < $currentDateTime) {
            return false; // Specific link has expired
        }
        waLog::dump($uniqueHash);
        waLog::dump($linksForPage);
        waLog::dump($link['expires_at']);
        waLog::dump($link);

        return true; // Specific link is valid
    }
}
