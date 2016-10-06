<?php

namespace Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\AppBundle\Model\Phabulous;

class GanttController extends Controller
{
    /**
     * This does not Honor WorkTimes
     *
     * @param $ganttTaskStartDateTS
     * @param $ganttTaskDuration
     * @param $ganttTaskEndDateTS
     *
     * @deprecated
     */
    protected function reCalculateDates(&$ganttTaskStartDateTS, &$ganttTaskDuration, &$ganttTaskEndDateTS) {
        switch (true) {
            case is_null($ganttTaskStartDateTS):
                $ganttTaskStartDateTS = strtotime(sprintf('- %s %s', $ganttTaskDuration, Phabulous::MANIPHEST_ESTIMATE_UNIT), $ganttTaskEndDateTS);
                break;
            case is_null($ganttTaskEndDateTS):
                $ganttTaskEndDateTS = strtotime(sprintf('+ %s %s', $ganttTaskDuration, Phabulous::MANIPHEST_ESTIMATE_UNIT), $ganttTaskStartDateTS);
                break;
            case is_null($ganttTaskDuration):
            default:
                $ganttTaskDuration = $ganttTaskEndDateTS - $ganttTaskStartDateTS;
                switch (strtoupper(Phabulous::MANIPHEST_ESTIMATE_UNIT)) {
                    case 'DAY':
                        $ganttTaskDuration /= 24;
                    case 'HOUR':
                        $ganttTaskDuration /= 60;
                    case 'MINUTE':
                        $ganttTaskDuration /= 60;
                }
                break;
        }
    }

    public function dataAction()
    {
        // Data
        $ganttTasks = [];
        $ganttLinks = [];
        $phacilityUsersJson = $this->forward('AppBundle:Phacility/Search:people')->getContent();
        $phacilityTasksJson = $this->forward('AppBundle:Phacility/Search:maniphest')->getContent();
        $phacilityUsers = json_decode($phacilityUsersJson, TRUE);
        $phacilityTasks = json_decode($phacilityTasksJson, TRUE);
        $phacilityUrl = $this->getParameter('phacility_url');

        // Helpers
        $phacilityFindById = function($phid, $phacilityItems) {
            foreach($phacilityItems['data'] as $phUser) {
                if ($phid === $phUser['phid']) {
                    return $phUser;
                }
            }
        };

        // Tasks
        foreach ($phacilityTasks['data'] as $phTask) {
            // Gantt Task
            $ganttTask = [];

            // Status
            $ganttTaskIsOpen = 'open' === $phTask['fields']['status']['value'];

            // Dates & Estimation
            $ganttTaskDuration = $phTask['fields'][Phabulous::MANIPHEST_ESTIMATE] ?: 0;
            $ganttTaskStartDate = null;
            $ganttTaskEndDate = null;
            if ($ganttTaskStartDateTS = $phTask['fields'][Phabulous::MANIPHEST_START_DATE]) {
                $ganttTaskStartDate = date('Y-m-d', $ganttTaskStartDateTS);
            }
            if ($ganttTaskEndDateTS = $phTask['fields'][Phabulous::MANIPHEST_END_DATE]) {
                $ganttTaskEndDate = date('Y-m-d', $ganttTaskEndDateTS);
            }

            // Progress
            $ganttTaskProgress = $phTask['fields'][Phabulous::MANIPHEST_PROGRESS] ?: 0;

            // UnScheduled Tasks
            $ganttTaskRequiredDates = [
                $ganttTaskStartDateTS,
                $ganttTaskEndDateTS,
                $ganttTaskDuration,
            ];
            $ganttTaskRequiredDates = array_filter($ganttTaskRequiredDates);
            if ( $ganttTask['_unscheduled'] = count($ganttTaskRequiredDates) !== 2 ) {
                continue;
            }

            // Overdue
            $ganttTask['overdue'] = !$ganttTask['_unscheduled'] &&
                $ganttTaskIsOpen &&
                strtotime(sprintf('+ %s %s', $ganttTaskDuration, Phabulous::MANIPHEST_ESTIMATE_UNIT), $ganttTaskStartDateTS) < time();

            $ganttTask['id'] = $phTask['phid'];
            $ganttTask['text'] = $phTask['fields']['name'];
            $ganttTask['description'] = $phTask['fields']['name'];
            $ganttTask['start_date'] = $ganttTaskStartDate;
            $ganttTask['duration'] = $ganttTaskDuration;
            $ganttTask['end_date'] = $ganttTaskEndDate;
            $ganttTask['progress'] = $ganttTaskProgress / 100;
            $ganttTask['open'] = $ganttTaskIsOpen;
            $ganttTask['holder'] = $phacilityFindById($phTask['fields']['ownerPHID'], $phacilityUsers)['fields']['realName'] ?: '';
            $ganttTask['uri'] = sprintf('%s/T%s', $phacilityUrl, $phTask['id']);
            $ganttTask['tid'] = $phTask['id'];
            $ganttTask['priority'] = $phTask['fields']['priority']['name'];
            $ganttTask['priority_color'] = $phTask['fields']['priority']['color'];
            $ganttTask['type'] = 'task';

            $ganttTasks[$phTask['phid']] = $ganttTask;
        }

        $gantt = [
            'data'  => array_values($ganttTasks),
            'links' => $ganttLinks,
        ];

        return $this->json($gantt);
    }
}
