<?php

/**
 * ratings module
 * search form for ratings
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/clubs
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_ratings_ratingsearch($params) {
	$data = [];

	$page['text'] = '';
	if (!empty($_GET['name'])) {
		$_GET['name'] = trim($_GET['name']);
		$conditions = [];
		$name = $_GET['name'];
		if (strstr($name, ', ')) {
			$name = str_replace(', ', ',', $name);
		}
		if (strstr($name, ' ')) {
			$name = explode(' ', $name);
			$name = array_reverse($name);
			$name = implode(',', $name);
		}
		$conditions[] = sprintf('Spielername LIKE "%%%s%%"', wrap_db_escape($name));
		$ratings = mf_ratings_ratinglist($conditions);
		if ($ratings) {
			$ratings['searchword'] = $_GET['name'];
			$page['text'] .= wrap_template('ratinglist', $ratings);
		}

		// clubs?
		$sql = 'SELECT contacts.contact_id, contact
				, contacts_identifiers.identifier AS zps_code
			FROM contacts
			LEFT JOIN contacts_identifiers
				ON contacts.contact_id = contacts_identifiers.contact_id
				AND contacts_identifiers.current = "yes"
				AND contacts_identifiers.identifier_category_id = %d
			WHERE contact LIKE "%%%s%%"
			AND NOT ISNULL(contacts_identifiers.identifier)
			AND contact_category_id IN (%d, %d)
			AND ISNULL(end_date)
		';
		$sql = sprintf($sql
			, wrap_category_id('identifiers/zps')
			, wrap_db_escape($_GET['name'])
				, wrap_category_id('contact/club')
				, wrap_category_id('contact/chess-department')
		);
		$data['clubs'] = wrap_db_fetch($sql, 'contact_id');

		// zps codes?
		if (strlen($_GET['name']) <= 5 AND preg_match('/[0-9A-Z]*/', $_GET['name'])) {
			$sql = 'SELECT contacts.contact_id, contact
					, contacts_identifiers.identifier AS zps_code
				FROM contacts
				LEFT JOIN contacts_identifiers
					ON contacts.contact_id = contacts_identifiers.contact_id
					AND contacts_identifiers.current = "yes"
					AND contacts_identifiers.identifier_category_id = %d
				WHERE contacts_identifiers.identifier LIKE "%s%%"
				AND contact_category_id IN (%d, %d)
				AND ISNULL(end_date)
			';
			$sql = sprintf($sql
				, wrap_category_id('identifiers/zps')
				, wrap_db_escape($_GET['name'])
				, wrap_category_id('contact/club')
				, wrap_category_id('contact/chess-department')
			);
			$data['clubs'] = array_merge($data['clubs'], wrap_db_fetch($sql, 'contact_id'));
		}
		if (!$ratings AND !$data['clubs'])
			$data['no_ratings_found'] = true;
		
		$data['searchword'] = $_GET['name'];
		$page['meta'][] = ['name' => 'robots', 'content' => 'noindex'];
	}

	$page['query_strings'][] = 'name';
	$page['text'] .= wrap_template('ratingsearch', $data);
	$page['text'] .= wrap_template('ratingstatus');
	return $page;
}
