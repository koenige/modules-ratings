/**
 * ratings module
 * sync queries
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/ratings
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2012-2016, 2019-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


-- verbaende_source --
SELECT Verband, Verbandname, CONCAT(Uebergeordnet, ' ') AS Uebergeordnet
FROM dwz_verbaende;

-- verbaende_existing --
SELECT ok.identifier AS zps_code, contact_id
FROM contacts_identifiers ok
LEFT JOIN contacts USING (contact_id)
WHERE ok.identifier IN (%s)
AND identifier_category_id = /*_ID categories kennungen/zps _*/;

-- verbaende_deletable --
SELECT ok.identifier AS zps_code, contact_id, contact
FROM contacts_identifiers ok
LEFT JOIN contacts USING (contact_id)
WHERE ok.identifier NOT IN (%s)
AND ok.current = 'yes'
AND identifier_category_id = /*_ID categories kennungen/zps _*/
AND contacts.contact_category_id = /*_ID categories contact/federation _*/;

-- verbaende_static1 --
contact_category_id = /*_ID categories contact/federation _*/;

-- verbaende_static2 --
contacts_identifiers[0][identifier_category_id] = /*_ID categories kennungen/zps _*/;

-- verbaende_static3 --
contacts_identifiers[0][current] = 'yes'


/** 
 * @todo do not change from chess department to club, not all clubs
 * have SABT in their name if they are just a department
 * maybe check this for import only
 */
-- vereine_source --
SELECT ZPS, Vereinname
, IF(Vereinname REGEXP 'SABT', /*_ID categories contact/chess-department _*/, *_ID contact/club _*/) AS contact_category_id
, CONCAT(Verband, ' ') AS Verband
FROM dwz_vereine;

-- vereine_existing --
SELECT contacts_identifiers.identifier, contact_id
FROM contacts
LEFT JOIN contacts_identifiers USING (contact_id)
WHERE contacts_identifiers.identifier IN (%s)
AND identifier_category_id = /*_ID categories kennungen/zps _*/
AND current = 'yes';

-- vereine_deletable --
SELECT contacts_identifiers.identifier, contact_id, contact
FROM contacts
LEFT JOIN contacts_identifiers USING (contact_id)
WHERE contacts_identifiers.identifier NOT IN (%s)
AND identifier_category_id = /*_ID categories kennungen/zps _*/
AND current = 'yes'
AND contact_category_id IN (/*_ID categories contact/club _*/, /*_ID categories contact/chess-department _*/)
AND ISNULL(end_date)

-- vereine_static1 --
contacts_identifiers[0][identifier_category_id] = /*_ID categories kennungen/zps _*/;

-- vereine_static2 --
contacts_identifiers[0][current] = 'yes';
