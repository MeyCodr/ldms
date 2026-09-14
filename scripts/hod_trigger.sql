DELIMITER $$
DROP TRIGGER IF EXISTS trg_departments_hod_update$$
CREATE TRIGGER trg_departments_hod_update
AFTER UPDATE ON departments
FOR EACH ROW
BEGIN
    IF NOT (NEW.hod_user_id <=> OLD.hod_user_id) THEN
        -- Excludes the HOD's own row: without this, a department head who
        -- is themselves a member of their own department gets their hodid
        -- set to their own id (self-loop). This was live in production
        -- data (40 staff on 2026-09-04) before this guard was added -
        -- fixing the trigger does not retroactively correct rows it
        -- already corrupted; those need a separate one-time cleanup.
        UPDATE user SET hodid = COALESCE(NEW.hod_user_id, 0)
        WHERE department_id = NEW.id
          AND id <> COALESCE(NEW.hod_user_id, 0);
    END IF;
END$$
DELIMITER ;
