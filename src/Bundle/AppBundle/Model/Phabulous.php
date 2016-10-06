<?php

namespace Bundle\AppBundle\Model;

class Phabulous
{
    /**
     * Custom Phabricator Maniphest Fields
     */
    const MANIPHEST_START_DATE = 'custom.phabulous-start-date';
    const MANIPHEST_END_DATE = 'custom.phabulous-end-date';
    const MANIPHEST_PROGRESS = 'custom.phabulous-progress';
    const MANIPHEST_ESTIMATE = 'custom.phabulous-estimated-duration';
    const MANIPHEST_ESTIMATE_UNIT = 'hour';
}
