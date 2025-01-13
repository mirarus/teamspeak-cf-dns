<?php

namespace Mirarus\TeamSpeakCFDNS;

use stdClass;

/**
 * Dns
 *
 * @package    Mirarus\TeamSpeakCFDNS
 * @author     Ali Güçlü <aliguclutr@gmail.com>
 * @copyright  Copyright (c) 2025
 * @license    MIT
 * @version    1.0.2
 * @since      1.0.0
 */
class Dns
{
	private $authorization;
	private $request;
	private $domain;

	/**
	 * @param Authorization $authorization
	 */
	public function __construct(Authorization $authorization)
	{
		$this->authorization = $authorization;

		$this->request = new Request([
		  'headers' => [
			'X-Auth-Email' => ($this->authorization->getEmail() ?? ''),
			'X-Auth-Key' => ($this->authorization->getApiKey() ?? ''),
		  ]
		]);
	}

	/**
	 * @param mixed $domain
	 */
	public function setDomain($domain): void
	{
		$this->domain = $domain;
	}

	/**
	 * @return mixed
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @return false|mixed
	 */
	private function identifier()
	{
		$result = $this->getZone($this->getDomain());
		if (isset($result->result) && count($result->result) == 1) {
			return $result->result[0]->id;
		}
		return false;
	}

	/**
	 * @param $name
	 * @return mixed|stdClass|string
	 */
	public function getZone($name)
	{
		return $this->request->get('zones', [
		  'name' => $name,
		  'status' => 'active',
		  'page' => 1,
		  'match' => 'all'
		]);
	}

	/**
	 * @return mixed|stdClass|string
	 */
	public function getZoneList()
	{
		return $this->request->get('zones');
	}

	/**
	 * @param string $type
	 * @param $name
	 * @return false|mixed
	 */
	public function getDNSRecord(string $type, $name)
	{
		$identifier = $this->identifier();

		if (!$identifier) {
			return false;
		}

		$response = $this->request->get('zones/' . $identifier . '/dns_records', [
		  'type' => $type,
		  "name" => $name,
		  'per_page' => 20,
		  'order' => 'type',
		  'match' => 'all'
		]);

		if ($response && count($response->result) == 1) {
			return $response->result[0]->id;
		}
		return false;
	}

	/**
	 * @param array $data
	 * @return false|mixed
	 */
	public function createDNSRecord(array $data = [])
	{
		$identifier = $this->identifier();

		if (!$identifier) {
			return false;
		}

		return $this->request->post('zones/' . $identifier . '/dns_records', $data);
	}

	/**
	 * @param string $recordID
	 * @param array $data
	 * @return false|mixed
	 */
	public function updateDNSRecord(string $recordID, array $data = [])
	{
		$identifier = $this->identifier();

		if (!$identifier) {
			return false;
		}

		return $this->request->put('zones/' . $identifier . '/dns_records/' . $recordID, $data);
	}

	/**
	 * @param string $recordID
	 * @return false|mixed
	 */
	public function deleteDNSRecord(string $recordID)
	{
		$identifier = $this->identifier();

		if (!$identifier) {
			return false;
		}

		return $this->request->delete('zones/' . $identifier . '/dns_records/' . $recordID);
	}

	/**
	 * @param $name
	 * @return false|mixed
	 */
	public function getARecord($name)
	{
		$target = strtolower($name . "." . $this->getDomain());

		return $this->getDNSRecord(RecordType::A, $target);
	}

	/**
	 * @param $name
	 * @return false|mixed
	 */
	public function getSRVRecord($name)
	{
		$target = strtolower($name . "." . $this->getDomain());
		$target = ('_ts3._udp.' . $target);

		return $this->getDNSRecord(RecordType::SRV, $target);
	}

	/**
	 * @param string $name
	 * @param string $host
	 * @return false|mixed
	 */
	public function createARecord(string $name, string $host)
	{
		$target = strtolower($name . "." . $this->getDomain());

		return $this->createDNSRecord([
		  "type" => RecordType::A,
		  "name" => $target,
		  "content" => $host
		]);
	}

	/**
	 * @param string $name
	 * @param int $port
	 * @return false|mixed
	 */
	public function createSRVRecord(string $name, int $port)
	{
		$target = strtolower($name . "." . $this->getDomain());

		return $this->createDNSRecord([
		  "type" => RecordType::SRV,
		  "name" => ("_ts3._udp." . $target),
		  "ttl" => 120,
		  "data" => [
			"service" => "_ts3",
			"proto" => "_udp",
			"weight" => 5,
			"port" => $port,
			"priority" => 0,
			"target" => $target
		  ]
		]);
	}

	/**
	 * @param string $recordID
	 * @param string $name
	 * @param string $host
	 * @return false|mixed
	 */
	public function updateARecord(string $recordID, string $name, string $host)
	{
		$target = strtolower($name);

		return $this->updateDNSRecord($recordID, [
		  "type" => RecordType::A,
		  "name" => $target,
		  "content" => $host
		]);
	}

	/**
	 * @param string $recordID
	 * @param string $name
	 * @param int $port
	 * @return false|mixed
	 */
	public function updateSRVRecord(string $recordID, string $name, int $port)
	{
		$target = strtolower($name . "." . $this->getDomain());

		return $this->updateDNSRecord($recordID, [
		  "type" => RecordType::SRV,
		  "name" => ("_ts3._udp." . $target),
		  "ttl" => 60,
		  "data" => [
			"service" => "_ts3",
			"proto" => "_udp",
			"weight" => 5,
			"port" => $port,
			"priority" => 0,
			"target" => $target
		  ]
		]);
	}

	/**
	 * @param $name
	 * @param $host
	 * @param int $port
	 * @return bool
	 */
	public function create($name, $host, int $port): bool
	{
		$ARecordID = $this->getARecord($name);
		$SRVRecordID = $this->getSRVRecord($name);

		if ($ARecordID == null && $SRVRecordID == null) {

			$createARecord = $this->createARecord($name, $host);
			if ($createARecord && $createARecord->success) {

				$createSRVRecord = $this->createSRVRecord($name, $port);
				if ($createSRVRecord && $createSRVRecord->success) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param $currentName
	 * @param $host
	 * @param int $port
	 * @return bool
	 */
	public function update($currentName, $name, $host, int $port): bool
	{
		$ARecordID = $this->getARecord($currentName);
		$SRVRecordID = $this->getSRVRecord($currentName);

		if ($ARecordID != null && $SRVRecordID != null) {

			$updateARecord = $this->updateARecord($ARecordID, $name, $host);
			$updateSRVRecord = $this->updateSRVRecord($SRVRecordID, $name, $port);

			if ($updateARecord && $updateSRVRecord) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function delete($name): bool
	{
		$ARecordID = $this->getARecord($name);
		$SRVRecordID = $this->getSRVRecord($name);

		if ($ARecordID != null && $SRVRecordID != null) {

			$deleteARecord = $this->deleteDNSRecord($ARecordID);
			$deleteSRVRecord = $this->deleteDNSRecord($SRVRecordID);

			if ($deleteARecord && $deleteSRVRecord) {
				return true;
			}
		}
		return false;
	}
}