<?php

declare(strict_types=1);

namespace Boelter\LeadsOptin\Cron;

use Boelter\LeadsOptin\Util\Constants;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

#[AsCronJob('daily')]
class PurgeCron
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface|null $contaoCronLogger = null,
    ) {
    }

    public function __invoke(): void
    {
        $leads = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_lead WHERE optin_tstamp = 0 AND optin_notification_tstamp > 0 AND optin_notification_tstamp < ?',
            [time() - Constants::$TOKEN_VALID_PERIOD]
        )
        ;

        $allIds = [];
        $allUploads = [];
        $forms = [];

        foreach ($leads as $lead) {
            $form = $this->connection->fetchAssociative(
                'SELECT * FROM tl_form WHERE id=?',
                [$lead['main_id']]
            );

            if (empty($form)) {
                continue;
            }

            $forms[] = $form;
            $allIds[] = $lead['id'];
            $allUploads[] = $form['leadPurgeUploads'] ? $this->getUploads([$lead['id']]) : [];
        }

        if (empty($allIds)) {
            return;
        }

        $allUploads = array_merge(...$allUploads);

        $deleted = $this->connection->executeStatement('DELETE FROM tl_lead WHERE id IN ('.implode(', ', $allIds).')');
        $this->connection->executeStatement('DELETE FROM tl_lead_data WHERE pid IN ('.implode(', ', $allIds).')');

        /** @var FilesModel $filesModel */
        foreach ($allUploads as $filesModel) {
            try {
                $this->filesystem->remove($filesModel->path);
                $filesModel->delete();
            } catch (IOException) {
                continue;
            }
        }

        if (null !== $this->contaoCronLogger) {
            $this->contaoCronLogger->info(sprintf('Purged %s leads from %s forms.', $deleted, \count($forms)));
        }
    }

    /**
     * @param array<mixed> $leadIds
     *
     * @throws Exception
     *
     * @return array<mixed>
     */
    private function getUploads(array $leadIds): array
    {
        $uploads = [];
        $rows = $this->connection->fetchAllAssociative("
            SELECT *
            FROM tl_lead_data
            WHERE pid IN (?)
              AND value REGEXP '[a-f0-9]{8}-[a-f0-9]{4}-1[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}'
        ", [$leadIds], [ArrayParameterType::INTEGER]);

        foreach ($rows as $row) {
            foreach (StringUtil::deserialize($row['value'], true) as $uuid) {
                if (Validator::isUuid($uuid) && null !== ($filesModel = FilesModel::findByUuid($uuid))) {
                    $uploads[] = $filesModel;
                }
            }
        }

        return $uploads;
    }
}
