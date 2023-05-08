<?php

namespace App\NotaFiscal\Action;

use App\NotaFiscal\Action\NotaFiscalServico;

class ConsultarNotaFiscalServicoAction extends NotaFiscalServicoAction
{
  public function execute($id = null): array
  {
    $data = $id ? $this->repository->read($id) : $this->repository->readAll();

    return [
      "message" => null,
      "data" =>  $data
    ];
  }
}
