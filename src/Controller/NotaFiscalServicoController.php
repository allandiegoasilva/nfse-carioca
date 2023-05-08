<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

use App\NotaFiscal\Action\CancelarNotaFiscalServicoAction;
use App\NotaFiscal\Action\ConsultarNotaFiscalServicoAction;
use App\NotaFiscal\Action\EmitirNotaFiscalServicoAction;

class NotaFiscalServicoController extends AppController
{
  public function initialize(): void
  {
    parent::initialize();
  }

  public function read()
  {
    $id = $this->request->getParam('id');
    $service = new ConsultarNotaFiscalServicoAction();

    $results = $service->execute($id);

    $this->response(200,  $results);
  }

  public function readAll()
  {
    $service = new ConsultarNotaFiscalServicoAction();

    $results = $service->execute();

    $this->response(200,  $results);
  }

  public function create()
  {
    $data = $this->request->getData();

    $service = new EmitirNotaFiscalServicoAction();

    $response = $service->execute($data);
    $this->response(200, $response);
  }

  public function cancel()
  {
    $data = $this->request->getData();
    $service = new CancelarNotaFiscalServicoAction();

    $response = $service->execute($data);
    $this->response(200, $response);
  }

  private function response($statusCode, $data = [])
  {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
  }
}
