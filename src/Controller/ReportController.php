<?php

/**
 * @file
 * Provide site administrators with a list of all the RSVP List signups so they know who is attending their events.
 */

 namespace Drupal\rsvplist\Controller;

 use Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Database\Database;

 class ReportController extends ControllerBase{

    /**
     * Gets all RSVPs for all nodes.
     * 
     * @return array|null
     */
    protected function load(){
        try {
            //Dynamic Query
            $database = \Drupal::database();
            $select_query = $database->select('rsvplist', 'r');

            //Join the user table, so we can get he entry creator's username.
            $select_query->join('users_field_data', 'u', 'r.uid = u.uid');

            //Join the node table, so we can get the event's name.
            $select_query->join('node_field_data', 'n', 'r.nid = n.nid');
            
            //Select these specific fields for the output.
            $select_query->addField('u', 'name', 'username');
            $select_query->addField('n', 'title');
            $select_query->addField('r', 'mail');

            //Execute and return associative array
            $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
            return $entries;

        } catch (\Exception $e) {
            //Display error
            \Drupal::messenger()->addStatus(
                t('Unable to access database. Try later.')
            );
            return NULL;
        }
    }

    /**
     * Creates the RSVPList report page.
     * 
     * @return array
     */
    public function report(){
        $content = [];

        $content['message'] = [
            '#markup' => t('Below is a list of all Event RSVPs.'),
        ];
        //headers of the table
        $headers = [
            t('Username'),
            t('Event'),
            t('Email'),
        ];

        $table_rows = $this->load(); //load function we created above return an associative array with each row.

        //HTML Table
        $content['table'] = [
            '#type' => 'table',
            '#header' => $headers,
            '#rows' => $table_rows,
            '#empty' => t('No entries available'),
        ];

        //Do not cache this page by setting the maximum time cache to 0 with max-age.
        $content['#cache']['max-age'] = 0;

        //Return render array.
        return $content;
    }
 }