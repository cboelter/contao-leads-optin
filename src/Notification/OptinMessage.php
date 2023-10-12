<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\Notification;


use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use NotificationCenter\Model\Notification;
use Psr\Log\LogLevel;

class OptinMessage
{
    protected $logger;

    public function __construct()
    {
        $this->logger = System::getContainer()->get('monolog.logger.contao');
    }

    /**
     * Custom send method for including file attachments to notification center
     */
    public function send(Notification &$objNotification, array &$arrTokens, string $strLanguage = '', array $attachments = []): array
    {
        if (empty($objMessages = $objNotification->getMessages()))
        {
            $this->logger->log(
                LogLevel::ERROR,
                'Could not find any messages for notification ID ' . $objNotification->id,
                ['contao' => new ContaoContext(__METHOD__, 'ERROR')]
            );
        }

        $arrResult   = [];
        $attachments = [];

        // Custom logic for file attachments
        if (isset($arrTokens['optin_attachments']) && is_array($arrTokens['optin_attachments']))
        {
            $attachments = $arrTokens['optin_attachments'];
        }

        $arrTokens = $this->addTemplateTokens($objNotification, $arrTokens);

        foreach ($objMessages as $objMessage)
        {
            $arrResult[$objMessage->id] = $objMessage->send($arrTokens, $strLanguage, $attachments);
        }

        return $arrResult;
    }

    private function addTemplateTokens(Notification &$objNotification, array $tokens): array
    {
        $templates = StringUtil::deserialize($objNotification->templates, true);

        foreach ($templates as $name)
        {
            try {
                $template = new FrontendTemplate($name);
                $template->setData($tokens);

                $tokens['template_' . substr($name, 13)] = $template->parse();
            } catch (\Exception $e) {
                $this->logger->log(
                    LogLevel::ERROR,
                    'Could not generate token template "' . $name . '"',
                    ['contao' => new ContaoContext(__METHOD__, 'ERROR')]
                );
            }
        }

        return $tokens;
    }
}
