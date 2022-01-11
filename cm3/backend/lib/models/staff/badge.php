<?php

namespace CM3_Lib\models\staff;

use CM3_Lib\database\Column as cm_Column;

class badge extends CM3_Lib\database\Table
{
    protected cm_eventinfo_db $eventinfo_db;
    protected cm_staff_badge_types_db $staff_badgetypes_db;
    protected function setupTableDefinitions(): void
    {
        $this->eventinfo_db = new cm_eventinfo_db($this->cm_db);
        $this->staff_badgetypes_db = new cm_staff_badge_types_db($this->cm_db);
        $this->TableName = 'Staff_Badges';
        $this->ColumnDefs = array(
            'id'            => new cm_Column('BIGINT', null, false, true, false, true, null, true),
            'badge_type_id' => new cm_Column('INT', null, false, false, false, false),
            'contact_id'    => new cm_Column('BIGINT', null, false, false, false, false),
            'display_id'    => new cm_Column('INT', null, true),
            'hidden'        => new cm_Column('BOOLEAN', null, false, defaultValue: 'false'),
            'uuid_raw'      => new cm_Column('BINARY', 16, false, false, true, false, '(UUID_TO_BIN(UUID()))'),
            'uuid'          => new cm_Column('CHAR', 36, null, false, false, false, null, false, 'GENERATED ALWAYS as (BIN_TO_UUID(`uuid_raw`)) VIRTUAL'),
            'real_name'     => new cm_Column('VARCHAR', '500', false),
            'fandom_name'   => new cm_Column('VARCHAR', '255', true),
            'name_on_badge' => new cm_Column(
                'ENUM',
                array(
                    'Fandom Name Large, Real Name Small',
                    'Real Name Large, Fandom Name Small',
                    'Fandom Name Only',
                    'Real Name Only'
                ),
                false
            ),
            'date_of_birth'      => new cm_Column('DATE', null, false),
            'ice_name'           => new cm_Column('VARCHAR', '255', true),
            'ice_relationship'   => new cm_Column('VARCHAR', '255', true),
            'ice_email_address'  => new cm_Column('VARCHAR', '255', true),
            'ice_phone_number'   => new cm_Column('VARCHAR', '255', true),
            'time_printed'       => new cm_Column('TIMESTAMP', null, true),
            'time_checked_in'    => new cm_Column('TIMESTAMP', null, true),

            'application_status' => new cm_Column(
                'ENUM',
                array(
                    'InProgress', //Draft
                    'Submitted',  //Newly submitted
                    'Cancelled',  //Applicant self-cancelled
                    'Rejected',   //Staff rejected
                    'Waitlisted', //Waitlisted for consideration
                    'Onboarding', //Accepted, onboarding in progress
                    'Active',     //Accepted, active staff
                    'Terminated', //No longer welcome
                ),
                false
            ),

                /* Payment Info */
            'payment_badge_price'	=> new cm_Column('DECIMAL', '7,2', false),
            'payment_txn_id'		=> new cm_Column('CHAR', 36, null, customPostfix: 'CHARACTER SET ascii'),
            'payment_txn_id_hist'	=> new cm_Column('VARCHAR', 740, null, customPostfix: 'CHARACTER SET ascii'),
            'payment_status'		=> new cm_Column(
                'ENUM',
                array(
                    'NotStarted',
                    'Incomplete',
                    'Cancelled',
                    'Rejected',
                    'Completed',
                    'Refunded',
                    'RefundedInPart',
                ),
                false
            ),

            'date_created'	=> new cm_Column('TIMESTAMP', null, false, false, false, false, 'CURRENT_TIMESTAMP'),
            'date_modified'	=> new cm_Column('TIMESTAMP', null, false, false, false, false, 'CURRENT_TIMESTAMP', false, 'ON UPDATE CURRENT_TIMESTAMP'),
            'notes'			=> new cm_Column('TEXT', null, true)
        );
        $this->IndexDefs = array();
        $this->PrimaryKeys = array('id'=>false);
        $this->DefaultSearchColumns = array('id','display_id','first_name','last_name');
        $this->Views = array(
            'default' => new cm_View(
                array(
                        new cm_SelectColumn('display_id', EncapsulationFunction: 'concat(\'S\' , ?)', Alias: 'ID'),
                        new cm_SelectColumn('real_name'),
                        new cm_SelectColumn('fandom_name'),
                        new cm_SelectColumn('name', Alias: 'Badge Type', JoinedTableAlias: 'bt'),
                        new cm_SelectColumn('application_status'),
                        new cm_SelectColumn('payment_status'),
                        new cm_SelectColumn('time_printed'),
                        new cm_SelectColumn('time_checked_in')
                    ),
                array(
                       new cm_Join(
                           $this->staff_badgetypes_db,
                           array('badge_type_id'=>'id'),
                           'INNER',
                           alias: 'bt',
                           subQSelectColumns: array(
                               new cm_SelectColumn('id'),
                               new cm_SelectColumn('name')
                           ),
                           subQSearchTerms: array(
                               $this->eventinfo_db->GetSearchTerm()
                           )
                       )
                    )
            )
        );
    }
}
