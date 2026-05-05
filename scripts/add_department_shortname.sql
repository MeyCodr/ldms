ALTER TABLE `divisions`
    ADD COLUMN `shortname` VARCHAR(100) NULL AFTER `name`;

ALTER TABLE `departments`
    ADD COLUMN `shortname` VARCHAR(100) NULL AFTER `name`;

ALTER TABLE `sections`
    ADD COLUMN `shortname` VARCHAR(100) NULL AFTER `name`;

UPDATE `divisions` SET `shortname` = 'BDS' WHERE `name` = 'BUSINESS DEVELOPMENT & STRATEGY';
UPDATE `divisions` SET `shortname` = 'DHMSB' WHERE `name` = 'DHMSB OPERATIONS';
UPDATE `divisions` SET `shortname` = 'FIN' WHERE `name` = 'FINANCE';
UPDATE `divisions` SET `shortname` = 'HCESG' WHERE `name` = 'HUMAN CAPITAL & ESG';
UPDATE `divisions` SET `shortname` = 'OPS' WHERE `name` = 'OPERATION MANAGEMENT';
UPDATE `divisions` SET `shortname` = 'ERD' WHERE `name` = 'ENGINEERING AND R&D';
UPDATE `divisions` SET `shortname` = 'COO' WHERE `name` = 'COO OFFICE';
UPDATE `divisions` SET `shortname` = 'QM' WHERE `name` = 'QUALITY MANAGEMENT';

UPDATE `departments` SET `shortname` = 'BD' WHERE `name` = 'BUSINESS DEVELOPMENT';
UPDATE `departments` SET `shortname` = 'IT' WHERE `name` = 'IT & DIGITALISATION';
UPDATE `departments` SET `shortname` = 'FIN' WHERE `name` = 'FINANCE';
UPDATE `departments` SET `shortname` = 'M&S(DHMSB)' WHERE `name` = 'MANUFACTURING & SCM (DHMSB)';
UPDATE `departments` SET `shortname` = 'OPIV' WHERE `name` = 'OPERATION IV';
UPDATE `departments` SET `shortname` = 'PVD' WHERE `name` = 'PROCUREMENT & VENDOR DEVELOPMENT';
UPDATE `departments` SET `shortname` = 'PM1' WHERE `name` = 'PROGRAM MANAGEMENT 1';
UPDATE `departments` SET `shortname` = 'PM2' WHERE `name` = 'PROGRAM MANAGEMENT 2';
UPDATE `departments` SET `shortname` = 'PM3' WHERE `name` = 'PROGRAM MANAGEMENT 3';
UPDATE `departments` SET `shortname` = 'QD' WHERE `name` = 'QUALITY DEVELOPMENT';
UPDATE `departments` SET `shortname` = 'R&A' WHERE `name` = 'REWARDS & ADMIN';
UPDATE `departments` SET `shortname` = 'C&TM' WHERE `name` = 'CULTURE & TALENT MANAGEMENT';
UPDATE `departments` SET `shortname` = 'MFG PKN' WHERE `name` = 'MANUFACTURING & SCM PEKAN';
UPDATE `departments` SET `shortname` = 'MFG BB/RASA' WHERE `name` = 'MANUFACTURING & SCM BB/RASA';
UPDATE `departments` SET `shortname` = 'MFG PGH' WHERE `name` = 'MANUFACTURING & SCM PEGOH';
UPDATE `departments` SET `shortname` = 'MFG SA1' WHERE `name` = 'MANUFACTURING & SCM SA1';
UPDATE `departments` SET `shortname` = 'MFG SA2' WHERE `name` = 'MANUFACTURING & SCM SA2';
UPDATE `departments` SET `shortname` = 'IMP' WHERE `name` = 'INVENTORY MANAGEMENT PLANNING (IMP)';
UPDATE `departments` SET `shortname` = 'MFG TM1' WHERE `name` = 'MANUFACTURING & SCM TM1 (FIF)';
UPDATE `departments` SET `shortname` = 'MFG TM2' WHERE `name` = 'MANUFACTURING & SCM TM2 (OSI)';
UPDATE `departments` SET `shortname` = 'C&C' WHERE `name` = 'COSTING & COMMERCIAL';
UPDATE `departments` SET `shortname` = 'ESG' WHERE `name` = 'ESG, HEALTH AND SAFETY';
UPDATE `departments` SET `shortname` = 'QA&C2' WHERE `name` = 'QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)';
UPDATE `departments` SET `shortname` = 'QA&C3' WHERE `name` = 'QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)';
UPDATE `departments` SET `shortname` = 'QA&C1' WHERE `name` = 'QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)';
UPDATE `departments` SET `shortname` = 'QS' WHERE `name` = 'QUALITY SYSTEM & BCM';
UPDATE `departments` SET `shortname` = 'ENG 1' WHERE `name` = 'ENGINEERING MANAGEMENT 1';
UPDATE `departments` SET `shortname` = 'ENG 2' WHERE `name` = 'ENGINEERING MANAGEMENT 2';
UPDATE `departments` SET `shortname` = 'EFM' WHERE `name` = 'ENERGY & FACILITY MANAGEMENT';
UPDATE `departments` SET `shortname` = 'EQM1' WHERE `name` = 'EQUIPMENT MAINTENANCE 1 (SA1, BB/RASA & PGH)';
UPDATE `departments` SET `shortname` = 'EQM2' WHERE `name` = 'EQUIPMENT MAINTENANCE 2 (SA2, DHMSB, TM1 & TM2)';
UPDATE `departments` SET `shortname` = 'PE' WHERE `name` = 'PROCESS ENGINEERING';
UPDATE `departments` SET `shortname` = 'R&D' WHERE `name` = 'RESEARCH & DEVELOPMENT';
UPDATE `departments` SET `shortname` = 'TE' WHERE `name` = 'TOOLING DESIGN & DEVELOPMENT';
UPDATE `departments` SET `shortname` = 'COO' WHERE `name` = 'COO OFFICE';
UPDATE `departments` SET `shortname` = 'HIM' WHERE `name` = 'HICOM INTELLIGENT MOBILITY';

UPDATE `sections` SET `shortname` = 'CP&C' WHERE `name` = 'COST PLANNING & COMMERCIAL';
UPDATE `sections` SET `shortname` = 'M&S' WHERE `name` = 'MARKETING & SALES';
UPDATE `sections` SET `shortname` = 'AER' WHERE `name` = 'AEROSPACE';
UPDATE `sections` SET `shortname` = 'PMO' WHERE `name` = 'PRO. MGMT. OTHERS';
UPDATE `sections` SET `shortname` = 'PMP' WHERE `name` = 'PRO. MGMT. PERODUA';
UPDATE `sections` SET `shortname` = 'PMH' WHERE `name` = 'PRO. MGMT. HONDA';
UPDATE `sections` SET `shortname` = 'PMNA' WHERE `name` = 'PRO. MGMT NON AUTO';
UPDATE `sections` SET `shortname` = 'QREO1' WHERE `name` = 'QUALITY RESIDENT ENGINEERING OP1';
UPDATE `sections` SET `shortname` = 'QREO2' WHERE `name` = 'QUALITY RESIDENT ENGINEERING OP2';
