CREATE DATABASE IF NOT EXISTS itms;

USE itms;

CREATE TABLE User (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('taxpayer', 'taxprofessional', 'taxauthority') NOT NULL
);


CREATE TABLE Taxpayer (
    TaxpayerID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100),
    TIN VARCHAR(15) UNIQUE,
    Address TEXT,
    Email VARCHAR(100) UNIQUE,
    Phone VARCHAR(15),
    RegistrationDate DATE,
    Password VARCHAR(255)
);

CREATE TABLE TaxProfessional (
    ProfessionalID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100),
    TIN VARCHAR(15),
    Certification_ID VARCHAR(20),
    Email VARCHAR(100) UNIQUE,
    Phone VARCHAR(15),
    RegistrationDate DATE
    Password VARCHAR(255)
);

CREATE TABLE TaxAuthority (
    AuthorityID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100),
    Email VARCHAR(100) UNIQUE,
    Phone VARCHAR(15),
    Designation VARCHAR(50),
    Department VARCHAR(100)
);

CREATE TABLE IncomeTaxReturn (
    ReturnID INT PRIMARY KEY AUTO_INCREMENT,
    TaxpayerID INT,
    FilingYear YEAR,
    IncomeDetails TEXT,
    Documents TEXT,
    FilingDate DATE,
    TaxLiability DECIMAL(10, 2),
    Status ENUM('Pending', 'Completed', 'Rejected'),
    FOREIGN KEY (TaxpayerID) REFERENCES Taxpayer(TaxpayerID)
);

CREATE TABLE Payment (
    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
    ReturnID INT,
    PaymentAmount DECIMAL(10, 2),
    PaymentDate DATE,
    PaymentStatus ENUM('Pending', 'Completed'),
    ReceiptNumber VARCHAR(50),
    PaymentMethod VARCHAR(20),
    FOREIGN KEY (ReturnID) REFERENCES IncomeTaxReturn(ReturnID)
);

CREATE TABLE Refund (
    RefundID INT PRIMARY KEY AUTO_INCREMENT,
    ReturnID INT,
    RefundAmount DECIMAL(10, 2),
    RefundStatus ENUM('Pending', 'Completed'),
    RefundIssuedDate DATE,
    FOREIGN KEY (ReturnID) REFERENCES IncomeTaxReturn(ReturnID)
);

CREATE TABLE TaxReport (
    ReportID INT PRIMARY KEY AUTO_INCREMENT,
    TaxpayerID INT,
    ReturnID INT,
    ReportType VARCHAR(50),
    GeneratedDate DATE,
    Format ENUM('PDF', 'HTML', 'CSV'),
    FOREIGN KEY (TaxpayerID) REFERENCES Taxpayer(TaxpayerID),
    FOREIGN KEY (ReturnID) REFERENCES IncomeTaxReturn(ReturnID)
);

CREATE TABLE ErrorLog (
    ErrorID INT PRIMARY KEY AUTO_INCREMENT,
    UserID INT,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ErrorMessage TEXT,
    Module VARCHAR(50),
    ResolvedStatus BOOLEAN,
    FOREIGN KEY (UserID) REFERENCES Taxpayer(TaxpayerID)
);

CREATE TABLE Notification (
    NotificationID INT PRIMARY KEY AUTO_INCREMENT,
    UserID INT,
    Message TEXT,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Type ENUM('Info', 'Warning', 'Alert'),
    Status ENUM('Unread', 'Read'),
    FOREIGN KEY (UserID) REFERENCES Taxpayer(TaxpayerID)
);
ALTER TABLE User
ADD COLUMN email VARCHAR(255),
ADD COLUMN phone VARCHAR(20);
ALTER TABLE Taxpayer ADD COLUMN user_id INT;
ALTER TABLE Taxpayer ADD FOREIGN KEY (user_id) REFERENCES User(id);
ALTER TABLE Taxprofessional ADD COLUMN user_id INT;
ALTER TABLE Taxprofessional ADD FOREIGN KEY (user_id) REFERENCES User(id);
ALTER TABLE Taxpayer
ADD CONSTRAINT fk_tax_professional
FOREIGN KEY (tax_professional_id) REFERENCES TaxProfessional(user_id);
CREATE TABLE tax_revenues (
    revenue_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES taxpayer(user_id)
);
ALTER TABLE tax_revenues
ADD COLUMN tax_professional_id INT;
CREATE TABLE Documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(id) -- Assumes your Users table is named 'Users' and has an 'id' column 
);

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



