<?php

namespace joshmoody\Mock;

use PDO;
use ZipArchive;

class DataLoader
{
	/**
	 * @var PDO
	 */
	protected $pdo;

	public function extractDataFiles()
	{
		$archive = $this->getStoragePath('data.zip');
		$extract_path = $this->getStoragePath('temp');

		$zip = new ZipArchive;
		$res = $zip->open($archive);

		if ($res === true) {
			$zip->extractTo($extract_path);
			$zip->close();
			printf("Extracted zip archive to %s \n", $extract_path);
		} else {
			printf("Extracting zip archive %s to %s failed with code %s \n", $archive, $extract_path, $res);
		}
	}

	/**
	 * @param $file
	 * @return string
	 */
	public function getStoragePath($file = null): string
	{
		$base_storage_path = __DIR__ . '/../storage/';

		if (!empty($file)) {
			return $base_storage_path . ltrim($file, DIRECTORY_SEPARATOR);
		} else {
			return $base_storage_path;
		}
	}

	public function __destruct()
	{
		$this->removeTmpFiles();
	}

	public function removeTmpFiles()
	{
		// Remove temp files.
		$tmp = $this->getStoragePath('temp');

		if (file_exists($tmp)) {
			array_map('unlink', glob("$tmp/*"));
		}

		rmdir($tmp);
	}

	/**
	 * @param int $limit
	 * @return int
	 */
	public function loadNames($limit = 500): int
	{
		$this->createTableFromSchema('first_names');
		$this->createTableFromSchema('last_names');

		$loaded_female = $this->loadFirstNames($limit, 'F');
		$loaded_male = $this->loadFirstNames($limit, 'M');
		$loaded_lastnames = $this->loadLastNames($limit);

		return $loaded_female + $loaded_male + $loaded_lastnames;
	}

	public function createTableFromSchema($name)
	{
		$filename = $this->getStoragePath('schema/' . basename($name) . '.sql');
		$schema = file_get_contents($filename);

		$this->getPdo()->exec($schema);
	}

	/**
	 * @return PDO
	 */
	public function getPdo(): PDO
	{
		if (empty($this->pdo)) {
			$this->pdo = new PDO('sqlite:' . $this->getStoragePath('database.sqlite'));
		}

		return $this->pdo;
	}

	/**
	 * @param PDO $pdo
	 */
	public function setPdo(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * @param int $limit
	 * @param string $gender
	 * @return int
	 */
	public function loadFirstNames($limit = 500, $gender = 'M'): int
	{
		$filename = $gender == 'F' ? 'temp/female_firstnames.txt' : 'temp/male_firstnames.txt';
		$fp = fopen($this->getStoragePath($filename), 'r');

		$count = 0;

		$sql = "INSERT INTO first_names (name, rank, gender) VALUES (:name, :rank, :gender)";

		try {
			$stmt = $this->getPdo()->prepare($sql);
		} catch (\Exception $e) {
			dd($e->getMessage());
		}

		while (!feof($fp)) {
			if ($count > $limit - 1) {
				fclose($fp);
				return $count;
			} else {
				$line = trim(fgets($fp));

				if (strlen($line) > 0) {
					$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));

					$count += $stmt->execute(
						[
							'name' => ucwords(strtolower($row['name'])),
							'rank' => $row['rank'],
							'gender' => $gender
						]
					);
				}
			}
		}

		fclose($fp);
		return $count;
	}

	/**
	 * @param int $limit
	 * @return int
	 */
	public function loadLastNames($limit = 500): int
	{
		$this->createTableFromSchema('last_names');

		$fp = fopen($this->getStoragePath('temp/lastnames.txt'), 'r');

		$count = 0;

		$sql = "INSERT INTO last_names (name, rank) VALUES (:name, :rank)";

		try {
			$stmt = $this->getPdo()->prepare($sql);
		} catch (\Exception $e) {
			dd($e->getMessage());
		}

		while (!feof($fp)) {
			if ($count > $limit - 1) {
				return $count;
			} else {
				$line = trim(fgets($fp));

				if (strlen($line) > 0) {
					$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));

					$count += $stmt->execute(
						[
							'name' => ucwords(strtolower($row['name'])),
							'rank' => $row['rank']
						]
					);
				} else {
					continue;
				}
			}
		}

		fclose($fp);

		return $count;
	}

	public function loadStreets()
	{
		$this->createTableFromSchema('streets');

		$fp = fopen($this->getStoragePath('temp/streets.txt'), 'r');

		$count = 0;

		$sql = "INSERT INTO streets (name) VALUES (:name)";

		try {
			$stmt = $this->getPdo()->prepare($sql);
		} catch (\Exception $e) {
			dd($e->getMessage());
		}

		while (!feof($fp)) {
			$name = trim(fgets($fp));

			if (strlen($name) > 0) {
				$count += $stmt->execute(['name' => $name]);
			} else {
				continue;
			}
		}

		fclose($fp);

		return $count;
	}

	public function loadZipCodes()
	{
		$this->createTableFromSchema('zipcodes');

		// The zip code database only contains state codes - no state names. The state abbreviations file supplements this data.
		$abbreviations = file($this->getStoragePath('temp/state_abbreviations.txt'));
		$state_lookup = [];

		foreach ($abbreviations as $x) {
			$row = unpack("A32name/A2code", trim($x));
			$state_lookup[trim($row['code'])] = ucfirst(strtolower(trim($row['name'])));
		}

		$fp = fopen($this->getStoragePath('temp/zip_code_database.csv'), 'r');

		$counter = 0; // Total number records processed.
		$loaded = 0; // Number records actually loaded.

		$sql = "INSERT INTO zipcodes
							(zip, type, city, acceptable_cities, unacceptable_cities, state_code, state, county, timezone, area_codes, latitude, longitude, world_region, country, decommissioned, estimated_population, notes)
							VALUES(:zip, :type, :city, :acceptable_cities, :unacceptable_cities, :state_code, :state, :county, :timezone, :area_codes, :latitude, :longitude, :world_region, :country, :decommissioned, :estimated_population, :notes)";

		try {
			$stmt = $this->getPdo()->prepare($sql);
		} catch (\Exception $e) {
			dd($e->getMessage());
		}

		while (!feof($fp)) {
			$counter++;

			list(
				$zip,
				$type,
				$primary_city,
				$acceptable_cities,
				$unacceptable_cities,
				$state_code,
				$county,
				$timezone,
				$area_codes,
				$lat,
				$long,
				$world_region,
				$country,
				$decommissioned,
				$estimated_population,
				$notes
				) = fgetcsv($fp);

			// Skip heading row and everything but standard zip codes for the 50 states and DC
			if ($counter > 1 && $type == 'STANDARD' && !in_array($state_code, ['GU', 'PR', 'VI'])) {
				if (array_key_exists($state_code, $state_lookup)) {
					$state = $state_lookup[$state_code];
				} else {
					$state = $state_code;
				}

				$data = [
					'zip' => $zip,
					'type' => $type,
					'city' => ucwords($primary_city),
					'acceptable_cities' => $acceptable_cities,
					'unacceptable_cities' => $unacceptable_cities,
					'state_code' => $state_code,
					'state' => ucwords($state),
					'county' => str_replace(' County', '', $county),
					'timezone' => $timezone,
					'area_codes' => $area_codes,
					'latitude' => $lat,
					'longitude' => $long,
					'world_region' => $world_region,
					'country' => $country,
					'decommissioned' => $decommissioned,
					'estimated_population' => $estimated_population,
					'notes' => $notes,
				];

				$loaded += $stmt->execute($data);
			}
		}

		return $loaded;
	}
}