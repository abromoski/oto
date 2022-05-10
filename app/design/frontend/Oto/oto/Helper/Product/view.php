
<?php
namespace Oto\oto\Helper\Product;
use Magento\Framework\View\Result\Page as ResultPage;
/**
 * Catalog category helper
 */
class View extends \Magento\Catalog\Helper\Product\View
{
    public function initProductLayout(ResultPage $resultPage, $product, $params = null)
    {
        $settings = $this->_catalogDesign->getDesignSettings($product);
        $pageConfig = $resultPage->getConfig();
        if ($settings->getCustomDesign()) {
            $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }
        // Apply custom page layout
        if ($settings->getPageLayout()) {
            $pageConfig->setPageLayout($settings->getPageLayout());
        }
        $urlSafeSku = rawurlencode($product->getSku());
        if ($params && $params->getBeforeHandles()) {
            foreach ($params->getBeforeHandles() as $handle) {
                $resultPage->addPageLayoutHandles(['customlayout' => $product->getCustomlayout(), 'id' => $product->getId(), 'sku' => $urlSafeSku], $handle);
                $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], $handle, false);
            }
        }
        $resultPage->addPageLayoutHandles(['customlayout' => $product->getCustomlayout(), 'id' => $product->getId(), 'sku' => $urlSafeSku]);
        $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], null, false);
        if ($params && $params->getAfterHandles()) {
            foreach ($params->getAfterHandles() as $handle) {
                $resultPage->addPageLayoutHandles(['customlayout' => $product->getCustomlayout(), 'id' => $product->getId(), 'sku' => $urlSafeSku], $handle);
                $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], $handle, false);
            }
        }
        // Apply custom layout update once layout is loaded
        $update = $resultPage->getLayout()->getUpdate();
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }
        $currentCategory = $this->_coreRegistry->registry('current_category');
        $controllerClass = $this->_request->getFullActionName();
        if ($controllerClass != 'catalog-product-view') {
            $pageConfig->addBodyClass('catalog-product-view');
        }
        $pageConfig->addBodyClass('product-' . $product->getUrlKey());
        if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
            $pageConfig->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($currentCategory))
                ->addBodyClass('category-' . $currentCategory->getUrlKey());
        }
        return $this;
    }
}
