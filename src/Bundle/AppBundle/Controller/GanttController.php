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
        $phacilityUsersJson = $this->forward('AppBundle:Phacility/Search:people')->getContent();
        $phacilityTasksJson = $this->forward('AppBundle:Phacility/Search:maniphest')->getContent();
        $phacilityProjectsJson = $this->forward('AppBundle:Phacility/Search:projects')->getContent();

        $phacilityUsers = json_decode($phacilityUsersJson, TRUE);
        $phacilityTasks = json_decode($phacilityTasksJson, TRUE);
        $phacilityProjects = json_decode($phacilityProjectsJson, TRUE);
        $phacilityUrl = $this->getParameter('phacility_url');

        $phacilityProjectsIds = array_map(function($phProject) {
            return $phProject['phid'];
        }, $phacilityProjects['data']);
        $phacilityFindById = function($phid, $phacilityItems) {
            foreach($phacilityItems['data'] as $phUser) {
                if ($phid === $phUser['phid']) {
                    return $phUser;
                }
            }
        };

        $ganttTasks = [];
        $ganttLinks = [];

        // Projects
        foreach ($phacilityProjects['data'] as $phProject) {
            $ganttTask = [];

            $ganttTask['id'] = $phProject['phid'];
            $ganttTask['text'] = $phProject['fields']['name'];
            $ganttTask['description'] = $phProject['fields']['description'];
            $ganttTask['start_date'] = date('Y-m-d');
            $ganttTask['duration'] = 0;
            $ganttTask['end_date'] = date('Y-m-d');
            $ganttTask['progress'] = 0;
            $ganttTask['open'] = TRUE;
            $ganttTask['holder'] = '';
            $ganttTask['uri'] = sprintf('%s/project/view/%s/', $phacilityUrl, $phProject['id']);
            $ganttTask['tid'] = $phProject['id'];
            $ganttTask['type'] = 'project';
            $ganttTask['childs_count'] = 0;

            $ganttTasks[$phProject['phid']] = $ganttTask;
        }

        // Tasks
        foreach ($phacilityTasks['data'] as $phTask) {
            // Gantt Task
            $ganttTask = [];

            // Dates & Estimation
            $ganttTaskDuration = $phTask['fields'][Phabulous::MANIPHEST_ESTIMATE];
            $ganttTaskStartDate = null;
            $ganttTaskEndDate = null;
            if ($ganttTaskStartDateTS = $phTask['fields'][Phabulous::MANIPHEST_START_DATE]) {
                $ganttTaskStartDate = date('Y-m-d', $ganttTaskStartDateTS);
            }
            if ($ganttTaskEndDateTS = $phTask['fields'][Phabulous::MANIPHEST_END_DATE]) {
                $ganttTaskEndDate = date('Y-m-d', $ganttTaskEndDateTS);
            }
            // UnScheduled Tasks
            $ganttTaskRequiredDates = [
                $ganttTaskStartDateTS,
                $ganttTaskEndDateTS,
                $ganttTaskDuration,
            ];
            $ganttTaskRequiredDates = array_filter($ganttTaskRequiredDates);
            $ganttTask['_unscheduled'] = count($ganttTaskRequiredDates) <= 1;
            // Overdue
            $ganttTask['overdue'] = !$ganttTask['_unscheduled'] &&
                'open' === $phTask['fields']['status']['value'] &&
                strtotime(sprintf('+ %s %s', $ganttTaskDuration, Phabulous::MANIPHEST_ESTIMATE_UNIT), $ganttTaskStartDateTS) < time();
            // Set UnScheduled Tasks Start/End Dates to Task Create/Modified Dates
            if ($ganttTask['_unscheduled']) {
                $ganttTaskStartDate = date('Y-m-d', $phTask['fields']['dateCreated']);
                $ganttTaskEndDate = date('Y-m-d', $phTask['fields']['dateModified']);
            }

            $ganttTask['id'] = $phTask['phid'];
            $ganttTask['text'] = $phTask['fields']['name'];
            $ganttTask['description'] = $phTask['fields']['name'];
            $ganttTask['start_date'] = $ganttTaskStartDate;
            $ganttTask['duration'] = $ganttTaskDuration ?: 0;
            $ganttTask['end_date'] = $ganttTaskEndDate;
            $ganttTask['progress'] = $phTask['fields'][Phabulous::MANIPHEST_PROGRESS] / 100;
            $ganttTask['open'] = 'open' === $phTask['fields']['status']['value'];
            $ganttTask['holder'] = $phacilityFindById($phTask['fields']['ownerPHID'], $phacilityUsers)['fields']['realName'];
            $ganttTask['uri'] = sprintf('%s/T%s', $phacilityUrl, $phTask['id']);
            $ganttTask['tid'] = $phTask['id'];
            $ganttTask['priority'] = $phTask['fields']['priority']['name'];
            $ganttTask['priority_color'] = $phTask['fields']['priority']['color'];
            $ganttTask['type'] = 'task';

            // Parent Project
            if ($parentProjectId = array_intersect($phacilityProjectsIds, $phTask['attachments']['projects']['projectPHIDs'])) {
                $parentProjectId = array_pop($parentProjectId);
                $ganttTaskParent = &$ganttTasks[$parentProjectId];

                // Update Parent Progress
                $ganttTaskParent['progress'] = ($ganttTaskParent['progress'] * $ganttTaskParent['childs_count']) + $ganttTask['progress'];
                $ganttTaskParent['progress'] /= ++$ganttTaskParent['childs_count'];
                $ganttTaskParent['progress'] = round($ganttTaskParent['progress'], 2);

                // Update Parent StartDate
                if ($ganttTaskStartDate) {
                    if ($ganttTaskStartDate < $ganttTaskParent['start_date']) {
                        $ganttTaskParent['start_date'] = $ganttTaskStartDate;
                    }
                    if ($ganttTaskEndDate > $ganttTaskParent['end_date']) {
                        $ganttTaskParent['end_date'] = $ganttTaskEndDate;
                    }
                    $ganttTaskParent['duration'] += $ganttTask['duration'];
                }

                $ganttTask['parent'] = $parentProjectId;
            }

            $ganttTasks[$phTask['phid']] = $ganttTask;
        }

        $gantt = [
            'data'  => array_values($ganttTasks),
            'links' => $ganttLinks,
        ];

        return $this->json($gantt);
    }
}
