--DELETE TAX PROFESSIONAL
DELIMITER //

CREATE TRIGGER delete_user_on_taxprofessional
AFTER DELETE ON TaxProfessional
FOR EACH ROW
BEGIN
    DELETE FROM USER WHERE id = OLD.user_id;
END //

DELIMITER ;


--ADD TAX PROFESSIONAL

DELIMITER //
CREATE PROCEDURE AddTaxProfessional(
    IN professional_name VARCHAR(255),
    IN professional_email VARCHAR(255),
    IN professional_phone VARCHAR(15),
    IN professional_tin VARCHAR(15),
    IN professional_password VARCHAR(255),
    IN certification_id INT
)
BEGIN
    DECLARE new_user_id INT;

    -- Insert into USER table
    INSERT INTO USER (name, email, phone, password, role)
    VALUES (professional_name, professional_email, professional_phone, professional_password, 'TaxProfessional');

    -- Get the ID of the newly inserted user
    SET new_user_id = LAST_INSERT_ID();

    -- Insert into TaxProfessional table
    INSERT INTO TaxProfessional (user_id, name, email, phone, TIN, registrationdate, certification_id, password)
    VALUES (new_user_id, professional_name, professional_email, professional_phone, professional_tin, NOW(), certification_id, professional_password);
END //
DELIMITER ;


--DELETE TAXPROFESSIONAL
DELIMITER //
CREATE PROCEDURE DeleteTaxProfessional(IN prof_id INT)
BEGIN
    -- Delete from TaxProfessional
    DELETE FROM TaxProfessional WHERE user_id = prof_id;
    
    -- Delete from Users table (trigger should handle this, but add for safety)
    DELETE FROM User WHERE id = prof_id; 
END //
DELIMITER ;



---audit logs;
DELIMITER //

CREATE TRIGGER log_add_taxprofessional
AFTER INSERT ON TaxProfessional
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (action, details, log_date)
    VALUES (
        'Add TaxProfessional', 
        CONCAT('Name: ', NEW.name, ', Email: ', NEW.email), 
        NOW()
    );
END //

DELIMITER ;

