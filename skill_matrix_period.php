<?php
// The skill matrix is filled one quarter in arrears: the quarter that can be
// filled (and that every skill matrix list, chart and export shows) is the
// one that has just ended, not the one today's date falls in.
//
//   Today in Jan-Mar -> Q4 of the previous year
//   Today in Apr-Jun -> Q1
//   Today in Jul-Sep -> Q2
//   Today in Oct-Dec -> Q3
//
// The period is stored on each row in skill_matrix_evaluations.eval_year /
// eval_quarter; evaluation_date is only the day the matrix was filled.
//
// Returns array($year, $quarter).
function skillMatrixFillPeriod($timestamp = null)
{
    $timestamp = $timestamp === null ? time() : $timestamp;
    $year = (int) date('Y', $timestamp);
    $quarter = (int) ceil(date('n', $timestamp) / 3) - 1;

    if ($quarter < 1) {
        $quarter = 4;
        $year--;
    }

    return array($year, $quarter);
}

// Months covered by a quarter, e.g. 3 -> "July - September".
function skillMatrixQuarterMonths($quarter)
{
    $months = array(1 => 'January - March', 2 => 'April - June', 3 => 'July - September', 4 => 'October - December');

    return isset($months[(int) $quarter]) ? $months[(int) $quarter] : '';
}

// The quarter before the given one. Returns array($year, $quarter).
function skillMatrixPreviousPeriod($year, $quarter)
{
    return $quarter > 1 ? array((int) $year, $quarter - 1) : array($year - 1, 4);
}

// Id of a staff's latest skill matrix for a period, or 0 if there is none.
function skillMatrixFindEvaluationId($conn, $staffId, $year, $quarter)
{
    $stmt = $conn->prepare("SELECT id FROM skill_matrix_evaluations WHERE staffid = ? AND eval_year = ? AND eval_quarter = ? ORDER BY evaluation_date DESC, id DESC LIMIT 1");
    $stmt->bind_param("iii", $staffId, $year, $quarter);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row ? (int) $row['id'] : 0;
}

// Ids of every staff who has a skill matrix for a period, as array(staffid => true).
function skillMatrixStaffIdsWithEvaluation($conn, $year, $quarter)
{
    $staffIds = array();
    $stmt = $conn->prepare("SELECT DISTINCT staffid FROM skill_matrix_evaluations WHERE eval_year = ? AND eval_quarter = ?");
    $stmt->bind_param("ii", $year, $quarter);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $staffIds[(int) $row['staffid']] = true;
    }

    return $staffIds;
}

// Copies the topics and ratings of an existing skill matrix into a new DRAFT
// for the same staff in another period. The copy belongs to whoever makes it
// ($createdBy) and is dated today, exactly like a matrix typed in by hand, so
// it still has to be reviewed and submitted for approval. Returns the new
// evaluation id, or 0 if nothing was copied.
function skillMatrixCopyToPeriod($conn, $sourceEvaluationId, $staffId, $year, $quarter, $createdBy)
{
    $topics = array();
    $topicStmt = $conn->prepare("SELECT id, section_type, topic_name, sort_order FROM skill_matrix_topics WHERE evaluation_id = ? ORDER BY FIELD(section_type, 'knowledge', 'skill', 'ability'), sort_order, id");
    $topicStmt->bind_param("i", $sourceEvaluationId);
    $topicStmt->execute();
    $topicResult = $topicStmt->get_result();
    $itemStmt = $conn->prepare("SELECT evaluation_text, rating, sort_order FROM skill_matrix_items WHERE topic_id = ? ORDER BY sort_order, id");

    while ($topic = $topicResult->fetch_assoc()) {
        $itemStmt->bind_param("i", $topic['id']);
        $itemStmt->execute();
        $topic['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $topics[] = $topic;
    }

    if (count($topics) == 0) {
        return 0;
    }

    mysqli_begin_transaction($conn);

    try {
        $evaluationDate = date('Y-m-d');
        $insertEvaluationStmt = $conn->prepare("INSERT INTO skill_matrix_evaluations (staffid, evaluation_date, eval_year, eval_quarter, created_by, approval_status) VALUES (?, ?, ?, ?, ?, NULL)");
        $insertEvaluationStmt->bind_param("isiii", $staffId, $evaluationDate, $year, $quarter, $createdBy);
        if (!$insertEvaluationStmt->execute()) {
            throw new Exception($insertEvaluationStmt->error);
        }
        $newEvaluationId = $conn->insert_id;

        $insertTopicStmt = $conn->prepare("INSERT INTO skill_matrix_topics (evaluation_id, section_type, topic_name, sort_order) VALUES (?, ?, ?, ?)");
        $insertItemStmt = $conn->prepare("INSERT INTO skill_matrix_items (topic_id, evaluation_text, rating, sort_order) VALUES (?, ?, ?, ?)");

        foreach ($topics as $topic) {
            $insertTopicStmt->bind_param("issi", $newEvaluationId, $topic['section_type'], $topic['topic_name'], $topic['sort_order']);
            if (!$insertTopicStmt->execute()) {
                throw new Exception($insertTopicStmt->error);
            }
            $newTopicId = $conn->insert_id;

            foreach ($topic['items'] as $item) {
                $insertItemStmt->bind_param("isii", $newTopicId, $item['evaluation_text'], $item['rating'], $item['sort_order']);
                if (!$insertItemStmt->execute()) {
                    throw new Exception($insertItemStmt->error);
                }
            }
        }

        mysqli_commit($conn);

        return (int) $newEvaluationId;
    } catch (Exception $e) {
        mysqli_rollback($conn);

        return 0;
    }
}
?>
