<?php

namespace App\NotaFiscal\Infra\Http;

use App\NotaFiscal\Contracts\IHttpService;
use Cake\Core\Configure;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class HttpService implements IHttpService
{

  private $xmlEncoder;

  public function __construct()
  {
    $this->xmlEncoder = new XmlEncoder();
  }

  public function request($action, $xml)
  {
    $config = Configure::read("nfse");

    $original_url = $config['local'] == 'DEV'  ? $config['url']['HML'] : $config['url']['PRD'];

    $url = $original_url . "/WSNacional/nfse.asmx";

    $msgSize = strlen($xml);

    $headers = ['Content-Type: text/xml;charset=UTF-8', "SOAPAction: \"$action\"", "Content-length: $msgSize"];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
    curl_setopt($curl, CURLOPT_TIMEOUT, 120 + 20);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    if (!file_exists($config["certificate"]))
      exit("Certificado não encontrado: " . $config["certificate"]);

    $data = file_get_contents($config["certificate"]);
    $certPassword = $config["private_key"];

    openssl_pkcs12_read($data, $certs, $certPassword);

    $pkey = $certs['pkey'];

    $encryptPassword = uniqid();

    openssl_pkey_export($certs['pkey'], $pkey, $encryptPassword);

    $err = openssl_error_string();

    $pemPath = __DIR__ . '/../../../../tmp/' . uniqid() . '.pem';

    file_put_contents($pemPath, $certs['cert'] . $pkey);

    curl_setopt($curl, CURLOPT_SSLVERSION, 0);
    curl_setopt($curl, CURLOPT_SSLCERT, $pemPath);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    $soapErr = curl_error($curl);

    $headSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $responseBody = trim(substr($response, $headSize));

    unlink($pemPath);

    if ('' != $soapErr)
      throw new \Exception($soapErr . " [$url]");

    if (200 != $httpCode)
      exit("HTTP error code: [$httpCode] - [$url] - " . $responseBody);

    $response = $this->extract($responseBody);

    $errors = $this->getErrors($response);
    $success = $errors ? false : true;

    if ($success)
      $response = simplexml_load_string($response);


    return [
      "success" => $success,
      "uri" => $original_url,
      "errors" => $errors,
      "data" => $response
    ];
  }

  private function getErrors(string $responseXml): array
  {
    $resultArr = $this->xmlEncoder->decode($responseXml, '');

    if (isset($resultArr['ListaMensagemRetorno'])) {
      if (isset($resultArr['ListaMensagemRetorno']['MensagemRetorno']['Codigo'])) {
        $errors[] = $resultArr['ListaMensagemRetorno']['MensagemRetorno']['Codigo'] . ' - ' . $resultArr['ListaMensagemRetorno']['MensagemRetorno']['Mensagem'];
      } else {
        foreach ($resultArr['ListaMensagemRetorno']['MensagemRetorno'] as $msgRetorno) {
          $errors[] = $msgRetorno['Codigo'] . ' - ' . $msgRetorno['Mensagem'];
        }
      }

      return $errors;
    }

    return [];
  }

  private function extract($response)
  {
    $dom = new \DomDocument('1.0', 'UTF-8');
    $dom->loadXML($response);

    if (!empty($dom->getElementsByTagName('outputXML')->item(0))) {
      $node = $dom->getElementsByTagName('outputXML')->item(0);
      return $node->textContent;
    }

    return $response;
  }
}
