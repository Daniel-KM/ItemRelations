<?php
    $adminSidebarOrMaincontent = get_option('item_relations_admin_sidebar_or_maincontent');
    $h4h2 = $adminSidebarOrMaincontent == "maincontent" ? '2' : '4';
    $relationsclass = $adminSidebarOrMaincontent == 'maincontent' ? 'element_set' : 'item-relations panel';

    $subjectRelations = $objectRelations = $allRelations = false;
    $totalSubjectRelations = $totalObjectRelations = $totalAllRelations = 0;
    $mode = get_option('item_relations_admin_display_mode') ?: 'table';
    $limit = get_option('item_relations_admin_limit_display');
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
    $noRelations = $totalSubjectRelations + $totalObjectRelations + $totalAllRelations == 0;
?>
<div class="<?php echo $relationsclass; ?>">
    <?php echo '<h' . $h4h2 . '>' . __('Item Relations') . '</h' . $h4h2 . '>'; ?>
    <div>
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
</div>
