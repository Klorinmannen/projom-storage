DROP TABLE IF EXISTS `Integration`.`UserRole`;
DROP TABLE IF EXISTS `Integration`.`Role`;
DROP TABLE IF EXISTS `Integration`.`User`;

CREATE TABLE
	`Integration`.`User` (
		`UserID` INT PRIMARY KEY AUTO_INCREMENT,
		`Username` VARCHAR(255) NOT NULL,
		`Password` VARCHAR(255) NOT NULL,
		`Firstname` VARCHAR(255) DEFAULT NULL,
		`Lastname` VARCHAR(255) DEFAULT NULL,
		`Active` INT DEFAULT 1,
		`Created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		`Updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		KEY `Username` (`Username`),
		KEY `FirstnameLastname` (`Firstname`, `Lastname`),
		KEY `Active` (`Active`)
	);

INSERT INTO
	`Integration`.`User` (
		`Username`,
		`Password`,
		`Firstname`,
		`Lastname`,
		`Active`
	)
VALUES
	('system', 'system', NULL, NULL, 1),
	(
		'john.doe@example.com',
		'pass1234',
		'John',
		'Doe',
		1
	),
	(
		'jane.doe@example.com',
		'qwerty1234',
		'Jane',
		'Doe',
		1
	),
	(
		'sofie.doe@example.com',
		'asdf1234',
		'Sofie',
		'Doe',
		0
	),
	(
		'andrew.doe@example.com',
		'zxcv1234',
		'Andrew',
		'Doe',
		0
	);

CREATE TABLE
	`Integration`.`Role` (
		`RoleID` INT PRIMARY KEY AUTO_INCREMENT,
		`Role` VARCHAR(255) NOT NULL,
		`Description` VARCHAR(255),
		`Active` INT DEFAULT 1,
		`Created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		`Updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		KEY `Role` (`Role`),
		KEY `Active` (`Active`)
	);

INSERT INTO
	`Integration`.`Role` (`Role`, `Description`)
VALUES
	('System', 'System/God user'),
	('Admin', 'Administrator'),
	('User', 'Regular User'),
	('Guest', 'Guest User');

CREATE TABLE
	`Integration`.`UserRole` (
		`UserRoleID` INT PRIMARY KEY AUTO_INCREMENT,
		`UserID` INT NOT NULL,
		`RoleID` INT NOT NULL,
		`Active` INT DEFAULT 1,
		`Created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		`Updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		KEY `Active` (`Active`),
		FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`),
		FOREIGN KEY (`RoleID`) REFERENCES `Role` (`RoleID`)
	);

INSERT INTO
	`Integration`.`UserRole` (`UserID`, `RoleID`)
VALUES
	(1, 1),
	(2, 2),
	(3, 3),
	(4, 3),
	(5, 3);