<?php

namespace App\NotaFiscal\Action;

use App\NotaFiscal\Action\NotaFiscalServico;

class EmitirNotaFiscalServicoAction extends NotaFiscalServicoAction
{
  public function execute($data): array
  {

    $xml = $this->soapStructure->emitir($data);


    $response = [
      "success" => true,
      "data" => [],
      "errors" => []
    ];
    // $response = $this->httpService->request($xml["action"], $xml["content"]);


    if ($response["success"])
      $this->repository->create([
        "id_prefeitura" => "1",
        "payload" => json_encode($data),
        "response" => json_encode($response),
        "status" => "EMITIDA"
      ]);


    return [
      "success" => $response["success"],
      "message" => $response["success"] ? "Nota emitida com sucesso!" : "Falha ao emitir a nota fiscal!",
      "data" => $response["success"] ? $response["data"]  : $response["errors"]
    ];
  }
}
