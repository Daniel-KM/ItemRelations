<?php
/**
 * Item Relations
 *
 * @copyright Copyright 2010-2014 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Item Relations Relation table.
 */
class Table_ItemRelationsRelation extends Omeka_Db_Table
{
    /**
     * Get the default select object.
     *
     * Automatically join with both Property and Vocabulary to get all the
     * data necessary to describe a whole relation.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $db = $this->_db;
        return parent::getSelect()
            ->join(
                array('item_relations_properties' => $db->ItemRelationsProperty),
                'item_relations_relations.property_id = item_relations_properties.id',
                array(
                    'property_vocabulary_id' => 'vocabulary_id',
                    'property_local_part' => 'local_part',
                    'property_label' => 'label',
                    'property_description' => 'description'
                )
            )
            ->join(
                array('item_relations_vocabularies' => $db->ItemRelationsVocabulary),
                'item_relations_properties.vocabulary_id = item_relations_vocabularies.id',
                array(
                    'vocabulary_namespace_prefix' => 'namespace_prefix',
                )
            );
    }

    /**
     * Find item relations by subject item ID.
     *
     * @param integer $subjectItemId
     * @param boolean $onlyExistingObjectItems
     * @param integer|array $propertyId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function findBySubjectItemId(
        $subjectItemId,
        $onlyExistingObjectItems = true,
        $propertyId = null,
        $limit = null,
        $page = null
    ) {
        $select = $this->_findBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId);
        if ($limit) {
            $this->applyPagination($select, $limit, $page);
        }
        return $this->fetchObjects($select);
    }

    /**
     * Find item relations by object item ID.
     *
     * @param integer $objectItemId
     * @param boolean $onlyExistingSubjectItems
     * @param integer|array $propertyId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function findByObjectItemId(
        $objectItemId,
        $onlyExistingSubjectItems = true,
        $propertyId = null,
        $limit = null,
        $page = null
    ) {
        $select = $this->_findByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId);
        if ($limit) {
            $this->applyPagination($select, $limit, $page);
        }
        return $this->fetchObjects($select);
    }

    /**
     * Find item relations by subject item ID, limited by group.
     *
     * @param integer $subjectItemId
     * @param boolean $onlyExistingObjectItems
     * @param integer|array $propertyId
     * @param int $limit
     * @param int $page The page is currently not managed.
     * @param string $group
     * @return array
     */
    public function findBySubjectItemIdByGroup(
        $subjectItemId,
        $onlyExistingObjectItems = true,
        $propertyId = null,
        $limit = null,
        $page = null,
        $group = null
    ) {
        if (empty($limit) || !in_array($group, array('item_type', 'property'))) {
            return $this->findBySubjectItemId($item, $limit, $page);
        }

        // A two-steps process is used to avoid complex query with multi-limit.
        switch ($group) {
            case 'item_type':
                $select = $this->_findBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId)
                    ->joinLeft(
                        array('ir_items' => $this->_db->Item),
                        'item_relations_relations.object_item_id = ir_items.id',
                        array(
                            'group' => 'ir_items.item_type_id',
                        )
                    );
                $colGroup = 'group';
                break;
            case 'property':
                $select = $this->_findBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId);
                $colGroup = 'property_id';
                break;
        }

        return $this->_fetchLimitByGroup($select, array(), $limit, $colGroup);
    }

    /**
     * Find item relations by object item ID, limited by group.
     *
     * @param integer $objectItemId
     * @param boolean $onlyExistingSubjectItems
     * @param integer|array $propertyId
     * @param int $limit
     * @param int $page The page is currently not managed.
     * @param string $group
     * @return array
     */
    public function findByObjectItemIdByGroup(
        $objectItemId,
        $onlyExistingSubjectItems = true,
        $propertyId = null,
        $limit = null,
        $page = null,
        $group = null
    ) {
        if (empty($limit) || !in_array($group, array('item_type', 'property'))) {
            return $this->findByObjectItemId($item, $limit, $page);
        }

        // A two-steps process is used to avoid complex query with multi-limit.
        switch ($group) {
            case 'item_type':
                $select = $this->_findByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId)
                    ->joinLeft(
                        array('ir_items' => $this->_db->Item),
                        'item_relations_relations.subject_item_id = ir_items.id',
                        array(
                            'group' => 'ir_items.item_type_id',
                        )
                    );
                    $colGroup = 'group';
                    break;
            case 'property':
                $select = $this->_findByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId);
                $colGroup = 'property_id';
                break;
        }

        return $this->_fetchLimitByGroup($select, array(), $limit, $colGroup);
    }

    /**
     * Helper to limit query of objects by group.
     *
     * @param string $sql
     * @param array $params
     * @param integer $limit
     * @param string $colGroup
     * @return array
     */
    protected function _fetchLimitByGroup($sql, $params = array(), $limit = null, $colGroup = 'group')
    {
        $res = $this->getDb()->query($sql, $params);
        $data = $res->fetchAll();

        $limitedData = array();
        foreach ($data as $row) {
            $groupId = (integer) $row[$colGroup] ?: '';
            if (!isset($limitedData[$groupId]) || count($limitedData[$groupId]) < $limit) {
                $limitedData[$groupId][] = $this->recordFromData($row);
            }
        }

        return $limitedData;
    }

    /**
     * Get the total of item relations by subject item ID.
     *
     * @param integer $subjectItemId
     * @param boolean $onlyExistingObjectItems
     * @param integer|array $propertyId
     * @return int
     */
    public function countBySubjectItemId($subjectItemId, $onlyExistingObjectItems = true, $propertyId = null)
    {
        $select = $this->_findBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId);
        return $this->_countRelations($select);
    }

    /**
     * Get the total of item relations by object item ID.
     *
     * @param integer $objectItemId
     * @param boolean $onlyExistingSubjectItems
     * @param integer|array $propertyId
     * @return int
     */
    public function countByObjectItemId($objectItemId, $onlyExistingSubjectItems = true, $propertyId = null)
    {
        $select = $this->_findByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId);
        return $this->_countRelations($select);
    }

    /**
     * Get the total of item relations by group and by subject item ID.
     *
     * @param integer $subjectItemId
     * @param boolean $onlyExistingObjectItems
     * @param integer|array $propertyId
     * @param string $group
     * @return array
     */
    public function countBySubjectItemIdByGroup($subjectItemId, $onlyExistingObjectItems = true, $propertyId = null, $group = null)
    {
        if (!in_array($group, array('item_type', 'property'))) {
            return $this->countBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId);
        }

        switch ($group) {
            case 'item_type':
                $select = $this->_findBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId)
                    ->joinLeft(
                        array('ir_items' => $this->_db->Item),
                        'item_relations_relations.object_item_id = ir_items.id',
                        array(
                            'group' => 'ir_items.item_type_id',
                        )
                    );
                $columns = array('group' => 'ir_items.item_type_id');
                break;
            case 'property':
                $select = $this->_findBySubjectItemId($subjectItemId, $onlyExistingObjectItems, $propertyId);
                $columns = array('property_id' => 'item_relations_relations.property_id');
                break;
        }

        return $this->_countRelationsByGroup($select, $columns);
    }

    /**
     * Get the total of item relations by group and by object item ID.
     *
     * @param integer $objectItemId
     * @param boolean $onlyExistingSubjectItems
     * @param integer|array $propertyId
     * @param string $group
     * @return array
     */
    public function countByObjectItemIdByGroup($objectItemId, $onlyExistingSubjectItems = true, $propertyId = null, $group = null)
    {
        if (!in_array($group, array('item_type', 'property'))) {
            return $this->countByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId);
        }

        switch ($group) {
            case 'item_type':
                $select = $this->_findByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId)
                    ->joinLeft(
                        array('ir_items' => $this->_db->Item),
                        'item_relations_relations.subject_item_id = ir_items.id',
                        array(
                            'group' => 'ir_items.item_type_id',
                        )
                    );
                $columns = array('group' => 'ir_items.item_type_id');
                break;
            case 'property':
                $select = $this->_findByObjectItemId($objectItemId, $onlyExistingSubjectItems, $propertyId);
                $columns = array('property_id' => 'item_relations_relations.property_id');
                break;
        }

        return $this->_countRelationsByGroup($select, $columns);
    }

    /**
     * Prepare the select to find item relations by subject item ID.
     *
     * @param integer $subjectItemId
     * @param boolean $onlyExistingObjectItems
     * @param integer|array $propertyId
     * @return Select
     */
    protected function _findBySubjectItemId($subjectItemId, $onlyExistingObjectItems = true, $propertyId = null)
    {
        $db = $this->_db;
        $select = $this->getSelect()
            ->where('item_relations_relations.subject_item_id = ?', (int) $subjectItemId);
        if ($onlyExistingObjectItems) {
            $select->join(
                array('items' => $db->Item),
                'items.id = item_relations_relations.object_item_id',
                array()
            );
        }
        if ($propertyId) {
            if (is_array($propertyId)) {
                $select
                    ->where('item_relations_relations.property_id IN (?)', array_map('intval', $propertyId));
            }
            // Single property.
            else{
                $select
                    ->where('item_relations_relations.property_id = ?', (int) $propertyId);
            }
        }
        return $select;
    }

    /**
     * Prepare the select to find item relations by object item ID.
     *
     * @param integer $objectItemId
     * @param boolean $onlyExistingSubjectItems
     * @param integer|array $propertyId
     * @return Select
     */
    protected function _findByObjectItemId($objectItemId, $onlyExistingSubjectItems = true, $propertyId = null)
    {
        $db = $this->_db;
        $select = $this->getSelect()
            ->where('item_relations_relations.object_item_id = ?', (int) $objectItemId);
        if ($onlyExistingSubjectItems) {
            $select->join(
                array('items' => $db->Item),
                'items.id = item_relations_relations.subject_item_id',
                array()
            );
        }
        if ($propertyId) {
            if (is_array($propertyId)) {
                $select
                    ->where('item_relations_relations.property_id IN (?)', array_map('intval', $propertyId));
            }
            // Single property.
            else{
                $select
                    ->where('item_relations_relations.property_id = ?', (int) $propertyId);
            }
        }
        return $select;
    }

    /**
     * Find all specified relations.
     *
     * @internal This is a short for findBy().
     *
     * @param integer|array $objectItemId
     * @param integer|array $propertyId
     * @param integer|array $subjectItemId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function findRelations(
        $subjectItemId = null,
        $propertyId = null,
        $objectItemId = null,
        $limit = null,
        $page = null
    ) {
        $params = array();
        if (!is_null($subjectItemId)) {
            $params['subject_item_id'] = is_array($subjectItemId)
                ? array_map('intval', $subjectItemId)
                : (integer) $subjectItemId;
        }
        if (!is_null($propertyId)) {
            $params['property_id'] = is_array($propertyId)
                ? array_map('intval', $propertyId)
                : (integer) $propertyId;
        }
        if (!is_null($objectItemId)) {
            $params['object_item_id'] = is_array($objectItemId)
                ? array_map('intval', $objectItemId)
                : (integer) $objectItemId;
        }
        return $this->findBy($params, $limit, $page);
    }

    /**
     * Get the total of relations from a select.
     *
     * @param Omeka_Db_Select $select
     * @return int
     */
    protected function _countRelations(Omeka_Db_Select $select)
    {
        $select->reset(Zend_Db_Select::COLUMNS);
        $alias = $this->getTableAlias();
        $select->from(array(), "COUNT(DISTINCT($alias.id))");
        $select->reset(Zend_Db_Select::ORDER)->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT)->reset(Zend_Db_Select::LIMIT_OFFSET);
        return $this->getDb()->fetchOne($select);
    }

    /**
     * Get the total of relations from a select, by group.
     *
     * @param Omeka_Db_Select $select
     * @param array $columns Associative array with one key/value.
     * @return array
     */
    protected function _countRelationsByGroup(Omeka_Db_Select $select, $columns)
    {
        $select->reset(Zend_Db_Select::COLUMNS);
        $alias = $this->getTableAlias();
        $colGroup = key($columns);
        $columns['count'] = "COUNT(DISTINCT($alias.id))";
        $select->from(array(), $columns);
        $select->reset(Zend_Db_Select::ORDER)->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT)->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->group($colGroup);
        $result = $this->getDb()->fetchPairs($select);
        if (isset($result[''])) {
            $result[0] = $result[''];
            unset($result['']);
        }
        return $result;
    }
}
