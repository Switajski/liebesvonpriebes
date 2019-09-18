<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class LastJob
 */
class LastJob
{
    use SingletonTrait;

    /**
     * Initialize Syncstatus
     */
    protected function init()
    {
    }

    /**
     * @param int $hours
     * @return false|stdClass[]
     */
    public function getRepeatedJobs($hours)
    {
        $result = Shop::DB()->query(
            "SELECT kJob, nJob, dErstellt
                FROM tlastjob
                WHERE cType = 'RPT'
                    AND (dErstellt = '0000-00-00 00:00:00'
                        OR DATE_ADD(dErstellt, INTERVAL " . (int)$hours . " HOUR) < NOW())", 2
        );

        return $result === 0 ? false : $result;
    }

    /**
     * @return false|stdClass[]
     */
    public function getStdJobs()
    {
        $result = Shop::DB()->selectAll(
            'tlastjob',
            ['cType', 'nFinished'],
            ['STD', 1],
            'kJob, nJob, cJobName, dErstellt',
            'dErstellt'
        );

        return $result === 0 ? false : $result;
    }

    /**
     * @param int $nJob
     * @return false|stdClass
     */
    public function getJob($nJob)
    {
        $result = Shop::DB()->select('tlastjob', 'nJob', (int)$nJob);

        return $result === 0 ? false : $result;
    }

    /**
     * @param int $nJob
     * @param string $cJobName
     * @return stdClass
     */
    public function run($nJob, $cJobName = null)
    {
        $job = $this->getJob($nJob);

        if (!isset($job) || $job === false) {
            $job = (object)[
                'cType'     => 'STD',
                'nJob'      => (int)$nJob,
                'cJobName'  => $cJobName,
                'nCounter'  => 1,
                'dErstellt' => date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ];

            $job->kJob = Shop::DB()->insert('tlastjob', $job);
        } else {
            $job->nCounter++;
            $job->dErstellt = date('Y-m-d H:i:s');

            Shop::DB()->update('tlastjob', 'kJob', $job->kJob, $job);
        }

        return $job;
    }

    /**
     * @param int $nJob
     * @return bool
     */
    public function restartJob($nJob)
    {
        $job = (object)[
            'nCounter'  => 0,
            'dErstellt' => date('Y-m-d H:i:s'),
            'nFinished' => 0,
        ];

        return Shop::DB()->update('tlastjob', 'nJob', (int)$nJob, $job);
    }

    /**
     * @param int|null $nJob
     * @return void
     */
    public function finishStdJobs($nJob = null)
    {
        $keys    = ['cType', 'nFinished'];
        $keyVals = ['STD', 0];

        if (!empty($nJob)) {
            $keys[]    = 'nJob';
            $keyVals[] = $nJob;
        }

        Shop::DB()->update('tlastjob', $keys, $keyVals, (object)['nFinished' => 1]);

        $keyVals[1] = 1;
        $jobs       = $this->getStdJobs();

        if (is_array($jobs)) {
            foreach ($jobs as $job) {
                $fileName   = PFAD_ROOT . PFAD_DBES . $job->cJobName . '.inc.php';
                $finishProc = $job->cJobName . '_Finish';

                if (is_file($fileName)) {
                    require_once $fileName;

                    if (function_exists($finishProc)) {
                        $finishProc();
                    }
                }
            }
        }

        Shop::DB()->delete('tlastjob', $keys, $keyVals);
    }
}
