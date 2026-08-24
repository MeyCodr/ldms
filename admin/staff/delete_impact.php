<?php
/**
 * What a staff deletion would actually destroy.
 *
 * DELETE FROM user is not a contained operation: fk_sme_staffid is
 * ON DELETE CASCADE, and skill_matrix_topics / skill_matrix_items cascade
 * from there, so removing one staff silently removes their entire skill
 * matrix - evaluations, topics and every rated item. Training rows are not
 * cascaded and are simply left pointing at an id that no longer exists.
 *
 * Shared by the admin and clerk staff pages so the warning cannot drift
 * between them.
 */

if (!function_exists('staffDeleteImpact')) {

    function staffDeleteImpact($conn, $userId)
    {
        $userId = (int) $userId;

        $impact = [
            'staffno' => '',
            'staffname' => '',
            'matrix_evaluations' => 0,
            'matrix_items' => 0,
            'matrix_approved' => 0,
            'training' => 0,
            'tna' => 0,
            'pme' => 0,
            'subordinates' => 0,
            'is_department_hod' => 0,
            'matrix_created_by' => 0,
            'matrix_approved_by' => 0,
        ];

        $stmt = $conn->prepare("SELECT staffno, staffname FROM user WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }
        $impact['staffno'] = $row['staffno'];
        $impact['staffname'] = $row['staffname'];

        // ---- destroyed by the cascade ----
        $sql = "SELECT
                    COUNT(DISTINCT e.id) AS evaluations,
                    COUNT(i.id)          AS items,
                    COUNT(DISTINCT CASE WHEN e.approval_status = 'APPROVED' THEN e.id END) AS approved
                FROM skill_matrix_evaluations e
                LEFT JOIN skill_matrix_topics t ON t.evaluation_id = e.id
                LEFT JOIN skill_matrix_items i  ON i.topic_id = t.id
                WHERE e.staffid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        if ($r = $stmt->get_result()->fetch_assoc()) {
            $impact['matrix_evaluations'] = (int) $r['evaluations'];
            $impact['matrix_items'] = (int) $r['items'];
            $impact['matrix_approved'] = (int) $r['approved'];
        }

        // ---- left orphaned (not cascaded, not cleaned up) ----
        $counts = [
            'training' => "SELECT
                    (SELECT COUNT(*) FROM participation           WHERE userid = ?)
                  + (SELECT COUNT(*) FROM participation_archive   WHERE userid = ?)
                  + (SELECT COUNT(*) FROM participateojt          WHERE userid = ?)
                  + (SELECT COUNT(*) FROM participateojt_archive  WHERE userid = ?) AS n",
            'tna' => "SELECT (SELECT COUNT(*) FROM tna WHERE userid = ?)
                           + (SELECT COUNT(*) FROM tna_archive WHERE userid = ?) AS n",
            'pme' => "SELECT (SELECT COUNT(*) FROM pme WHERE userid = ?)
                           + (SELECT COUNT(*) FROM pme_archive WHERE userid = ?) AS n",
        ];
        foreach ($counts as $key => $sql) {
            $placeholders = substr_count($sql, '?');
            $stmt = $conn->prepare($sql);
            $types = str_repeat('i', $placeholders);
            $params = array_fill(0, $placeholders, $userId);
            $refs = [$types];
            foreach ($params as $k => $v) { $refs[] = &$params[$k]; }
            call_user_func_array([$stmt, 'bind_param'], $refs);
            $stmt->execute();
            $impact[$key] = (int) $stmt->get_result()->fetch_assoc()['n'];
        }

        // ---- links that would be blanked or left dangling ----
        $simple = [
            'subordinates'       => "SELECT COUNT(*) n FROM user WHERE hodid = ?",
            'is_department_hod'  => "SELECT COUNT(*) n FROM departments WHERE hod_user_id = ?",
            'matrix_created_by'  => "SELECT COUNT(*) n FROM skill_matrix_evaluations WHERE created_by = ?",
            'matrix_approved_by' => "SELECT COUNT(*) n FROM skill_matrix_evaluations WHERE approved_by = ?",
        ];
        foreach ($simple as $key => $sql) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $impact[$key] = (int) $stmt->get_result()->fetch_assoc()['n'];
        }

        return $impact;
    }

    /**
     * Reasons this staff must not be hard deleted.
     *
     * Training, TNA, PME and skill matrix rows are history: deleting the
     * staff either destroys them (skill matrix, via the cascade) or strands
     * them pointing at an id that no longer exists, where they silently drop
     * out of every report that inner joins user. 11,150 such orphans already
     * exist in this database from past deletions.
     *
     * The supported way to remove someone who has left is status = RESIGN
     * with a dateresign, which the dashboards already understand and which
     * keeps their training history attributable.
     *
     * Links that can simply be cleaned up - subordinates, department HOD,
     * who evaluated or approved a matrix - are deliberately NOT blockers.
     */
    function staffDeleteBlockers(array $impact)
    {
        $blockers = [];
        if ($impact['matrix_evaluations'] > 0) {
            $blockers[] = $impact['matrix_evaluations'] . ' skill matrix evaluation(s) with '
                . $impact['matrix_items'] . ' rated item(s) would be permanently destroyed.';
        }
        if ($impact['training'] > 0) {
            $blockers[] = $impact['training'] . ' training record(s) would be orphaned.';
        }
        if ($impact['tna'] > 0) {
            $blockers[] = $impact['tna'] . ' TNA record(s) would be orphaned.';
        }
        if ($impact['pme'] > 0) {
            $blockers[] = $impact['pme'] . ' PME record(s) would be orphaned.';
        }
        return $blockers;
    }

    /**
     * Turn the impact into the lines shown in the confirmation dialog.
     */
    function staffDeleteImpactWarnings(array $impact)
    {
        $lines = [];

        if ($impact['matrix_evaluations'] > 0) {
            $line = 'PERMANENTLY DELETES the skill matrix: '
                . $impact['matrix_evaluations'] . ' evaluation(s) and '
                . $impact['matrix_items'] . ' rated item(s).';
            if ($impact['matrix_approved'] > 0) {
                $line .= ' ' . $impact['matrix_approved'] . ' of these is APPROVED and signed off.';
            }
            $lines[] = $line;
        }
        if ($impact['training'] > 0) {
            $lines[] = $impact['training'] . ' training record(s) will be left orphaned and drop out of reports.';
        }
        if ($impact['tna'] > 0) {
            $lines[] = $impact['tna'] . ' TNA record(s) will be left orphaned.';
        }
        if ($impact['pme'] > 0) {
            $lines[] = $impact['pme'] . ' PME record(s) will be left orphaned.';
        }
        if ($impact['subordinates'] > 0) {
            $lines[] = $impact['subordinates'] . ' staff report to this person and will be left without a HOD.';
        }
        if ($impact['is_department_hod'] > 0) {
            $lines[] = 'This person is the assigned HOD of ' . $impact['is_department_hod'] . ' department(s), which will be left with no HOD.';
        }
        if ($impact['matrix_created_by'] > 0) {
            $lines[] = 'This person filled in ' . $impact['matrix_created_by'] . ' skill matrix evaluation(s) for others; those will lose their "Evaluated By" and "Verified By" names.';
        }
        if ($impact['matrix_approved_by'] > 0) {
            $lines[] = 'This person approved ' . $impact['matrix_approved_by'] . ' skill matrix evaluation(s); those will lose their "Approved By" name.';
        }

        return $lines;
    }

    /**
     * Delete a staff, refusing if it would orphan or destroy history, and
     * clearing the links that would otherwise dangle. All in one transaction.
     *
     * Returns ['ok' => bool, 'blocked' => bool, 'detail' => string, 'cleared' => int].
     */
    function staffDeleteSafely($conn, $userId)
    {
        $userId = (int) $userId;

        $impact = staffDeleteImpact($conn, $userId);
        if ($impact === null) {
            return ['ok' => false, 'blocked' => false, 'detail' => 'Staff not found.', 'cleared' => 0];
        }

        // Re-checked here, not only in the browser, so the rule holds however
        // the request arrives.
        $blockers = staffDeleteBlockers($impact);
        if (!empty($blockers)) {
            return [
                'ok' => false,
                'blocked' => true,
                'detail' => "This staff cannot be deleted because it would lose data:\n\n- "
                    . implode("\n- ", $blockers)
                    . "\n\nEdit the staff and set Status to RESIGN instead. That keeps their"
                    . " training history and removes them from active headcount.",
                'cleared' => 0,
            ];
        }

        $conn->begin_transaction();

        // user.hodid carries no foreign key, so subordinates would be left
        // pointing at a HOD that no longer exists. departments.hod_user_id
        // and the skill matrix created_by / approved_by columns are already
        // ON DELETE SET NULL and need no help.
        $stmt = $conn->prepare("UPDATE user SET hodid = 0 WHERE hodid = ?");
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $conn->rollback();
            return ['ok' => false, 'blocked' => false, 'detail' => 'Could not clear reporting links: ' . $stmt->error, 'cleared' => 0];
        }
        $cleared = $stmt->affected_rows;

        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $conn->rollback();
            return ['ok' => false, 'blocked' => false, 'detail' => 'Delete failed: ' . $stmt->error, 'cleared' => 0];
        }
        if ($stmt->affected_rows !== 1) {
            $conn->rollback();
            return ['ok' => false, 'blocked' => false, 'detail' => 'Expected to delete exactly one row.', 'cleared' => 0];
        }

        $conn->commit();
        return ['ok' => true, 'blocked' => false, 'detail' => '', 'cleared' => $cleared];
    }
}
?>
