USE studyverse;

-- 1) Admin password (safe for the supplied admin-ready schema)
ALTER TABLE admin_info
ADD COLUMN IF NOT EXISTS password VARCHAR(255) NOT NULL DEFAULT 'admin123';

-- 2) Admin-to-admin responses/messages
CREATE TABLE IF NOT EXISTS admin_messages (
    messageID INT AUTO_INCREMENT PRIMARY KEY,
    senderAdminID VARCHAR(8) NOT NULL,
    receiverAdminID VARCHAR(8) NOT NULL,
    message VARCHAR(1000) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'UNREAD',
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (senderAdminID) REFERENCES admin_info(AdminID) ON DELETE CASCADE,
    FOREIGN KEY (receiverAdminID) REFERENCES admin_info(AdminID) ON DELETE CASCADE
);

-- 3) Work Due
CREATE TABLE IF NOT EXISTS admin_work_due (
    workID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description VARCHAR(500) NOT NULL,
    due_date DATE NOT NULL,
    assignedAdminID VARCHAR(8) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignedAdminID) REFERENCES admin_info(AdminID) ON DELETE CASCADE
);

-- 4) Reports
CREATE TABLE IF NOT EXISTS admin_reports (
    reportID INT AUTO_INCREMENT PRIMARY KEY,
    reporterID VARCHAR(8) NOT NULL,
    targetType VARCHAR(30) NOT NULL,
    targetID VARCHAR(20) NOT NULL,
    reason VARCHAR(500) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    reported_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    handledBy VARCHAR(8) DEFAULT NULL,
    handled_at TIMESTAMP NULL DEFAULT NULL
);

-- Example:
-- INSERT INTO admin_work_due(title,description,due_date,assignedAdminID)
-- VALUES ('Review course reports','Review newly submitted course reports','2026-09-10','A1');
