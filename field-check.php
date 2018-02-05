<?php
/**
 * Checks to see if an ACF post group exists within the database.
 * 
 * @since 0.1.0
 */
namespace imimobile;

class Field_Group_Check
{
    function __construct()
    {

    }
    
    function exists($value)
    {
        $value = strtolower($value);
        $exists = false;
        $field_groups = acf_get_field_groups();
        if($field_groups)
        {
            foreach($field_groups as $field_group)
            {
                if($field_group['key'] == $value)
                {
                    $exists = true;
                }
            }
        }
        return $exists;
    }

    function force_sync()
    {
        /**
         * Set up the variables.
         */
        $groups = acf_get_field_groups();
        $sync 	= array();

        /**
         * Exit if there are no field groups.
         */
        if( empty( $groups ) )
            return;

        /**
         * Find all JSON groups that haven't been imported yet.
         */
        foreach( $groups as $group ) {
            
            /**
             * Set up the variables.
             */
            $local 		= acf_maybe_get( $group, 'local', false );
            $modified 	= acf_maybe_get( $group, 'modified', 0 );
            $private 	= acf_maybe_get( $group, 'private', false );
            
            /**
             * Ignore database, PHP and private field groups.
             */
            if( $local !== 'json' || $private ) {
                
                // do nothing
                
            } elseif( ! $group[ 'ID' ] ) {
                
                $sync[ $group[ 'key' ] ] = $group;
                
            } elseif( $modified && $modified > get_post_modified_time( 'U', true, $group[ 'ID' ], true ) ) {
                
                $sync[ $group[ 'key' ] ]  = $group;
            }
        }

        /**
         * Exit if no sync is required.
         */
        if( empty( $sync ) )
            return;

        if( ! empty( $sync ) ) {
            
            /**
             * Set up the variables.
             */
            $new_ids = array();
            
            foreach( $sync as $key => $v ) { //foreach( $keys as $key ) {
                
                /**
                 * Append values.
                 */
                if( acf_have_local_fields( $key ) ) {
                    
                    $sync[ $key ][ 'fields' ] = acf_get_local_fields( $key );
                    
                }
                /**
                 * Import the field groups.
                 */
                $field_group = acf_import_field_group( $sync[ $key ] );
            }
        }
    }
}