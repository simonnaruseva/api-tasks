<?php
namespace ConstructionStages;
use Api\Api;
use ConstructionStagesCreate;
use Exception;
use PDO;

class ConstructionStages
{
	private $db;

	public function __construct()
	{

		$this->db =  Api::getDb();
	}


	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");

		return $stmt->fetchAll(PDO::FETCH_ASSOC);


	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name,
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

    /**
     * @throws Exception
     */
    public function update(ConstructionStagesCreate $data, $id)
    {
        if(isset($data->status)) {
            $status = strtoupper($data->status);
            if(!in_array($status, ['NEW','PLANNED','DELETED'])) {
                throw new Exception('Invalid status value');
            }
        }
        $fields = [];
        $values = [];
        foreach($data as $key => $value) {
            $fields[] = "$key = :$key";
            $values[':$key'] = $value;

        }

        $values[':id'] = $id;

        $stmt = $this->db->prepare("
	UPDATE construction_stages
	SET " .implode(',', $fields)."
        WHERE ID = :id
");

        $stmt->execute($values);
        return $this->getSingle($id);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("
            UPDATE construction_stages
            SET status = 'DELETED'
             WHERE ID = :id
        ");
        $stmt->execute(['id' => $id]);
    }

}