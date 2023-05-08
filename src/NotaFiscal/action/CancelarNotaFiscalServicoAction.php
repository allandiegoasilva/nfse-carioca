<?php

namespace App\NotaFiscal\Action;

use App\NotaFiscal\Action\NotaFiscalServico;

class CancelarNotaFiscalServicoAction extends NotaFiscalServicoAction
{

  public function execute($data): array
  {

    $data = $this->validateParameters($data);

    if ($data["errors"])
      return [
        "success" => false,
        "message" => "Parâmetros inválidos!",
        "data" => $data["errors"]
      ];

    $data = $data["parameters"];

    $nfse = $this->repository->read($data["id"]);

    $prestadorServico = $nfse["response"]->PrestadorServico;

    if (!$nfse)
      return [
        "success" => false,
        "message" => "Nota fiscal não encontrada!",
        "data" => []
      ];

    $data = [
      "IdentificacaoNfse" => [
        "Numero" => $nfse["id_prefeitura"],
        "Cnpj" => $prestadorServico->IdentificacaoPrestador->Cnpj,
        "CodigoMunicipio" => $prestadorServico->Endereco->CodigoMunicipio
      ],
      "CodigoCancelamento" => $data["CodigoCancelamento"]
    ];

    $xml = $this->soapStructure->cancelar($data);

    $response = $this->httpService->request($xml["action"], $xml["content"]);

    if ($response["success"])
      $this->repository->updateStatus($nfse["id"], "CANCELADO");


    return [
      "success" => $response["success"],
      "message" => $response["success"] ? "Nota cancelada com sucesso!" : "Falha ao cancelar a nota fiscal!",
      "data" => $response["success"] ? $response["data"]  : $response["errors"]
    ];
  }

  public function validateParameters($data)
  {
    $erros = [];

    $id = $data["id"] ?? null;
    $codigoCancelamento = $data["CodigoCancelamento"] ?? null;

    if (!$id)
      $erros[] = "Informe um id válido para a NFSe";

    if (!in_array($codigoCancelamento, [1, 2, 3, 9]))
      $erros[] = "Código de cancelamento inválido, valores possíveis: 1, 2, 3, 9";

    return [
      "errors" => $erros,
      "parameters" => [
        "id" => $id,
        "CodigoCancelamento" => $codigoCancelamento
      ]
    ];
  }
}
