<?php

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version 5
 *
 * @package    LeadsOptin
 * @author     Christopher Bölter <kontakt@boelter.eu>
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

namespace Boelter\LeadsOptin\Handler;

use Haste\Util\StringUtil;
use NotificationCenter\Model\Notification;

/**
 * Provides several function to access leads hooks and send notifications.
 *
 * @package Boelter\LeadsOptin\Handler
 */
class Hook
{
    /**
     * Access the storeLeadsData hook and handle the optin.
     *
     * @param $post
     * @param $form
     * @param $files
     * @param $lead
     * @param $fields
     */
    public function appendOptInData($post, $form, $files, $lead, $fields)
    {
        if (!$form['leadOptIn']) {
            return;
        }

        $token  = md5(uniqid(mt_rand(), true));
        $tstamp = time();
        $set    = array(
            'optin_token'               => $token,
            'optin_notification_tstamp' => $tstamp,
        );

        $database = \Database::getInstance();
        $database->prepare("UPDATE tl_lead %s Where id = ?")->set($set)->execute($lead);

        $formData = array();
        $fields   = $fields->fetchAllAssoc();

        if (!$fields) {
            return;
        }

        foreach ($fields as $field) {
            if (array_key_exists($field['name'], $post) && $post[$field['name']]) {
                $formData[$field['name']] = $post[$field['name']];
            }
        }

        $formTokens = array();
        StringUtil::flatten($formData, 'form', $formTokens);
        unset($formTokens['form']);

        $formTokens['optin_token'] = $token;
        $formTokens['optin_url']   = $this->generateOptInUrl($token, $form);

        $objNotification = Notification::findByPk($form['leadOptInNotification']);
        if (null !== $objNotification) {
            $objNotification->send($formTokens); // Language is optional
        }
    }

    /**
     * Generate the optin target url and pass it back.
     *
     * @param $token
     * @param $form
     *
     * @return string
     */
    private function generateOptInUrl($token, $form)
    {
        $page = $GLOBALS['objPage'];

        if ($form['leadOptInTarget']) {
            $page = \PageModel::findWithDetails($form['leadOptInTarget']);
        }

        $url       = \Environment::get('base') . \Controller::generateFrontendUrl($page->row());
        $parameter = '?token=' . $token;

        return $url . $parameter;
    }
}
