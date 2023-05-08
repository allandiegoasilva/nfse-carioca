<?php

namespace App\NotaFiscal\Infra\Soap;

use App\NotaFiscal\Contracts\ISoapStructure;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class StructureSoap implements ISoapStructure
{

  private $encoder;

  public function __construct()
  {
    $this->encoder = new XmlEncoder();
  }

  function emitir($nfse): array
  {
    $rps = [
      'InfRps' => [
        '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
        '@Id' => $nfse['IdentificacaoRps']['Numero'],
        'IdentificacaoRps' => $nfse['IdentificacaoRps'],
        'DataEmissao' => $nfse['DataEmissao'],
        'NaturezaOperacao' => $nfse['NaturezaOperacao'],
        'RegimeEspecialTributacao' => $nfse['RegimeEspecialTributacao'],
        'OptanteSimplesNacional' => $nfse['OptanteSimplesNacional'],
        'IncentivadorCultural' => $nfse['IncentivadorCultural'],
        'Status' => $nfse['Status'],
        'RpsSubstituido' => $nfse['RpsSubstituido'],
        'Servico' => [
          'Valores' => $nfse['Servico']['Valores'],
          'ItemListaServico' => $nfse['Servico']['ItemListaServico'],
          'CodigoTributacaoMunicipio' => $nfse['Servico']['CodigoTributacaoMunicipio'],
          'Discriminacao' => $nfse['Servico']['Discriminacao'],
          'CodigoMunicipio' => $nfse['Servico']['CodigoMunicipio'],
        ],
        'Prestador' => $nfse['Prestador'],
        'Tomador' => $nfse['Tomador']
      ],
    ];

    $xml = $this->encoder->encode($rps, 'xml', ['xml_root_node_name' => 'Rps', 'remove_empty_tags' => true]);

    $xml = str_replace('<?xml version="1.0"?>', '', $xml);

    $content = '<GerarNfseEnvio xmlns="http://notacarioca.rio.gov.br/WSNacional/XSD/1/nfse_pcrj_v01.xsd">' . $xml . '</GerarNfseEnvio>';

    return $this->base_structure("GerarNfse", $content);
  }

  function consultar($data): array
  {
    $rps = [
      'ConsultarNfseEnvio' => [
        '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
        'Prestador' => $data['Prestador'],
        'PeriodoEmissao' => $data['PeriodoEmissao'],
        'Tomador' => $data['Tomador'],
      ],
    ];

    $xml = $this->encoder->encode($rps, 'xml', ['xml_root_node_name' => 'rootnode', 'remove_empty_tags' => true]);

    $xml = str_replace('<?xml version="1.0"?>', '', $xml);
    $xml = str_replace('<rootnode>', '', $xml);
    $xml = str_replace('</rootnode>', '', $xml);

    return $this->base_structure("ConsultarNfse", $xml);
  }

  function cancelar($data): array
  {
    $rps = [
      'CancelarNfseEnvio' => [
        '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
        'Pedido' => [
          '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
          'InfPedidoCancelamento' => [
            'IdentificacaoNfse' => $data['IdentificacaoNfse'],
            'CodigoCancelamento' => $data['CodigoCancelamento'],
          ],
        ],
      ],
    ];

    $xml = $this->encoder->encode($rps, 'xml', ['xml_root_node_name' => 'rootnode', 'remove_empty_tags' => true]);

    $xml = str_replace('<?xml version="1.0"?>', '', $xml);
    $xml = str_replace('<rootnode>', '', $xml);
    $xml = str_replace('</rootnode>', '', $xml);

    return $this->base_structure("CancelarNfse", $xml);
  }

  private function base_structure($operation, $content): array
  {
    $actionRequest = $operation . 'Request';

    $env = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <' . $actionRequest . ' xmlns="http://notacarioca.rio.gov.br/">
                    <inputXML>
                    <![CDATA[
                        PLACEHOLDER
                    ]]>
                    </inputXML>
                </' . $actionRequest . '>
            </soap:Body>
        </soap:Envelope>';

    $content = str_replace('PLACEHOLDER', $content, $env);

    return [
      "action"  => "http://notacarioca.rio.gov.br/" . $operation,
      "content" => $content
    ];
  }
}
