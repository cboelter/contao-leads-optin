<?php

/**
 * The leads optin extension allows you to store leads with double optin function.
 *
 * PHP version ^7.4 || ^8.0
 *
 * @package    LeadsOptin
 * @author     Christopher Bölter <kontakt@boelter.eu>
 * @copyright  Christopher Bölter 2017
 * @license    LGPL.
 * @filesource
 */

namespace Boelter\LeadsOptin\Handler;

use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Haste\Util\StringUtil;
use NotificationCenter\Model\Notification;
use PageModel;

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
        if (!isset($form['leadOptIn']) || !$form['leadOptIn'])
        {
            return;
        }

        $token  = md5(uniqid(mt_rand(), true));
        $set    = [
            'optin_token'               => $token,
            'optin_notification_tstamp' => time()
        ];

        $database = Database::getInstance();
        $database->prepare("UPDATE tl_lead %s Where id = ?")
            ->set($set)
            ->execute($lead);

        $formData = [];
        $fields   = $fields->fetchAllAssoc();

        if (null === $fields)
        {
            return;
        }

        foreach ($fields as $field)
        {
            if (array_key_exists($field['name'], $post) && $post[$field['name']])
            {
                $formData[$field['name']] = $post[$field['name']];
            }
        }

        $tokens = [];
        StringUtil::flatten($formData, 'lead', $tokens);
        unset($tokens['form']);

        $tokens['optin_token'] = $token;
        $tokens['optin_url']   = $this->generateOptInUrl($token, $form);

        $objNotification = Notification::findByPk($form['leadOptInNotification']);
        if (null !== $objNotification)
        {
            $objNotification->send($tokens);
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
    private function generateOptInUrl($token, $form): string
    {
        $page = $GLOBALS['objPage'];

        if ($form['leadOptInTarget'])
        {
            $page = PageModel::findWithDetails($form['leadOptInTarget']);
        }

        $url       = Environment::get('base') . Controller::generateFrontendUrl($page->row());
        $parameter = '?token=' . $token;

        return $url . $parameter;
    }
}
