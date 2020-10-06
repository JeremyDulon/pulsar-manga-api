<?php


namespace App\Service;


use JMS\JobQueueBundle\Entity\Job;

class ConsoleService
{
    /**
     * @param string $consoleFunction
     * @param array $params
     * @param null $dependentJob
     * @param int $priority
     * @param string $queue
     * @return Job
     */
    public static function createConsoleJob(string $consoleFunction, array $params, $dependentJob = null, $priority = Job::PRIORITY_HIGH, $queue = Job::DEFAULT_QUEUE) {
        if (!is_array($params)) {
            $params = [$params];
        }

        $job = new Job($consoleFunction, $params, true, $queue, $priority);
        if (!is_null($dependentJob)) {
            $job->addDependency($dependentJob);
        }

        return $job;
    }
}
