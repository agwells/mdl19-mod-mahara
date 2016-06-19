<?php
function mahara_mnet_publishes() {
    return array(array(
        'name'       => 'mahara',
        'apiversion' => 1,
        'methods'    => array(
            'get_views_for_user',
            'submit_view_for_assessment',
            'release_submitted_view',
            'can_view_view',
        ),
    ));
}

/**
 * Determines whether the specified Moodle user should be able to view the
 * specified Mahara page (or collection), because it has been submitted to a
 * Moodle assignment and the user is grading it.
 *
 * // TODO: Currently, this function allows anyone with gradebook access in the
 * assignment, to view the page once it has been selected by the student. We
 * may want to change this to be more selective. For instance, only granting
 * view access if the submission record in mdl_assign_submission is in
 * ASSIGN_SUBMISSION_STATUS_SUBMITTED
 *
 * @param int $viewid Mahara view or collection ID
 * @param string $username Moodle username of the user whoe permission we're checking
 * @param int $submission ID of the assignment submission (in Moodle) this is
 * a part of
 * @param bool $iscollection Whether or not it's a view or a collection
 * @return boolean
 */
function can_view_view($viewid, $username, $submission, $iscollection) {
    global $DB;
    $submission = get_record('assignment_submissions', 'id', $submission, null, null, null, null, 'id, assignment, data2');
    if (!$submission) {
        // bad submission id
        return false;
    }

    $data = unserialize($submission->data2);
    if (!($data['id'] == $viewid && $data['iscollection'] == $iscollection)) {
        // Submission doesn't refer to this view
        return false;
    }

    if (!record_exists('assignment', 'id', $submission->assignment, 'assignmenttype', 'mahara')) {
        // Not a mahara assignment
        return false;
    }

    if (!($cm = get_coursemodule_from_id('assignment', $submission->assignment))) {
        // Bad assignment ID.
        return false;
    }

    if (!($userid = get_field('user', 'id', 'username', $username))) {
        // Unknown username
        return false;
    }

    // Now that we've verified all the params, check whether the user actually
    // has permission to view this submission.
    return has_capability(
        'mod/assignment:grade',
        get_context_instance(CONTEXT_MODULE, $cm->id),
        $userid,
        $doanything
    );
}

/**
 * These functions are placeholders describing remote methods on the Mahara
 * side
 */
function get_views_for_user($username, $query=null) {
    return new StdClass;
}

function submit_view_for_assessment($username, $viewid) {
    return array();
}

function release_submitted_view($viewid, $assessmentdata=array(), $username) {
}
