<?php
/**
 * Helper to display the Item Relations Form.
 */
class ItemRelations_View_Helper_ItemRelationsForm extends Zend_View_Helper_Abstract
{
    /**
     * Returns the form code to add item relations.
     *
     * @param Item $item
     * @return string Html string.
     */
    public function itemRelationsForm($item)
    {
        $view = $this->view;
        $db = get_db();

        // Prepare list of item types for the select form.
        $sql = "SELECT id, name from {$db->Item_Types} ORDER BY name";
        $itemtypes = $db->fetchAll($sql);
        $itemTypesList = array(
            '-1' => '- ' . __('All') . ' -',
        );
        foreach ($itemtypes as $type) {
            $itemTypesList[$type['id']] = $type['name'];
        }

        $html = $view->partial('common/item-relations-form.php', array(
            'item' => $item,
            'provideRelationComments' => get_option('item_relations_provide_relation_comments'),
            'formSelectProperties' => get_table_options('ItemRelationsProperty'),
            'subjectRelations' => ItemRelationsPlugin::prepareSubjectRelations($item),
            'objectRelations' => ItemRelationsPlugin::prepareObjectRelations($item),
            'itemTypesList' => $itemTypesList,
        ));

        $html .= '<link href="' . css_src('lity.min', 'javascripts/lity') . '" rel="stylesheet">';
        $html .= '<link href="' . css_src('item-relations') . '" rel="stylesheet">';
        $html .= js_tag('lity.min', $dir = 'javascripts/lity');
        $html .= '<script type="text/javascript">var url = ' . json_encode(url('item-relations/lookup/')) . '</script>';
        $html .= js_tag('item-relations');

        return $html;
    }
}
