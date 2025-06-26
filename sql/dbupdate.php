<#1>
<?php
/**
 * @var $ilDB ilDBInterface
 */

$fields = array(
    'obj_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'api_url_health' => array(
        'type' => 'text',
        'length' => 512,
        'notnull' => false
    ),
    'api_url_info' => array(
        'type' => 'text',
        'length' => 512,
        'notnull' => false
    )
);

if (!$ilDB->tableExists('rep_robj_xobs_data')) {
    $ilDB->createTable("rep_robj_xobs_data", $fields);
    $ilDB->addPrimaryKey("rep_robj_xobs_data", array("obj_id"));

}
?>
