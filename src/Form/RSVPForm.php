<?php

/**
 * @file
 * A form to collet an email address for RSVP details
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId(){
        return 'rsvplist_email_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state){
        // Attempt to get the fully loaded node object of the viewed page.
        $node = \Drupal::routeMatch()->getParameter('node');

        if (!(is_null($node))){
            $nid = $node->id();
        }
        else{
            $nid = 0;
        }

        // Establish the $form render array. It has an email text field,
        // a submit button, and a hidden field containing the node ID.
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Email address'),
            '#size' => 25,
            '#description' => t("We will send updates to the email adress you provide."),
            '#required' => TRUE,
        ];
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => t('RSVP'),
        ];
        $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $nid,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateform(array &$form, FormStateInterface $form_state){
        $value = $form_state->getValue('email');
        if ( !(\Drupal::service('email.validator')->isValid($value)) ){
            $form_state->setErrorByName('email',
            $this->t('It appears that %mail is not a valid email. Try again.',
            ['%mail'=> $value]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){
        // $submitted_email = $form_state->getValue('email');
        // $this->messenger()->addMessage(t("The form is working! You entered @entry.",
        // ['@entry' => $submitted_email]));

        try {
            //Get current user ID
            $uid = \Drupal::currentUser()->id();

            //Obtain values as entered in the Form
            $nid = $form_state->getValue('nid');
            $email = $form_state->getValue('email');

            $current_time= \Drupal::time()->getRequestTime();

            //Save the values to the database
            //Build a query builder object
            $query = \Drupal::database()->insert('rsvplist');

            //Specify the fields that the query will insert into
            $query->fields([
                'uid',
                'nid',
                'mail',
                'created',
            ]);

            //Set the values in the same order
            $query->values([
                $uid,
                $nid,
                $email,
                $current_time,
            ]);

            //Execute the query
            $query->execute();

            //Display a success message
            \Drupal::messenger()->addMessage(
                t('Thank your for your RSVP, you are on the list for the event!')
            );

        } catch (\Exception $e) {
            \Drupal::messenger()->addError(
                t('Unable to save RSVP due to DDBB error. Please try again.')
            );
        }
    }
}