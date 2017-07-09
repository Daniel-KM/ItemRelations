<?php
$provideRelationComments = get_option('item_relations_provide_relation_comments');

// Reorder relations by group.
// TODO Factorize with other views.
$groups = array();
switch ($group) {
    case 'item_type':
        if ($isLimitByGroup) {
            $subjectRelationsByGroup = &$subjectRelations;
            $objectRelationsByGroup = &$objectRelations;
        } else {
            $subjectRelationsByGroup = array();
            foreach ($subjectRelations as $subjectRelation) {
                $groupId = (integer) $subjectRelation['object_item']->item_type_id;
                $subjectRelationsByGroup[$groupId][] = $subjectRelation;
            }
            $objectRelationsByGroup = array();
            foreach ($objectRelations as $objectRelation) {
                $groupId = (integer) $objectRelation['subject_item']->item_type_id;
                $objectRelationsByGroup[$groupId][] = $objectRelation;
            }
        }

        // Prepare the list of types with one query.
        foreach (get_records('ItemType', array(), 0) as $itemType) {
            $groups[$itemType->id] = $itemType->name;
        }
        $groups[0] = __('No Item Type');
        break;

    case 'property':
        if ($isLimitByGroup) {
            $subjectRelationsByGroup = &$subjectRelations;
            $objectRelationsByGroup = &$objectRelations;
            foreach ($subjectRelations as $groupId => $subjectRelationsArray) {
                foreach ($subjectRelationsArray as $subjectRelation) {
                    $relation = $subjectRelation['relation_property'];
                    $groups[$relation] = $subjectRelation['relation_text'];
                }
            }
            foreach ($objectRelations as $groupId => $objectRelationsArray) {
                foreach ($objectRelationsArray as $objectRelation) {
                    $relation = $objectRelation['relation_property'];
                    $groups[$relation] = $objectRelation['relation_text'];
                }
            }
        } else {
            $subjectRelationsByGroup = array();
            foreach ($subjectRelations as $subjectRelation) {
                $relation = $subjectRelation['relation_property'];
                $subjectRelationsByGroup[$relation][] = $subjectRelation;
                $groups[$relation] = $subjectRelation['relation_text'];
            }
            $objectRelationsByGroup = array();
            foreach ($objectRelations as $objectRelation) {
                $relation = $objectRelation['relation_property'];
                $objectRelationsByGroup[$relation][] = $objectRelation;
                $groups[$relation] = $objectRelation['relation_text'];
            }
        }
        break;
}
?>
<h3><?php echo __('Relation from this item'); ?></h3>
<?php if (empty($totalSubjectRelations)): ?>
<p><?php echo __('This item has no relation to other records.'); ?></p>
<?php else: ?>
<ul>
    <?php foreach ($subjectRelationsByGroup as $groupId => $relations):
        // Avoid an issue when the group id is empty.
        $groupId = (integer) $groupId;
    ?>
        <li><?php
            echo $groups[$groupId];
            if ($isLimitByGroup):
                echo ' (' . $totalSubjectRelationsByGroup[$groupId] . ')';
            endif; ?>
            <ul>
                <?php foreach ($relations as $subjectRelation): ?>
                <li>
                    <?php echo link_to_item($subjectRelation['object_item_title'], array(), 'show', $subjectRelation['object_item']); ?>
                    <?php if ($group != 'property'): ?>
                    <em>[<?php echo $subjectRelation['relation_text']; ?>]</em>
                    <?php endif; ?>
                    <?php
                    if ($provideRelationComments && $subjectRelation['relation_comment']):
                        echo '(' . $subjectRelation['relation_comment'] . ')';
                    endif;
                    ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            if ($isLimitByGroup && $groupId):
                switch ($group):
                    case 'item_type':
                        echo link_to_items_browse(
                            __('Browse all relations for this item type.'),
                            array(
                                'item_relations_item_id' => $item->id,
                                'item_relations_clause_part' => 'subject',
                                'item_type' => $groupId,
                            ),
                            array());
                        break;
                    case 'property':
                        echo link_to_items_browse(
                            __('Browse all relations for this relation type.'),
                            array(
                                'item_relations_item_id' => $item->id,
                                'item_relations_clause_part' => 'subject',
                                'item_relations_property_id' => $groupId,
                            ),
                            array());
                        break;
                endswitch;
            endif;
            ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php
if ($limit):
    echo link_to_items_browse(
        __('Browse all %s subject relations.', $totalSubjectRelations),
        array(
            'item_relations_item_id' => $item->id,
            'item_relations_clause_part' => 'subject',
        ),
        array());
endif;
?>
<?php endif; ?>

<h3><?php echo __('Relations to this item'); ?></h3>
<?php if (empty($totalObjectRelations)): ?>
<p><?php echo __('No record is related to this item.'); ?>
<?php else: ?>
<ul>
    <?php foreach ($objectRelationsByGroup as $groupId => $relations):
        // Avoid an issue when the group id is empty.
        $groupId = (integer) $groupId;
    ?>
        <li><?php
            echo __($groups[$groupId]);
            if ($isLimitByGroup):
                echo ' (' . $totalObjectRelationsByGroup[$groupId] . ')';
            endif; ?>
            ?>
            <ul>
                <?php foreach ($relations as $objectRelation): ?>
                <li>
                    <?php echo link_to_item($objectRelation['subject_item_title'], array(), 'show', $objectRelation['subject_item']); ?>
                    <?php if ($group != 'property'): ?>
                    <em>[<?php echo $objectRelation['relation_text']; ?>]</em>
                    <?php endif; ?>
                    <?php
                    if ($provideRelationComments && $objectRelation['relation_comment']):
                        echo '(' . $objectRelation['relation_comment'] . ')';
                    endif;
                    ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            if ($isLimitByGroup && $groupId):
                switch ($group):
                    case 'item_type':
                        echo link_to_items_browse(
                            __('Browse all relations for this item type.'),
                            array(
                                'item_relations_item_id' => $item->id,
                                'item_relations_clause_part' => 'object',
                                'item_type' => $groupId,
                            ),
                            array());
                        break;
                    case 'property':
                        echo link_to_items_browse(
                            __('Browse all relations for this relation type.'),
                            array(
                                'item_relations_item_id' => $item->id,
                                'item_relations_clause_part' => 'object',
                                'item_relations_property_id' => $groupId,
                            ),
                            array());
                        break;
                endswitch;
            endif;
            ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php
if ($limit):
    echo link_to_items_browse(
        __('Browse all %s object relations.', $totalObjectRelations),
        array(
            'item_relations_item_id' => $item->id,
            'item_relations_clause_part' => 'object',
        ),
        array());
endif;
?>
<?php endif; ?>
