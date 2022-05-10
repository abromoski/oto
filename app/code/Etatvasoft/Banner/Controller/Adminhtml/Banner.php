<?php

namespace Etatvasoft\Banner\Controller\Adminhtml;

/**
 * Class Banner
 * @package Etatvasoft\Banner\Controller\Adminhtml
 * Admin Banner edit controller
 */
class Banner extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $formSessionKey = 'etatvasoft_banner_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $allowedKey = 'Etatvasoft_Banner::banner';

    /**
     * Model class name
     * @var string
     */
    protected $modelClass = 'Etatvasoft\Banner\Model\Banner';

    /**
     * Active menu key
     * @var string
     */
    protected $activeMenu = 'Etatvasoft_Banner::banner';

    /**
     * Status field name
     * @var string
     */
    protected $statusField = 'is_active';

    /**
     * Save request params key
     * @var string
     */
    protected $paramsHolder = 'post';
}
