<?php
namespace Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email;

abstract class AbstractEmail extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Load Email Templates for product
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function loadEmailData($productId = null, $storeId = null)
    {
        $connection = $this->getConnection();

        $columns = [
            'gemail_id' => $this->getIdFieldName(),
            'product_id' => 'product_id',
            'email_template' => 'email_template',
            'image_path' => 'image_path',
            'store_id' => 'store_id',
        ];

        $columns = $this->_loadEmailDataColumns($columns);

        $select = $connection->select()
            ->from($this->getMainTable(), $columns);
        
        if ($productId !== null) {
            $select->where("product_id = ?", $productId);
        }

        $this->_loadEmailDataSelect($select);

        if ($storeId !== null) {
            if ($storeId == '0') {
                $select->where('store_id = ?', $storeId);
            } else {
                $select->where('store_id IN(?)', [0, $storeId]);
            }
        }

        return $connection->fetchAll($select);
    }

    public function loadEmailDataById($gemail_id)
    {
        $connection = $this->getConnection();

        $columns = [
            'gemail_id' => $this->getIdFieldName(),
            'product_id' => 'product_id',
            'email_template' => 'email_template',
            'image_path' => 'image_path',
            'store_id' => 'store_id',
        ];

        $columns = $this->_loadEmailDataColumns($columns);

        $select = $connection->select()
            ->from($this->getMainTable(), $columns)
            ->where("gemail_id = ?", $gemail_id);

        $this->_loadEmailDataSelect($select);

        return $connection->fetchAll($select);
    }
    
    /**
     * Load specific sql columns
     *
     * @param array $columns
     * @return array
     */
    protected function _loadEmailDataColumns($columns)
    {
        return $columns;
    }

    /**
     * Load specific db-select data
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _loadEmailDataSelect($select)
    {
        return $select;
    }

    /**
     * Delete Email template for product
     *
     * @param int $productId
     * @param int $storeId
     * @param int $gemailId
     * @return int The number of affected rows
     */
    public function deleteEmailData($productId, $storeId = null, $gemailId = null)
    {
        $connection = $this->getConnection();

        $conds = [$connection->quoteInto('product_id = ?', $productId)];

        if ($storeId !== null) {
            $conds[] = $connection->quoteInto('store_id = ?', $storeId);
        }

        if ($gemailId !== null) {
            $conds[] = $connection->quoteInto($this->getIdFieldName() . ' = ?', $gemailId);
        }

        $where = implode(' AND ', $conds);

        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * Save Email template object
     *
     * @param \Magento\Framework\DataObject $emailObject
     * @return \Mconnect\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Email
     */
    public function saveEmailData(\Magento\Framework\DataObject $emailObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($emailObject, $this->getMainTable());

        if (!empty($data[$this->getIdFieldName()])) {
            $where = $connection->quoteInto($this->getIdFieldName() . ' = ?', $data[$this->getIdFieldName()]);
            unset($data[$this->getIdFieldName()]);
            $connection->update($this->getMainTable(), $data, $where);
        } else {
            $connection->insert($this->getMainTable(), $data);
        }
        return $this;
    }
}
