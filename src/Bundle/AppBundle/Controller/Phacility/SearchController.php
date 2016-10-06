<?php

namespace Bundle\AppBundle\Controller\Phacility;

use DateInterval;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    public function maniphestAction()
    {
        $appCache = $this->get('cache.app');
        $phacilityTasksCache = $appCache->getItem('phacility.tasks');

        if (!$phacilityTasksCache->isHit()) {
            $conduit = $this->get('phacility.conduit');
            $serializer = $this->get('serializer');

            try {
                $result = $conduit->searchManiphest(
                    [
                        'queryKey'    => $this->getParameter('phacility_maniphest_query_key'),
                        'attachments' => [
                            'projects' => TRUE,
                        ],
                        'limit'       => $this->getParameter('phacility_maniphest_query_limit'),
                    ]
                );
            } catch (\Exception $e) {
                $result = ['data' => []];
            }

            $phacilityTasksCache->set($result);
            $phacilityTasksCache->expiresAfter(DateInterval::createFromDateString('6 hour'));
            $appCache->save($phacilityTasksCache);
        }

        $phacilityTasks = $phacilityTasksCache->get();

        return $this->json($phacilityTasks);
    }

    public function peopleAction()
    {
        $appCache = $this->get('cache.app');
        $phacilityUsersCache = $appCache->getItem('phacility.users');

        if (!$phacilityUsersCache->isHit()) {
            $conduit = $this->get('phacility.conduit');
            $serializer = $this->get('serializer');

            try {
                $result = $conduit->searchUser(
                    [
                        'queryKey' => $this->getParameter('phacility_people_query_key'),
                    ]
                );
            } catch (\Exception $e) {
                $result = ['data' => []];
            }

            $phacilityUsersCache->set($result);
            $phacilityUsersCache->expiresAfter(DateInterval::createFromDateString('1 week'));
            $appCache->save($phacilityUsersCache);
        }

        $phacilityUsers = $phacilityUsersCache->get();

        return $this->json($phacilityUsers);
    }

    public function projectsAction()
    {
        $appCache = $this->get('cache.app');
        $phacilityProjectsCache = $appCache->getItem('phacility.projects');

        if (!$phacilityProjectsCache->isHit()) {
            $conduit = $this->get('phacility.conduit');

            try {
                $result = $conduit->searchProjects(
                    [
                        'queryKey' => $this->getParameter('phacility_projects_query_key'),
                    ]
                );
            } catch (\Exception $e) {
                $result = ['data' => []];
            }

            $phacilityProjectsCache->set($result);
            $phacilityProjectsCache->expiresAfter(DateInterval::createFromDateString('1 week'));
            $appCache->save($phacilityProjectsCache);
        }

        $phacilityProjects = $phacilityProjectsCache->get();

        return $this->json($phacilityProjects);
    }
}
