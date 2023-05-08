<?php

namespace App\NotaFiscal\Action;

use App\NotaFiscal\Action\NotaFiscalServico;

class CancelarNotaFiscalServicoAction extends NotaFiscalServicoAction
{

  public function execute($data): array
  {
    $xml = $this->soapStructure->cancelar($data);

    $response = $this->httpService->request($xml["action"], $xml["content"]);

    return [
      "success" => $response["success"],
      "message" => $response["success"] ? "Nota cancelada com sucesso!" : "Falha ao cancelar a nota fiscal!",
      "data" => $response["success"] ? $response["data"]  : $response["errors"]
    ];
  }
}
