<?php

/**
 * ratings module
 * import elo rating data
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/ratings
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2017, 2019-2020, 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Import FIDE Elo
 *
 * @param array $params
 *		[0]: string folder name
 * @return array
 */
function mod_ratings_make_ratings_import_elo($params) {
	$sql = 'TRUNCATE fide_players';
	wrap_db_query($sql);

	$field_names = [
		'ID_Number' => 'player_id', 'Name' => 'player', 'Fed' => 'federation',
		'Sex' => 'sex', 'Tit' => 'title', 'WTit' => 'title_women',
		'OTit' => 'title_other', 'FOA' => 'foa_rating', 
		'SRtng' =>'standard_rating', 'SGm' => 'standard_games',
		'SK' => 'standard_k_factor', 'RRtng' => 'rapid_rating',
		'RGm' => 'rapid_games', 'Rk' => 'rapid_k_factor',
		'BRtng' => 'blitz_rating', 'BGm' => 'blitz_games',
		'BK' => 'blitz_k_factor', 'B-day' => 'birth', 'Flag' => 'flag'
	];

	$file = $params[0].'/players_list_foa.txt';
	$handle = fopen($file, 'r');
	$i = 0;
	if ($handle) {
		while (($line = fgets($handle, 4096)) !== false) {
			if (!$i) {
				// not a standard title line
				$line = str_replace('ID Number', 'ID_Number', $line);
				preg_match_all('~([-\w]+)\s+~', $line, $matches);
				$fields = $matches[1];
				$pos = 0;
				foreach ($matches[0] as $index => $field) {
					$lengths[$index] = strlen($field);
					$start[$index] = $pos;
					$pos += strlen($field);
				}
			} else {
				$fieldnames = [];
				$values = [];
				foreach ($fields as $index => $field) {
					$value = trim(substr($line, $start[$index], $lengths[$index]));
					if (!$value) continue;
					if ($field === 'B-day' AND $value === '0000') continue;
					$fieldnames[] = $field_names[$field];
					$values[] = is_numeric($value) ? $value : sprintf('"%s"', $value);
				}
				if (count($values) > 1) {
					// sometimes, FIDE exports have errors
					$sql = 'INSERT INTO fide_players (%s) VALUES (%s)';
					$sql = sprintf($sql, implode(',', $fieldnames), implode(',', $values));
					wrap_db_query($sql);
				}
			}
			$i++;
		}
	}
    fclose($handle);
	unlink($file);
	return [];
}
