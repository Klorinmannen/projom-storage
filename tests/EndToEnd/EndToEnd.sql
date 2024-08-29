CREATE DATABASE IF NOT EXISTS `EndToEnd` DEFAULT CHARACTER
SET
	utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `EndToEnd`.`User`;

ADD TABLE `EndToEnd`.`User` (
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

DELETE FROM `EndToEnd`.`User`;

INSERT INTO
	`EndToEnd`.`User` (`Username`, `Password`, `Firstname`, `Lastname`)
VALUES
	('system', 'system', NULL, NULL),
	('john.doe@example.com', 'pass1234', 'John', 'Doe'),
	(
		'jane.doe@example.com',
		'qwerty1234',
		'Jane',
		'Doe'
	),
	(
		'sofie.doe@example.com',
		'asdf1234',
		'Sofie',
		'Doe'
	),
	(
		'andrew.doe@example.com',
		'zxcv1234',
		'Andrew',
		'Doe'
	);

DROP TABLE IF EXISTS `EndToEnd`.`Role`;

ADD TABLE `EndToEnd`.`Role` (
	`RoleID` INT PRIMARY KEY AUTO_INCREMENT,
	`Role` VARCHAR(255) NOT NULL,
	`Description` VARCHAR(255),
	'Active' INT DEFAULT 1,
	`Created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`Updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	KEY `Role` (`Role`),
	KEY `Active` (`Active`)
);

DELETE FROM `EndToEnd`.`Role`;

INSERT INTO
	`EndToEnd`.`Role` (`Role`, `Description`)
VALUES
	('System', 'System/God user'),
	('Admin', 'Administrator'),
	('User', 'Regular User'),
	('Guest', 'Guest User');

DROP TABLE IF EXISTS `EndToEnd`.`UserRole`;

ADD TABLE `EndToEnd`.`UserRole` (
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

DELETE FROM `EndToEnd`.`UserRole`;

INSERT INTO
	`EndToEnd`.`UserRole` (`UserID`, `RoleID`)
VALUES
	(1, 1),
	(2, 2),
	(3, 3),
	(4, 3),
	(5, 3);
