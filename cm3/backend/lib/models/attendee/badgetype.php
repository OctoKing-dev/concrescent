<?php

namespace CM3_Lib\models\attendee;

use CM3_Lib\database\Column as cm_Column;

class badgetype extends \CM3_Lib\database\Table
{
    use \CM3_Lib\database\orderableTrait;
    protected function setupTableDefinitions(): void
    {
        $this->TableName = 'Attendee_Badge_Types';
        $this->ColumnDefs = array(
            'id' 			=> new cm_Column('INT', null, false, true, false, true, null, true),
            'event_id'		=> new cm_Column('INT', null, false, false, false, true),
            'active'        => new cm_Column('BOOLEAN', null, false, defaultValue: 'false'),
            'display_order' => new cm_Column('INT', null, false),
            'name'          => new cm_Column('VARCHAR', '255', false),
            'description'   => new cm_Column('TEXT', null, true),
            'rewards'       => new cm_Column('TEXT', null, true),
            'price'         => new cm_Column('DECIMAL', '7,2', false),
            'payable_onsite'=> new cm_Column('BOOLEAN', null, false, defaultValue: 'false'),
            'quantity'      => new cm_Column('INT', null, true),
            'start_date'	=> new cm_Column('DATE', null, true),
            'end_date'  	=> new cm_Column('DATE', null, true),
            'min_age'   	=> new cm_Column('INT', null, true),
            'max_age'     	=> new cm_Column('INT', null, true),
            'active_override_code' => new cm_Column('VARCHAR', '255', true),
            'date_created'	=> new cm_Column('TIMESTAMP', null, false, false, false, false, 'CURRENT_TIMESTAMP'),
            'date_modified'	=> new cm_Column('TIMESTAMP', null, false, false, false, false, 'CURRENT_TIMESTAMP', false, 'ON UPDATE CURRENT_TIMESTAMP'),
            'notes'			=> new cm_Column('TEXT', null, true),
            //Generated columns
            'dates_available' => new cm_Column('VARCHAR', '50', null, customPostfix: 'GENERATED ALWAYS as (CASE `start_date` IS NULL WHEN true THEN CASE end_date IS NULL WHEN true THEN \'Always\' ELSE CONCAT( \'Until \' ,`end_date`) END ELSE CASE end_date IS NULL WHEN true THEN CONCAT ( \'From \' ,`start_date` ) ELSE CONCAT ( `end_date` ,\' to \' ,`end_date` ) END END ) VIRTUAL'),
        );
        $this->IndexDefs = array();
        $this->PrimaryKeys = array('id'=>false);
        $this->DefaultSearchColumns = array('id','active','display_order','name','price','quantity','dates_available');

        //OrderableTrait defs
        $this->orderColumn = 'display_order';
        $this->orderGroupColumns = ['event_id'];
    }

    public function verifyBadgeTypeBelongsToEvent(int $id, int $event_id)
    {
        $bt = $this->GetByID($id, array('event_id'));
        if ($bt === false) {
            return false;
        }
        if ($bt['event_id'] != $event_id) {
            return false;
        }
        return true;
    }
}
