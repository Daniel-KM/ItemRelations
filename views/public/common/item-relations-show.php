<?php
    $subjectRelations = $objectRelations = $allRelations = false;
    $totalSubjectRelations = $totalObjectRelations = $totalAllRelations = 0;
    $mode = get_option('item_relations_public_display_mode') ?: 'table';
    $limit = get_option('item_relations_public_limit_display');
    if ($mode == 'list-by-item-type') {
        $subjectRelations = ItemRelationsPlugin::prepareSubjectRelations($item, $limit);
        $objectRelations = ItemRelationsPlugin::prepareObjectRelations($item, $limit);
        $totalSubjectRelations = ItemRelationsPlugin::countSubjectRelations($item);
        $totalObjectRelations = ItemRelationsPlugin::countObjectRelations($item);
    }
    else {
        $allRelations = ItemRelationsPlugin::prepareAllRelations($item, $limit);
        $totalAllRelations = ItemRelationsPlugin::countAllRelations($item);
    }
    $noRelations = !$subjectRelations && !$objectRelations && !$allRelations;
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
            'limit' => $limit,
        ));
    endif; ?>
</div>
