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
                $groupId = (int) $subjectRelation['object_item']->item_type_id;
                $subjectRelationsByGroup[$groupId][] = $subjectRelation;
            }
            $objectRelationsByGroup = array();
            foreach ($objectRelations as $objectRelation) {
                $groupId = (int) $objectRelation['subject_item']->item_type_id;
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

$tabIds = array();
$tabLabels = array();
$tabDivs = array();

if (empty($totalSubjectRelations)):
    // No tab.
else:
    foreach ($subjectRelationsByGroup as $groupId => $relations):
        // Avoid an issue when the group id is empty.
        $groupId = (int) $groupId;
        $relationId = str_replace(' ', '_', $groups[$groupId]);
        $tabIds[$relationId] = $groups[$groupId];
        $label = '<a href="#tabs-' . $relationId . '">' . $groups[$groupId];
        if ($isLimitByGroup):
            $label .= ' (' . $totalSubjectRelationsByGroup[$groupId] . ')';
        endif;
        $label .= '</a>';
        $tabLabels[$relationId] = $label;
        $tabcontent = '<div id="tabs-' . $relationId . '">';
        foreach ($relations as $subjectRelation):
            $title = link_to_item($subjectRelation['object_item_title'], array(), 'show', $subjectRelation['object_item']);
            $item = $subjectRelation['object_item'];
            if (metadata($item, 'has files')):
                $thumb = link_to_item(item_image('square_thumbnail', array(), 0, $item));
            else:
                $thumb = '';
            endif;
            $tabcontent .= "<div class='item hentry'><div class='item-img'>$thumb</div><h4>$title</h4></div>\n";
            if ($group != 'property'):
                $tabContent .= '<em>[' . $subjectRelation['relation_text'] . ']</em>' . "\n";
            endif;
        endforeach;
        if ($isLimitByGroup && $groupId):
            $tabcontent .= '<p>';
            switch ($group):
                case 'item_type':
                    $tabcontent .= link_to_items_browse(
                        __('Browse all relations for this item type.'),
                        array(
                            'item_relations_item_id' => $item->id,
                            'item_relations_clause_part' => 'subject',
                            'item_type' => $groupId,
                        ),
                        array());
                    break;
                case 'property':
                    $tabcontent .= link_to_items_browse(
                        __('Browse all relations for this relation type.'),
                        array(
                            'item_relations_item_id' => $item->id,
                            'item_relations_clause_part' => 'subject',
                            'item_relations_property_id' => $groupId,
                        ),
                        array());
                    break;
            endswitch;
            $tabcontent .= '</p>' . "\n";
        endif;
        $tabcontent .= '</div>' . "\n";
        $tabDivs[$relationId] = $tabcontent;
    endforeach;
endif;

if (empty($totalObjectRelations)):
    // No tab.
else:
    foreach ($objectRelationsByGroup as $groupId => $relations):
        // Avoid an issue when the group id is empty.
        $groupId = (int) $groupId;
        $relationId = str_replace(' ', '_', $groups[$groupId]);
        $tabIds[$relationId] = $groups[$groupId];
        $label = '<a href="#tabs-' . $relationId . '">' . $groups[$groupId];
        if ($isLimitByGroup):
            $label .= ' (' . $totalObjectRelationsByGroup[$groupId] . ')';
        endif;
        $label .= '</a>';
        $tabLabels[$relationId] = $label;
        $tabcontent = '<div id="tabs-' . $relationId . '">';
        foreach ($relations as $objectRelation):
            $title = link_to_item($objectRelation['subject_item_title'], array(), 'show', $objectRelation['subject_item']);
            $item = $objectRelation['subject_item'];
            if (metadata($item, 'has files')):
                $thumb = link_to_item(item_image('square_thumbnail', array(), 0, $item));
            else:
                $thumb = '';
            endif;
            $tabcontent .= "<div class='item hentry'><div class='item-img'>$thumb</div><h4>$title</h4></div>";
            if ($group != 'property'):
                $tabContent .= '<em>[' . $objectRelation['relation_text'] . ']</em>';
            endif;
        endforeach;
        if ($isLimitByGroup && $groupId):
            $tabcontent .= '<p>';
            switch ($group):
                case 'item_type':
                    $tabcontent .= link_to_items_browse(
                        __('Browse all relations for this item type.'),
                        array(
                            'item_relations_item_id' => $item->id,
                            'item_relations_clause_part' => 'object',
                            'item_type' => $groupId,
                        ),
                        array());
                    break;
                case 'property':
                    $tabcontent .= link_to_items_browse(
                        __('Browse all relations for this relation type.'),
                        array(
                            'item_relations_item_id' => $item->id,
                            'item_relations_clause_part' => 'object',
                            'item_relations_property_id' => $groupId,
                        ),
                        array());
                    break;
            endswitch;
            $tabcontent .= '</p>';
        endif;
        $tabcontent .= '</div>' . "\n";
        // Avoid to overwrite subject relations with the same name.
        if (isset($tabDivs[$relationId])) {
            $tabDivs[$relationId] .= $tabcontent;
        } else {
            $tabDivs[$relationId] = $tabcontent;
        }
    endforeach;
endif;

// Tabs without javascript.
// See https://css-tricks.com/functional-css-tabs-revisited/
// Feel free to improve the code or use javascript.
?>
<style media="screen" type="text/css">
.tabs {
  position: relative;
  min-height: 400px; /* This part sucks */
  clear: both;
  margin: 25px 0;
  overflow: scroll; /* This part sucks */
}
.tab {
  float: left;
}
.tab label {
  background: #eee;
  padding: 10px;
  border: 1px solid #ccc;
  margin-left: -1px;
  position: relative;
  left: 1px;
}
.tab [type=radio] {
  display: none;
}
.tab-content {
  position: absolute;
  top: 28px;
  left: 0;
  background: white;
  right: 0;
  bottom: 0;
  padding: 20px;
  border: 1px solid #ccc;
}
[type=radio]:checked ~ label {
  background: white;
  border-bottom: 1px solid white;
  z-index: 2;
}
[type=radio]:checked ~ label ~ .tab-content {
  z-index: 1;
}
#tabsitemrel .item.hentry {
    min-height: 75px;
    margin-top: 1em;
    margin-bottom: 1em;
    padding-bottom: 0;
    min-height: 100px;
    clear: both;
    margin-bottom: 78px;
    margin-top: 52px;
}
</style>
<div id="item-relations-display-item-relations" class="tabs">
<?php foreach($tabIds as $tabId => $tabLabel): ?>
   <div id="tabsitemrel" class="tab">
       <input type="radio" id="tab-<?php echo $tabId; ?>" name="tab-group-1" checked>
       <label for="tab-<?php echo $tabId; ?>"><?php echo $tabLabel; ?></label>
       <div class="tab-content">
           <?php echo $tabDivs[$tabId]; ?>
       </div>
   </div>
<?php endforeach; ?>
</div>
<?php
if ($limit):
    if ($totalSubjectRelations):
        echo '<p>' . link_to_items_browse(
            __('Browse all %s subject relations.', $totalSubjectRelations),
            array(
                'item_relations_item_id' => $item->id,
                'item_relations_clause_part' => 'subject',
            ),
            array()) . '</p>';
    endif;
    if ($totalObjectRelations):
        echo '<p>' . link_to_items_browse(
            __('Browse all %s object relations.', $totalObjectRelations),
            array(
                'item_relations_item_id' => $item->id,
                'item_relations_clause_part' => 'object',
            ),
            array()) . '</p>';
    endif;
endif;
?>
