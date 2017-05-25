DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_2_3_status`()
BEGIN
	DELETE FROM tasks_dicts WHERE net_id IN(SELECT id FROM tasks WHERE status IN('2', '3') AND forDelete='1');
	UPDATE tasks SET forDelete='0' WHERE forDelete='1';
END$$

DELIMITER ;

DELIMITER //
CREATE TRIGGER `change_status_to_failed` AFTER UPDATE ON `tasks_dicts`
 FOR EACH ROW BEGIN
   	DECLARE dicts_count INT;
	DECLARE stat INT;

	SELECT COUNT(*) INTO dicts_count FROM (SELECT * FROM tasks_dicts WHERE status NOT IN ('1') AND net_id=NEW.net_id) as alias;
	IF (dicts_count=0) THEN
		UPDATE tasks SET status='3' WHERE id=NEW.net_id;
		UPDATE tasks SET forDelete='1' WHERE id=NEW.net_id;
	END IF;
    SELECT COUNT(*) INTO dicts_count FROM (SELECT * FROM tasks_dicts WHERE status NOT IN ('1') AND net_id=NEW.net_id) as alias;
	SELECT status INTO stat FROM tasks WHERE id=NEW.net_id;
	IF dicts_count!=0 AND stat=3 THEN
		UPDATE tasks SET status='0' WHERE id=NEW.net_id;
	END IF;
END
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER `reset_failed` AFTER INSERT ON `tasks_dicts`
 FOR EACH ROW BEGIN
DECLARE dicts_count INT;
DECLARE stat INT;
SELECT COUNT(*) INTO dicts_count FROM (SELECT * FROM tasks_dicts WHERE status NOT IN ('1') AND net_id=NEW.net_id) as alias;
	SELECT status INTO stat FROM tasks WHERE id=NEW.net_id;
	IF dicts_count!=0 AND stat=3 THEN
		UPDATE tasks SET status='0' WHERE id=NEW.net_id;
	END IF;
END
//
DELIMITER ;