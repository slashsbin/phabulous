<?php

namespace Bundle\AppBundle\Controller\Phacility;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EditController extends Controller
{
    public function maniphestAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $conduit = $this->get('phacility.conduit');

        $startDateStamp = 0;
        $duration = 0;
        $phacilityAuxiliary = [];
        $id = $request->get('id');
        $mode = $request->get('mode');
        $task = $request->get('task');

        switch (strtoupper($mode)) {
            case 'MOVE':
                $startDateStamp = strtotime($task['start_date']);
                $phacilityAuxiliary['std:maniphest:ray:maniphest-start-date'] = $startDateStamp;
                break;
            case 'RESIZE':
                $duration = $task['duration'];
                $phacilityAuxiliary['std:maniphest:ray:maniphest-estimated-hours'] = $duration;
                break;
            default:
                $this->createAccessDeniedException();
        }

        $phTask = $conduit->updateManiphest([
            'phid'      => $id,
            'auxiliary' => $phacilityAuxiliary,
        ]);

        $ganttTask = [];
        $ganttTask['id'] = $phTask['phid'];
        $ganttTask['tid'] = $phTask['id'];
        $ganttTask['start_date_ts'] = date('U', $phTask['auxiliary']['std:maniphest:ray:maniphest-start-date']);
        $ganttTask['start_date'] = date('Y-m-d', $phTask['auxiliary']['std:maniphest:ray:maniphest-start-date']);
        $ganttTask['duration'] = $phTask['auxiliary']['std:maniphest:ray:maniphest-estimated-hours'];

        return $this->json($ganttTask);
    }
}
