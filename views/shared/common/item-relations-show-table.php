<?php
$provideRelationComments = get_option('item_relations_provide_relation_comments');
$colspan = ($provideRelationComments ? 4 : 3);
$lastVocab = -1;
$thisItemId = $item-> id;

$relVocabShowHide = __("Show / Hide");
$relVocabShowHideAll = __("Show / Hide All");
?>
<script type='text/javascript'>
    var relVocabShowHide = <?php echo json_encode($relVocabShowHide); ?>;
    var relVocabShowHideAll = <?php echo json_encode($relVocabShowHideAll); ?>;
    var isLimited = <?php echo $limit ? 'true' : 'false'; ?>;
</script>
<?php
  if (get_option('item_relations_tables_collapsible')) {
    echo js_tag('item-relations-vocab-toggle');
  }
?>
<table id='relVocabTable'><tbody>
<?php
foreach ($allRelations as $relation) {
    if ($lastVocab != $relation["vocabulary_id"]) {
        $lastVocab = $relation["vocabulary_id"];
        echo "<tr class='relVocabHead' data-vocab='$lastVocab'><th colspan='$colspan'>"
            . "<span title='" . $relation["vocabulary_desc"] . "'>"
            . $relation["vocabulary"]
            . "</span></th></tr>";
    }
    echo "<tr class='relVocabRow relVocab_$lastVocab'>";
    echo "<td>"
        . ($relation['subject_item_id'] == $thisItemId
            ? __('This Item')
            : "<a href='" . url('items/show/' . $relation['subject_item_id']) . "'>" . $relation['subject_item_title'] . "</a>"
        )
        . '</td>';
    echo "<td><strong>" . $relation['relation_text'] . "</strong></td>";
    echo "<td>"
        . ($relation['object_item_id'] == $thisItemId
            ? __('This Item')
            : "<a href='" . url('items/show/' . $relation['object_item_id']) . "'>" . $relation['object_item_title'] . "</a>"
        )
        . '</td>';
    if ($provideRelationComments) {
        echo "<td>(" . $relation['relation_comment'] . ")</td>";
    }
    echo '</tr>';
} # foreach
?>
</tbody></table>
<?php
if ($limit && $totalAllRelations) {
    echo link_to_items_browse(
        __('Browse all %s relations.', $totalAllRelations),
        array(
            'item_relations_item_id' => $item->id,
            'item_relations_clause_part' => 'all',
        ),
        array());
}
