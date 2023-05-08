<?php

namespace App\NotaFiscal\Action;

use App\NotaFiscal\Action\NotaFiscalServico;

class EmitirNotaFiscalServicoAction extends NotaFiscalServicoAction
{
  public function execute($data): array
  {

    $validation = $this->validateParameters($data);

    if ($validation["errors"])
      return [
        "success" => false,
        "message" => "Verifique os parâmetros inválidos!",
        "data" => $validation["errors"]
      ];

    $data = $validation["parameters"];

    $xml = $this->soapStructure->emitir($data);

    $response = [
      "success" => true,
      "data" => [],
      "errors" => []
    ];

    $response = $this->httpService->request($xml["action"], $xml["content"]);

    if ($response["success"]) {
      $nfse = $response["data"]->CompNfse->Nfse->InfNfse;
      $idPrefeitura = $nfse->Numero;
      $ccm = $nfse->PrestadorServico->IdentificacaoPrestador->InscricaoMunicipal;
      $codigoVerificacao =  str_replace('-', '', $nfse->CodigoVerificacao);

      $this->repository->create([
        "id_prefeitura" => $idPrefeitura,
        "payload" => json_encode($data),
        "response" => json_encode($nfse),
        "status" => "EMITIDA",
        "link" => $response["uri"] . "/nfse.aspx?ccm={$ccm}&nf={$idPrefeitura}&cod={$codigoVerificacao}"
      ]);
    }

    return [
      "success" => $response["success"],
      "message" => $response["success"] ? "Nota emitida com sucesso!" : "Falha ao emitir a nota fiscal!",
      "data" => $response["success"] ? $response["data"]  : $response["errors"]
    ];
  }

  private function validateParameters($data)
  {
    $errors = [];

    $dataEmissao = $data["DataEmissao"] ?? '';
    $naturezaOperacao = $data["NaturezaOperacao"] ?? 1;
    $regimeEspecialTributacao = $data["RegimeEspecialTributacao"] ?? null;
    $optanteSimplesNacional = $data["OptanteSimplesNacional"] ?? 2;
    $incentivadorCultural = $data["IncentivadorCultural"] ?? 2;

    $prestador = $this->validarPrestador($data);
    $tomador = $this->validarTomador($data);
    $servico = $this->validarServico($data);

    $validations = [
      [
        "message" => "Informe uma natureza de operação válida: 1, 2, 3, 4, 5, 6",
        "invalid" => !in_array($naturezaOperacao, [1, 2, 3, 4, 5, 6])
      ],
      [
        "message" => "Informe um  regime especial de tributacao válido: 1, 2, 3, 4, 5, 6",
        "invalid" => !in_array($regimeEspecialTributacao, [1, 2, 3, 4, 5, 6, null])
      ],
      [
        "message" => "Informe um valor válido para optante simples nacional: 1 ou 2",
        "invalid" => !in_array($optanteSimplesNacional, [1, 2])
      ],
      [
        "message" => "Informe um incentivador cultural válido: 1 ou 2",
        "invalid" => !in_array($incentivadorCultural, [1, 2])
      ]
    ];

    if ($prestador["errors"])
      $errors = [...$errors, ...$prestador["errors"]];

    if ($tomador["errors"])
      $errors = [...$errors, ...$tomador["errors"]];

    if ($servico["errors"])
      $errors = [...$errors, ...$servico["errors"]];

    foreach ($validations as $validation)
      if ($validation["invalid"])
        $errors[] = $validation["message"];

    return [
      "errors" => $errors,
      "parameters" => [
        'IdentificacaoRps' => [
          "Numero" => random_int(1000000, 9999999),
          "Serie"  => "A",
          "Tipo"   => 1,
        ],
        'DataEmissao' => $dataEmissao,
        'NaturezaOperacao' => $naturezaOperacao,
        'RegimeEspecialTributacao' => $regimeEspecialTributacao,
        'OptanteSimplesNacional' => $optanteSimplesNacional,
        'IncentivadorCultural' => $incentivadorCultural,
        'Status' => 1,
        'Prestador' => $prestador["parameters"],
        'Tomador' => $tomador["parameters"],
        'Servico' => $servico["parameters"],
        "RpsSubstituido" => null,
      ]
    ];
  }

  private function validarPrestador($data)
  {
    $errors = [];

    if (!isset($data["Prestador"]))
      return [
        "errors" => ["Informe o prestador"],
        "parameters" => []
      ];

    $prestador = $data["Prestador"];

    $cnpj = $prestador["Cnpj"] ?? null;
    $inscricaoMunicipal = $prestador["InscricaoMunicipal"] ?? null;

    if (!is_numeric($cnpj) || strlen($cnpj) != 14)
      $errors[] = "CNPJ do prestador está inválido!";

    return [
      "errors" => $errors,
      "parameters" => [
        'Cnpj' => $cnpj,
        'InscricaoMunicipal' => $inscricaoMunicipal
      ]
    ];
  }

  private function validarTomador($data)
  {
    $errors = [];

    if (!isset($data["Tomador"]))
      return [
        "errors" => ["Informe o Tomador"],
        "parameters" => []
      ];

    $tomador = $data["Tomador"];

    $indentificacaoTomador = $tomador["IdentificacaoTomador"] ?? null;
    $cpfCnpj = $indentificacaoTomador && isset($indentificacaoTomador["CpfCnpj"]) ? $indentificacaoTomador["CpfCnpj"] : null;

    $razaoSocial = $tomador["RazaoSocial"] ?? null;

    $endereco = [
      'Endereco' => null,
      'Numero' => null,
      'Complemento' => null,
      'Bairro' => null,
      'CodigoMunicipio' => null,
      'Uf' => null,
      'Cep' => null,
    ];

    if (isset($data["Endereco"])) {
      $endereco["Endereco"] = $endereco["Endereco"]["Endereco"] ?? null;
      $endereco["Numero"] = $endereco["Endereco"]["Numero"] ?? null;
      $endereco["Complemento"] = $endereco["Endereco"]["Complemento"] ?? null;
      $endereco["CodigoMunicipio"] = $endereco["Endereco"]["CodigoMunicipio"] ?? null;
      $endereco["Uf"] = $endereco["Endereco"]["Uf"] ?? null;
      $endereco["Cep"] = $endereco["Endereco"]["Cep"] ?? null;
    }

    $cpfCnpjValue = [];
    $cpfCnpjValid = false;

    if (isset($cpfCnpj["Cpf"])) {
      $cpfCnpjValue["Cpf"] = $cpfCnpj["Cpf"];
      $cpfCnpjValid = is_numeric($cpfCnpjValue["Cpf"]) && strlen($cpfCnpjValue["Cpf"]) == 11;
    }

    if (isset($cpfCnpj["Cnpj"])) {
      $cpfCnpjValue["Cnpj"] = $cpfCnpj["Cnpj"];
      $cpfCnpjValid = is_numeric($cpfCnpjValue["Cnpj"]) && strlen($cpfCnpjValue["Cnpj"]) == 14;
    }

    if (!$cpfCnpjValid)
      $errors[] = "Informe um CPF/CNPJ válido para o tomador!";

    return [
      "errors" => $errors,
      "parameters" => [
        'IdentificacaoTomador' => [
          'CpfCnpj' => $cpfCnpjValue,
        ],
        'RazaoSocial' => $razaoSocial,
        'Endereco' => $endereco,
      ]
    ];
  }

  private function validarServico($data)
  {
    $errors = [];

    if (!isset($data["Servico"]))
      return [
        "errors" => ["Informe o Servico"],
        "parameters" => []
      ];

    $servico = $data["Servico"];
    $itemListaServico = $servico["ItemListaServico"] ?? null;
    $codigoTributacaoMunicipio = $servico["CodigoTributacaoMunicipio"] ?? null;
    $disciminacao = $servico["Discriminacao"] ?? null;
    $codigoMunicipio = $servico["CodigoMunicipio"] ?? null;

    $valores = [
      'ValorServicos' => 0,
      'ValorDeducoes' => 0.0,
      'ValorPis' => 0.0,
      'ValorCofins' => 0.0,
      'ValorInss' => 0.0,
      'ValorIr' => 0.0,
      'ValorCsll' => 0.0,
      'IssRetido' => 2, // 1 para ISS Retido - 2 para ISS não Retido,
      'ValorIss' => 0.0,
      'OutrasRetencoes' => 0.0,
      'Aliquota' => 0,
      'DescontoIncondicionado' => 0.0,
      'DescontoCondicionado' => 0.0,
    ];

    if (!isset($servico["Valores"]))
      $errors[] = "Informe os valores do serviço";

    if (!$itemListaServico)
      $errors[] = "Informe o item na lista de serviço!";

    if (!$codigoTributacaoMunicipio)
      $errors[] = "Informe o codigo de tributacao do município!";

    if (!$codigoTributacaoMunicipio)
      $errors[] = "Informe uma disciminacao da nota fiscal!";

    if (!$codigoMunicipio)
      $errors[] = "Informe o código de município!";

    if (isset($servico["Valores"])) {
      $values = $servico["Valores"];
      $keys = array_keys($values);

      foreach ($keys as $key)
        if (in_array($key, array_keys($valores)))
          $valores[$key] = $values[$key];
    }


    if ($valores["ValorServicos"] == 0)
      $errors[] = "Informe um valor dos serviços maior que R$ 0,00";

    if ($valores["IssRetido"] != 1 && $valores["IssRetido"] != 2)
      $errors[] = "Informe uma valor válido para o iss retido: 1 ou 2";

    return [
      "errors" => $errors,
      "parameters" => [
        'ItemListaServico' => $itemListaServico,
        'CodigoTributacaoMunicipio' => $codigoTributacaoMunicipio,
        'Discriminacao' => $disciminacao,
        'CodigoMunicipio' => $codigoMunicipio,
        'Valores' => $valores,
      ]
    ];
  }
}
