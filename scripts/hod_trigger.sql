DELIMITER $$
DROP TRIGGER IF EXISTS trg_departments_hod_update$$
CREATE TRIGGER trg_departments_hod_update
AFTER UPDATE ON departments
FOR EACH ROW
BEGIN
    IF (NEW.hod_user_id IS NULL AND OLD.hod_user_id IS NOT NULL)
    OR (NEW.hod_user_id IS NOT NULL AND OLD.hod_user_id IS NULL)
    OR (NEW.hod_user_id IS NOT NULL AND OLD.hod_user_id IS NOT NULL AND NEW.hod_user_id <> OLD.hod_user_id)
    THEN
        UPDATE user SET hodid = COALESCE(NEW.hod_user_id, 0)
        WHERE department_id = NEW.id;
    END IF;
END$$
DELIMITER ;
