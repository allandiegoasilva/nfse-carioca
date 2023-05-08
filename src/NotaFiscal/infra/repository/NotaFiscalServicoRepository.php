<?php

namespace App\NotaFiscal\Infra\Repository;

use App\NotaFiscal\Domain\Repository\INotaFiscalServicoRepository;

class NotaFiscalServicoRepository implements INotaFiscalServicoRepository
{
  private $database;

  public function __construct($connection)
  {
    $this->database = $connection;
  }

  function create($notaFiscalServico): void
  {
    $sql = "INSERT INTO nota_fiscal(id_prefeitura, payload, response, status)
                 VALUES (:id_prefeitura, :payload, :response, :status)";

    $prepare = $this->database->prepare($sql);

    $prepare->execute([
      "id_prefeitura" => $notaFiscalServico["id_prefeitura"],
      "payload" => $notaFiscalServico["payload"],
      "response" => $notaFiscalServico["response"],
      "status" => $notaFiscalServico["status"],
    ]);
  }

  function read($id): array
  {
    $statement = $this->database->prepare("SELECT * FROM nota_fiscal WHERE id = :id");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch('assoc');

    if (!$result)
      return [];

    $result["payload"] = json_decode($result["payload"]);
    $result["response"] = json_decode($result["response"]);

    return $result;
  }

  function readAll(): array
  {
    $results = $this->database->execute("SELECT * FROM nota_fiscal")->fetchAll('assoc');

    $results = array_map(function ($result) {
      $result["payload"] = json_decode($result["payload"]);
      $result["response"] = json_decode($result["response"]);

      return $result;
    }, $results);

    return $results;
  }

  /* 
    @status - EMITIDA / CANCELADA
  */
  function updateStatus($id, $status): void
  {
    $sql = "UPDATE nota_fiscal SET status = :status WHERE id = :id";

    $prepare = $this->database->prepare($sql);

    $prepare->execute([
      "id" => $id,
      "status" => $status
    ]);
  }
}
