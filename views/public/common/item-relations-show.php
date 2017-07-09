<?php
    $subjectRelations = $objectRelations = $allRelations = false;
    $totalSubjectRelations = $totalObjectRelations = $totalAllRelations = 0;
    $totalSubjectRelationsByGroup = $totalObjectRelationsByGroup = 0;
    $group = null;
    $mode = get_option('item_relations_public_display_mode') ?: 'table';
    $limit = get_option('item_relations_public_limit_display');
    $isLimitByGroup = false;
    if (in_array($mode, array(
        'list-by-item-type',
        'list-by-property',
        'tabs-by-item-type',
        'tabs-by-property',
    ))) {
        $group = $mode == 'list-by-property' ? 'property' : 'item_type';
        $subjectRelations = ItemRelationsPlugin::prepareSubjectRelations($item, $limit);
        $objectRelations = ItemRelationsPlugin::prepareObjectRelations($item, $limit);
        $totalSubjectRelations = ItemRelationsPlugin::countSubjectRelations($item);
        $totalObjectRelations = ItemRelationsPlugin::countObjectRelations($item);
        $mode = strpos($mode, 'tabs') !== false ? 'tabs' : 'list-group';
    }
    elseif (in_array($mode, array(
        'list-by-item-type-limit',
        'list-by-property-limit',
        'tabs-by-item-type-limit',
        'tabs-by-property-limit',
    ))) {
        $group = strpos($mode, 'property') !== false ? 'property' : 'item_type';
        $subjectRelations = ItemRelationsPlugin::prepareSubjectRelationsLimitByGroup($item, $limit, null, $group);
        $objectRelations = ItemRelationsPlugin::prepareObjectRelationsLimitByGroup($item, $limit, null, $group);
        $totalSubjectRelationsByGroup = ItemRelationsPlugin::countSubjectRelationsByGroup($item, $group);
        $totalObjectRelationsByGroup = ItemRelationsPlugin::countObjectRelationsByGroup($item, $group);
        $totalSubjectRelations = ItemRelationsPlugin::countSubjectRelations($item);
        $totalObjectRelations = ItemRelationsPlugin::countObjectRelations($item);
        $mode = strpos($mode, 'tabs') !== false ? 'tabs' : 'list-group';
        $isLimitByGroup = true;
    }
    else {
        $allRelations = ItemRelationsPlugin::prepareAllRelations($item, $limit);
        $totalAllRelations = ItemRelationsPlugin::countAllRelations($item);
    }
    $noRelations = $totalSubjectRelations + $totalObjectRelations + $totalAllRelations == 0;
?>
<div id="item-relations-display-item-relations">
    <h2><?php echo __('Item Relations'); ?></h2>
    <?php if ($noRelations): ?>
    <p><?php echo __('This item has no relations.'); ?></p>
    <?php else:
        echo common('item-relations-show-' . $mode, array(
            'item' => $item,
            'subjectRelations' => $subjectRelations,
            'objectRelations' => $objectRelations,
            'allRelations' => $allRelations,
            'totalSubjectRelations' => $totalSubjectRelations,
            'totalObjectRelations' => $totalObjectRelations,
            'totalAllRelations' => $totalAllRelations,
            'totalSubjectRelationsByGroup' => $totalSubjectRelationsByGroup,
            'totalObjectRelationsByGroup' => $totalObjectRelationsByGroup,
            'limit' => $limit,
            'isLimitByGroup' => $isLimitByGroup,
            'group' => $group,
        ));
    endif; ?>
</div>
