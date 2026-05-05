-- create_org_update_triggers.sql
-- Apply once to keep user legacy text fields in sync automatically
-- Usage in mysql:
--   SOURCE /path/to/ldms/scripts/create_org_update_triggers.sql;
-- OR in command-line mysql:
--   mysql -u user -p yourdb < scripts/create_org_update_triggers.sql

DELIMITER $$

DROP TRIGGER IF EXISTS trg_divisions_update$$
CREATE TRIGGER trg_divisions_update
AFTER UPDATE ON divisions
FOR EACH ROW
BEGIN
    IF NEW.name <> OLD.name THEN
        UPDATE `user`
        SET `division` = NEW.name
        WHERE `division_id` = NEW.id;
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_departments_update$$
CREATE TRIGGER trg_departments_update
AFTER UPDATE ON departments
FOR EACH ROW
BEGIN
    IF NEW.name <> OLD.name THEN
        UPDATE `user`
        SET `department` = NEW.name
        WHERE `department_id` = NEW.id;
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_sections_update$$
CREATE TRIGGER trg_sections_update
AFTER UPDATE ON sections
FOR EACH ROW
BEGIN
    IF NEW.name <> OLD.name THEN
        UPDATE `user`
        SET `section` = NEW.name
        WHERE `section_id` = NEW.id;
    END IF;
END$$

DELIMITER ;
