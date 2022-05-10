<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Taskrouter\V1\Workspace\TaskQueue;

use Twilio\ListResource;
use Twilio\Version;

class TaskQueueStatisticsList extends ListResource {
    /**
     * Construct the TaskQueueStatisticsList
     *
     * @param Version $version Version that contains the resource
     * @param string $workspaceSid The SID of the Workspace that contains the
     *                             TaskQueue
     * @param string $taskQueueSid The SID of the TaskQueue from which these
     *                             statistics were calculated
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueue\TaskQueueStatisticsList
     */
    public function __construct(Version $version, $workspaceSid, $taskQueueSid) {
        parent::__construct($version);

        // Path Solution
        $this->solution = array('workspaceSid' => $workspaceSid, 'taskQueueSid' => $taskQueueSid, );
    }

    /**
     * Constructs a TaskQueueStatisticsContext
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueue\TaskQueueStatisticsContext
     */
    public function getContext() {
        return new TaskQueueStatisticsContext(
            $this->version,
            $this->solution['workspaceSid'],
            $this->solution['taskQueueSid']
        );
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString() {
        return '[Twilio.Taskrouter.V1.TaskQueueStatisticsList]';
    }
}